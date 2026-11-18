<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $bid  = $this->businessId();
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $query = Transaction::with('customer')
            ->where('id_business', $bid)
            ->whereBetween('transaction_date', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->q) {
            $cari = $request->q;
            $query->where(function ($q) use ($cari) {
                $q->where('invoice_number', 'like', '%' . $cari . '%')
                  ->orWhereHas('customer', function ($c) use ($cari) {
                      $c->where('customer_name', 'like', '%' . $cari . '%');
                  });
            });
        }

        $transactions = $query->latest('transaction_date')->latest('id_transaction')->paginate(10)->withQueryString();

        return view('transaksi.index', compact('transactions', 'from', 'to'));
    }

    public function create()
    {
        $bid       = $this->businessId();
        $items     = Item::with('unit')->where('id_business', $bid)->orderBy('item_name')->get();
        $customers = Customer::where('id_business', $bid)->orderBy('customer_name')->get();

        return view('transaksi.create', compact('items', 'customers'));
    }

    public function store(Request $request)
    {
        $bid = $this->businessId();

        $request->validate([
            'transaction_date' => 'required|date',
            'id_customer'      => 'nullable|exists:customers,id_customer,id_business,' . $bid,
            'payment_method'   => 'required|in:cash,transfer,qris,e-wallet',
            'payment_status'   => 'required|in:paid,unpaid,down_payment',
            'paid_amount'      => 'nullable|numeric|min:0',
            'note'             => 'nullable|max:255',
            'items'            => 'required|array|min:1',
            'items.*.id_item'  => 'required|exists:items,id_item,id_business,' . $bid,
            'items.*.quantity' => 'required|numeric|min:0.01|max:999999',
        ], ['items.required' => 'Tambahkan minimal satu item.']);

        $jumlahPerItem = [];
        foreach ($request->items as $baris) {
            $idItem = $baris['id_item'];
            $jumlahPerItem[$idItem] = ($jumlahPerItem[$idItem] ?? 0) + $baris['quantity'];
        }

        $total   = 0;
        $dataItem = [];

        foreach ($jumlahPerItem as $idItem => $jumlah) {
            $item = Item::find($idItem);

            if (! $item->is_service && $item->stock < $jumlah) {
                return back()->withInput()->withErrors(['items' => 'Stok ' . $item->item_name . ' tidak cukup (tersisa ' . qty($item->stock) . ').']);
            }

            $subtotal = $item->selling_price * $jumlah;
            $total   += $subtotal;

            $dataItem[] = ['item' => $item, 'jumlah' => $jumlah, 'subtotal' => $subtotal];
        }

        $hasil = $this->hitungPembayaran($request->payment_status, $total, (float) $request->paid_amount);

        if ($hasil['error']) {
            return back()->withInput()->withErrors(['paid_amount' => $hasil['error']]);
        }

        $dibayar = $hasil['dibayar'];

        if ($dibayar < $total && ! $request->id_customer) {
            return back()->withInput()->withErrors(['id_customer' => 'Pilih pelanggan untuk transaksi belum lunas / DP (agar tercatat sebagai piutang).']);
        }

        DB::beginTransaction();

        $transaction = Transaction::create([
            'id_business'      => $bid,
            'id_customer'      => $request->id_customer,
            'id_user'          => auth()->id(),
            'invoice_number'   => $this->buatNomorNota($request->transaction_date),
            'transaction_date' => $request->transaction_date,
            'total_amount'     => $total,
            'paid_amount'      => $dibayar,
            'payment_method'   => $request->payment_method,
            'payment_status'   => $hasil['status'],
            'status'           => 'completed',
            'note'             => $request->note,
        ]);

        foreach ($dataItem as $baris) {
            $item = $baris['item'];

            TransactionDetail::create([
                'id_transaction' => $transaction->id_transaction,
                'id_item'        => $item->id_item,
                'item_name'      => $item->item_name,
                'selling_price'  => $item->selling_price,
                'cost_price'     => $item->cost_price,
                'quantity'       => $baris['jumlah'],
                'subtotal'       => $baris['subtotal'],
            ]);

            $this->changeStock($item, 'sale', -$baris['jumlah'], 'Penjualan ' . $transaction->invoice_number, $transaction->id_transaction);
        }

        $this->aturPiutang($transaction);

        DB::commit();

        return redirect()->route('transaksi.show', $transaction->id_transaction)->with('success', 'Penjualan berhasil dicatat.');
    }

    public function show($id)
    {
        $transaction = Transaction::with(['details', 'customer', 'cashier', 'receivable'])
            ->where('id_business', $this->businessId())->findOrFail($id);

        return view('transaksi.show', compact('transaction'));
    }

    public function print($id)
    {
        $transaction = Transaction::with(['details', 'customer', 'cashier'])
            ->where('id_business', $this->businessId())->findOrFail($id);
        $business = auth()->user()->business;

        return view('transaksi.nota', compact('transaction', 'business'));
    }

    public function edit($id)
    {
        $bid         = $this->businessId();
        $transaction = Transaction::with('details')->where('id_business', $bid)->findOrFail($id);
        $customers   = Customer::where('id_business', $bid)->orderBy('customer_name')->get();

        return view('transaksi.edit', compact('transaction', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $bid         = $this->businessId();
        $transaction = Transaction::where('id_business', $bid)->findOrFail($id);

        if ($transaction->status == 'cancelled') {
            return back()->withErrors(['status' => 'Transaksi yang sudah dibatalkan tidak bisa diubah.']);
        }

        $request->validate([
            'transaction_date' => 'required|date',
            'id_customer'      => 'nullable|exists:customers,id_customer,id_business,' . $bid,
            'payment_method'   => 'required|in:cash,transfer,qris,e-wallet',
            'payment_status'   => 'required|in:paid,unpaid,down_payment',
            'paid_amount'      => 'nullable|numeric|min:0',
            'note'             => 'nullable|max:255',
        ]);

        $hasil   = $this->hitungPembayaran($request->payment_status, $transaction->total_amount, (float) $request->paid_amount);
        $dibayar = $hasil['dibayar'];

        if ($hasil['error']) {
            return back()->withInput()->withErrors(['paid_amount' => $hasil['error']]);
        }

        $adaCicilan = false;
        if ($transaction->receivable) {
            $adaCicilan = ReceivablePayment::where('id_receivable', $transaction->receivable->id_receivable)->count() > 0;
        }

        $pembayaranBerubah = $hasil['status'] != $transaction->payment_status || $dibayar != $transaction->paid_amount;

        if ($adaCicilan && $pembayaranBerubah) {
            return back()->withInput()->withErrors(['payment_status' => 'Transaksi ini sudah punya cicilan. Ubah pembayaran lewat menu Piutang.']);
        }

        if ($dibayar < $transaction->total_amount && ! $request->id_customer) {
            return back()->withInput()->withErrors(['id_customer' => 'Pilih pelanggan untuk transaksi belum lunas / DP (agar tercatat sebagai piutang).']);
        }

        $transaction->update([
            'id_customer'      => $request->id_customer,
            'transaction_date' => $request->transaction_date,
            'payment_method'   => $request->payment_method,
            'payment_status'   => $hasil['status'],
            'paid_amount'      => $dibayar,
            'note'             => $request->note,
        ]);

        $this->aturPiutang($transaction);

        return redirect()->route('transaksi.show', $transaction->id_transaction)->with('success', 'Transaksi berhasil dikoreksi.');
    }

    public function cancel($id)
    {
        $transaction = Transaction::where('id_business', $this->businessId())->findOrFail($id);

        if ($transaction->status == 'cancelled') {
            return back()->withErrors(['status' => 'Transaksi sudah dibatalkan sebelumnya.']);
        }

        DB::beginTransaction();

        $details = TransactionDetail::where('id_transaction', $transaction->id_transaction)->whereNotNull('id_item')->get();

        foreach ($details as $detail) {
            $item = Item::find($detail->id_item);

            if ($item) {
                $this->changeStock($item, 'cancellation', $detail->quantity, 'Dibatalkan: ' . $transaction->invoice_number, $transaction->id_transaction);
            }
        }

        Receivable::where('id_transaction', $transaction->id_transaction)->delete();

        $transaction->update(['status' => 'cancelled']);

        DB::commit();

        return redirect()->route('transaksi.show', $transaction->id_transaction)->with('success', 'Transaksi dibatalkan dan stok dikembalikan.');
    }

    private function hitungPembayaran($status, $total, $dp)
    {
        if ($status == 'paid') {
            return ['status' => 'paid', 'dibayar' => $total, 'error' => null];
        }

        if ($status == 'unpaid') {
            return ['status' => 'unpaid', 'dibayar' => 0, 'error' => null];
        }

        if ($dp <= 0 || $dp >= $total) {
            return ['status' => 'down_payment', 'dibayar' => 0, 'error' => 'Nominal DP harus lebih dari 0 dan kurang dari total (' . rupiah($total) . ').'];
        }

        return ['status' => 'down_payment', 'dibayar' => $dp, 'error' => null];
    }

    private function buatNomorNota($tanggal)
    {
        $awalan = 'INV-' . date('Ymd', strtotime($tanggal)) . '-';

        $terakhir = Transaction::where('id_business', $this->businessId())
            ->where('invoice_number', 'like', $awalan . '%')
            ->max('invoice_number');

        $urutan = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;

        return $awalan . str_pad($urutan, 4, '0', STR_PAD_LEFT);
    }

    private function aturPiutang(Transaction $transaction)
    {
        $sisa      = $transaction->total_amount - $transaction->paid_amount;
        $piutang   = Receivable::where('id_transaction', $transaction->id_transaction)->first();
        $adaCicilan = $piutang && ReceivablePayment::where('id_receivable', $piutang->id_receivable)->count() > 0;

        if ($adaCicilan) {
            $piutang->update(['id_customer' => $transaction->id_customer]);
            return;
        }

        if ($sisa <= 0) {
            if ($piutang) {
                $piutang->delete();
            }
            return;
        }

        if ($piutang) {
            $piutang->update([
                'id_customer' => $transaction->id_customer,
                'amount'      => $sisa,
            ]);
            return;
        }

        Receivable::create([
            'id_business'     => $transaction->id_business,
            'id_customer'     => $transaction->id_customer,
            'id_transaction'  => $transaction->id_transaction,
            'id_user'         => auth()->id(),
            'note'            => 'Dari transaksi ' . $transaction->invoice_number,
            'amount'          => $sisa,
            'paid_amount'     => 0,
            'receivable_date' => $transaction->transaction_date->toDateString(),
            'status'          => 'unpaid',
        ]);
    }

    public function customerIndex(Request $request)
    {
        $query = Customer::withCount('transactions')->where('id_business', $this->businessId());

        if ($request->q) {
            $cari = $request->q;
            $query->where(function ($q) use ($cari) {
                $q->where('customer_name', 'like', '%' . $cari . '%')
                  ->orWhere('phone', 'like', '%' . $cari . '%');
            });
        }

        $customers = $query->orderBy('customer_name')->paginate(10)->withQueryString();

        return view('transaksi.pelanggan', compact('customers'));
    }

    public function customerCreate()
    {
        $customer = new Customer();

        return view('transaksi.pelanggan-form', compact('customer'));
    }

    public function customerStore(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|max:150',
            'phone'         => 'nullable|max:20',
            'address'       => 'nullable|max:1000',
        ]);

        Customer::create([
            'id_business'   => $this->businessId(),
            'customer_name' => $request->customer_name,
            'phone'         => $request->phone,
            'address'       => $request->address,
        ]);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function customerShow($id)
    {
        $customer     = Customer::where('id_business', $this->businessId())->findOrFail($id);
        $transactions = $customer->transactions()->latest('transaction_date')->limit(10)->get();
        $receivables  = $customer->receivables()->where('status', '!=', 'paid')->get();

        return view('transaksi.pelanggan-detail', compact('customer', 'transactions', 'receivables'));
    }

    public function customerEdit($id)
    {
        $customer = Customer::where('id_business', $this->businessId())->findOrFail($id);

        return view('transaksi.pelanggan-form', compact('customer'));
    }

    public function customerUpdate(Request $request, $id)
    {
        $customer = Customer::where('id_business', $this->businessId())->findOrFail($id);

        $request->validate([
            'customer_name' => 'required|max:150',
            'phone'         => 'nullable|max:20',
            'address'       => 'nullable|max:1000',
        ]);

        $customer->update([
            'customer_name' => $request->customer_name,
            'phone'         => $request->phone,
            'address'       => $request->address,
        ]);

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function customerDestroy($id)
    {
        $customer = Customer::where('id_business', $this->businessId())->findOrFail($id);

        if ($customer->receivables()->count() > 0) {
            return back()->with('error', 'Pelanggan tidak bisa dihapus karena masih punya catatan piutang.');
        }

        $customer->delete();

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus.');
    }
}
