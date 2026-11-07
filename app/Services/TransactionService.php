<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Receivable;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function __construct(private StockService $stock)
    {
    }

    public function create(array $data, array $lines): Transaction
    {
        return DB::transaction(function () use ($data, $lines) {
            [$details, $total] = $this->buildDetails($lines);
            [$status, $paid]   = $this->resolvePayment($total, $data['payment_status'], (float) ($data['paid_amount'] ?? 0));

            $this->ensureCustomerIfNotPaid($data, $total - $paid);

            $transaction = Transaction::create([
                'id_customer'      => $data['id_customer'] ?? null,
                'id_user'          => auth()->id(),
                'invoice_number'   => $this->nextInvoiceNumber($data['transaction_date']),
                'transaction_date' => $data['transaction_date'],
                'total_amount'     => $total,
                'paid_amount'      => $paid,
                'payment_method'   => $data['payment_method'],
                'payment_status'   => $status,
                'status'           => 'completed',
                'note'             => $data['note'] ?? null,
            ]);

            foreach ($details as $row) {
                $transaction->details()->create($row['detail']);
                $this->stock->change($row['item'], 'sale', -$row['detail']['quantity'], 'Penjualan ' . $transaction->invoice_number, $transaction->id_transaction);
            }

            $this->syncReceivable($transaction);

            return $transaction;
        });
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        if ($transaction->isCancelled()) {
            throw ValidationException::withMessages(['status' => 'Transaksi yang sudah dibatalkan tidak bisa diubah.']);
        }

        return DB::transaction(function () use ($transaction, $data) {
            [$status, $paid] = $this->resolvePayment((float) $transaction->total_amount, $data['payment_status'], (float) ($data['paid_amount'] ?? 0));

            $receivable = $transaction->receivable;
            $paymentChanged = $status !== $transaction->payment_status || $paid != (float) $transaction->paid_amount;

            if ($receivable && $receivable->payments()->exists() && $paymentChanged) {
                throw ValidationException::withMessages([
                    'payment_status' => 'Transaksi ini sudah punya cicilan. Ubah pembayaran lewat menu Piutang.',
                ]);
            }

            $this->ensureCustomerIfNotPaid($data, (float) $transaction->total_amount - $paid);

            $transaction->update([
                'id_customer'      => $data['id_customer'] ?? null,
                'transaction_date' => $data['transaction_date'],
                'payment_method'   => $data['payment_method'],
                'payment_status'   => $status,
                'paid_amount'      => $paid,
                'note'             => $data['note'] ?? null,
            ]);

            $this->syncReceivable($transaction->fresh());

            return $transaction;
        });
    }

    public function cancel(Transaction $transaction): void
    {
        if ($transaction->isCancelled()) {
            throw ValidationException::withMessages(['status' => 'Transaksi sudah dibatalkan sebelumnya.']);
        }

        DB::transaction(function () use ($transaction) {
            foreach ($transaction->details()->whereNotNull('id_item')->get() as $detail) {
                $item = Item::lockForUpdate()->find($detail->id_item);

                if ($item) {
                    $this->stock->change($item, 'cancellation', (float) $detail->quantity, 'Dibatalkan: ' . $transaction->invoice_number, $transaction->id_transaction);
                }
            }

            $transaction->receivable()->delete();
            $transaction->update(['status' => 'cancelled']);
        });
    }

    private function buildDetails(array $lines): array
    {
        $details = [];
        $total   = 0;

        $merged = collect($lines)->groupBy('id_item')->map(fn ($rows) => $rows->sum('quantity'));

        foreach ($merged as $idItem => $quantity) {
            $item = Item::lockForUpdate()->find($idItem);

            if (! $item) {
                throw ValidationException::withMessages(['items' => 'Ada item yang tidak ditemukan.']);
            }

            if (! $item->is_service && (float) $item->stock < $quantity) {
                throw ValidationException::withMessages([
                    'items' => "Stok {$item->item_name} tidak cukup (tersisa " . qty($item->stock) . ').',
                ]);
            }

            $subtotal = (float) $item->selling_price * $quantity;
            $total   += $subtotal;

            $details[] = [
                'item'   => $item,
                'detail' => [
                    'id_item'       => $item->id_item,
                    'item_name'     => $item->item_name,
                    'selling_price' => $item->selling_price,
                    'cost_price'    => $item->cost_price,
                    'quantity'      => $quantity,
                    'subtotal'      => $subtotal,
                ],
            ];
        }

        return [$details, $total];
    }

    private function resolvePayment(float $total, string $status, float $paidInput): array
    {
        if ($status === 'paid') {
            return ['paid', $total];
        }

        if ($status === 'unpaid') {
            return ['unpaid', 0.0];
        }

        if ($paidInput <= 0 || $paidInput >= $total) {
            throw ValidationException::withMessages([
                'paid_amount' => 'Nominal DP harus lebih dari 0 dan kurang dari total (' . rupiah($total) . ').',
            ]);
        }

        return ['down_payment', $paidInput];
    }

    private function ensureCustomerIfNotPaid(array $data, float $remaining): void
    {
        if ($remaining > 0 && empty($data['id_customer'])) {
            throw ValidationException::withMessages([
                'id_customer' => 'Pilih pelanggan untuk transaksi belum lunas / DP (agar tercatat sebagai piutang).',
            ]);
        }
    }

    private function syncReceivable(Transaction $transaction): void
    {
        $remaining  = $transaction->remaining;
        $receivable = $transaction->receivable;

        if ($remaining <= 0) {
            $receivable?->delete();

            return;
        }

        $attributes = [
            'id_customer' => $transaction->id_customer,
            'amount'      => $remaining,
            'note'        => 'Dari transaksi ' . $transaction->invoice_number,
        ];

        if ($receivable) {
            $receivable->update($attributes);
            $receivable->refreshStatus();

            return;
        }

        Receivable::create($attributes + [
            'id_transaction'  => $transaction->id_transaction,
            'id_user'         => auth()->id(),
            'receivable_date' => $transaction->transaction_date->toDateString(),
            'status'          => 'unpaid',
        ]);
    }

    private function nextInvoiceNumber(string $date): string
    {
        $prefix = 'INV-' . date('Ymd', strtotime($date)) . '-';

        $last = Transaction::where('invoice_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->max('invoice_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
