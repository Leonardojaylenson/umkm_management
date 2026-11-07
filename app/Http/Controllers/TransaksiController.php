<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function __construct(private TransactionService $service)
    {
    }

    public function index(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $transactions = Transaction::with('customer')
            ->whereBetween('transaction_date', ["$from 00:00:00", "$to 23:59:59"])
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->payment_status, fn ($q, $v) => $q->where('payment_status', $v))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('invoice_number', 'like', "%$s%")
                ->orWhereHas('customer', fn ($c) => $c->where('customer_name', 'like', "%$s%"))))
            ->latest('transaction_date')->latest('id_transaction')
            ->paginate(10)->withQueryString();

        return view('transaksi.index', compact('transactions', 'from', 'to'));
    }

    public function create()
    {
        return view('transaksi.create', [
            'items'     => Item::with('unit')->orderBy('item_name')->get(),
            'customers' => Customer::orderBy('customer_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'transaction_date' => 'required|date',
            'id_customer'      => ['nullable', $this->existsInBusiness('customers', 'id_customer')],
            'payment_method'   => 'required|in:cash,transfer,qris,e-wallet',
            'payment_status'   => 'required|in:paid,unpaid,down_payment',
            'paid_amount'      => 'nullable|numeric|min:0',
            'note'             => 'nullable|string|max:255',
            'items'            => 'required|array|min:1',
            'items.*.id_item'  => ['required', $this->existsInBusiness('items', 'id_item')],
            'items.*.quantity' => 'required|numeric|min:0.01|max:999999',
        ], ['items.required' => 'Tambahkan minimal satu item.']);

        $transaction = $this->service->create($data, $data['items']);

        return redirect()->route('transaksi.show', $transaction)->with('success', 'Penjualan berhasil dicatat.');
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['details', 'customer', 'cashier', 'receivable']);

        return view('transaksi.show', compact('transaction'));
    }

    public function print(Transaction $transaction)
    {
        $transaction->load(['details', 'customer', 'cashier']);
        $business = auth()->user()->business;

        return view('transaksi.nota', compact('transaction', 'business'));
    }

    public function edit(Transaction $transaction)
    {
        return view('transaksi.edit', [
            'transaction' => $transaction->load('details'),
            'customers'   => Customer::orderBy('customer_name')->get(),
        ]);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'transaction_date' => 'required|date',
            'id_customer'      => ['nullable', $this->existsInBusiness('customers', 'id_customer')],
            'payment_method'   => 'required|in:cash,transfer,qris,e-wallet',
            'payment_status'   => 'required|in:paid,unpaid,down_payment',
            'paid_amount'      => 'nullable|numeric|min:0',
            'note'             => 'nullable|string|max:255',
        ]);

        $this->service->update($transaction, $data);

        return redirect()->route('transaksi.show', $transaction)->with('success', 'Transaksi berhasil dikoreksi.');
    }

    public function cancel(Transaction $transaction)
    {
        $this->service->cancel($transaction);

        return redirect()->route('transaksi.show', $transaction)->with('success', 'Transaksi dibatalkan dan stok dikembalikan.');
    }

    public function customerIndex(Request $request)
    {
        $customers = Customer::withCount('transactions')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('customer_name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%")))
            ->orderBy('customer_name')
            ->paginate(10)->withQueryString();

        return view('transaksi.pelanggan', compact('customers'));
    }

    public function customerCreate()
    {
        return view('transaksi.pelanggan-form', ['customer' => new Customer()]);
    }

    public function customerStore(Request $request)
    {
        Customer::create($this->validateCustomer($request));

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function customerShow(Customer $customer)
    {
        $transactions = $customer->transactions()->latest('transaction_date')->limit(10)->get();
        $receivables  = $customer->receivables()->where('status', '!=', 'paid')->get();

        return view('transaksi.pelanggan-detail', compact('customer', 'transactions', 'receivables'));
    }

    public function customerEdit(Customer $customer)
    {
        return view('transaksi.pelanggan-form', compact('customer'));
    }

    public function customerUpdate(Request $request, Customer $customer)
    {
        $customer->update($this->validateCustomer($request));

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function customerDestroy(Customer $customer)
    {
        if ($customer->receivables()->exists()) {
            return back()->with('error', 'Pelanggan tidak bisa dihapus karena masih punya catatan piutang.');
        }

        $customer->delete();

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus.');
    }

    private function validateCustomer(Request $request): array
    {
        return $request->validate([
            'customer_name' => 'required|string|max:150',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:1000',
        ]);
    }
}
