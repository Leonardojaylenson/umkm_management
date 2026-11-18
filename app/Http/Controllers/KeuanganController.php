<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Http\Request;

class KeuanganController extends Controller
{
    public function expenseIndex(Request $request)
    {
        $bid  = $this->businessId();
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $query = Expense::where('id_business', $bid)->whereBetween('expense_date', [$from, $to]);

        if ($request->category) {
            $query->where('id_expense_category', $request->category);
        }

        $total    = (clone $query)->sum('amount');
        $expenses = $query->with('category')->latest('expense_date')->latest('id_expense')->paginate(10)->withQueryString();

        $perCategory = Expense::with('category')
            ->where('id_business', $bid)
            ->whereBetween('expense_date', [$from, $to])
            ->selectRaw('id_expense_category, SUM(amount) AS total')
            ->groupBy('id_expense_category')
            ->get();

        $categories = ExpenseCategory::where('id_business', $bid)->orderBy('category_name')->get();

        return view('keuangan.pengeluaran', compact('expenses', 'total', 'perCategory', 'categories', 'from', 'to'));
    }

    public function expenseCreate()
    {
        $expense    = new Expense(['expense_date' => now()]);
        $categories = ExpenseCategory::where('id_business', $this->businessId())->orderBy('category_name')->get();

        return view('keuangan.pengeluaran-form', compact('expense', 'categories'));
    }

    public function expenseStore(Request $request)
    {
        $bid = $this->businessId();

        $request->validate([
            'id_expense_category' => 'required|exists:expense_categories,id_expense_category,id_business,' . $bid,
            'expense_date'        => 'required|date',
            'amount'              => 'required|numeric|min:1',
            'note'                => 'nullable|max:255',
        ]);

        Expense::create([
            'id_business'         => $bid,
            'id_expense_category' => $request->id_expense_category,
            'id_user'             => auth()->id(),
            'expense_date'        => $request->expense_date,
            'amount'              => $request->amount,
            'note'                => $request->note,
        ]);

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function expenseEdit($id)
    {
        $bid        = $this->businessId();
        $expense    = Expense::where('id_business', $bid)->findOrFail($id);
        $categories = ExpenseCategory::where('id_business', $bid)->orderBy('category_name')->get();

        return view('keuangan.pengeluaran-form', compact('expense', 'categories'));
    }

    public function expenseUpdate(Request $request, $id)
    {
        $bid     = $this->businessId();
        $expense = Expense::where('id_business', $bid)->findOrFail($id);

        $request->validate([
            'id_expense_category' => 'required|exists:expense_categories,id_expense_category,id_business,' . $bid,
            'expense_date'        => 'required|date',
            'amount'              => 'required|numeric|min:1',
            'note'                => 'nullable|max:255',
        ]);

        $expense->update([
            'id_expense_category' => $request->id_expense_category,
            'expense_date'        => $request->expense_date,
            'amount'              => $request->amount,
            'note'                => $request->note,
        ]);

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function expenseDestroy($id)
    {
        $expense = Expense::where('id_business', $this->businessId())->findOrFail($id);
        $expense->delete();

        return redirect()->route('pengeluaran.index')->with('success', 'Catatan pengeluaran berhasil dihapus.');
    }

    public function expenseCategoryIndex()
    {
        $rows    = ExpenseCategory::withCount('expenses')->where('id_business', $this->businessId())->orderBy('category_name')->get();
        $editing = null;

        return view('keuangan.kategori-pengeluaran', compact('rows', 'editing'));
    }

    public function expenseCategoryEdit($id)
    {
        $bid     = $this->businessId();
        $rows    = ExpenseCategory::withCount('expenses')->where('id_business', $bid)->orderBy('category_name')->get();
        $editing = ExpenseCategory::where('id_business', $bid)->findOrFail($id);

        return view('keuangan.kategori-pengeluaran', compact('rows', 'editing'));
    }

    public function expenseCategoryStore(Request $request)
    {
        $bid = $this->businessId();

        $request->validate([
            'category_name' => 'required|max:100|unique:expense_categories,category_name,NULL,id_expense_category,id_business,' . $bid,
        ]);

        ExpenseCategory::create(['id_business' => $bid, 'category_name' => $request->category_name]);

        return redirect()->route('kategori-pengeluaran.index')->with('success', 'Kategori pengeluaran berhasil ditambahkan.');
    }

    public function expenseCategoryUpdate(Request $request, $id)
    {
        $bid      = $this->businessId();
        $category = ExpenseCategory::where('id_business', $bid)->findOrFail($id);

        $request->validate([
            'category_name' => 'required|max:100|unique:expense_categories,category_name,' . $category->id_expense_category . ',id_expense_category,id_business,' . $bid,
        ]);

        $category->update(['category_name' => $request->category_name]);

        return redirect()->route('kategori-pengeluaran.index')->with('success', 'Kategori pengeluaran berhasil diperbarui.');
    }

    public function expenseCategoryDestroy($id)
    {
        $category = ExpenseCategory::where('id_business', $this->businessId())->findOrFail($id);

        $dipakai = $category->expenses()->count();

        if ($dipakai > 0) {
            return back()->with('error', 'Kategori pengeluaran tidak bisa dihapus karena masih dipakai oleh ' . $dipakai . ' catatan pengeluaran.');
        }

        $category->delete();

        return redirect()->route('kategori-pengeluaran.index')->with('success', 'Kategori pengeluaran berhasil dihapus.');
    }

    public function supplierIndex(Request $request)
    {
        $query = Supplier::withCount('debts')->where('id_business', $this->businessId());

        if ($request->q) {
            $query->where('supplier_name', 'like', '%' . $request->q . '%');
        }

        $suppliers = $query->orderBy('supplier_name')->paginate(10)->withQueryString();

        return view('keuangan.supplier', compact('suppliers'));
    }

    public function supplierCreate()
    {
        $supplier = new Supplier();

        return view('keuangan.supplier-form', compact('supplier'));
    }

    public function supplierStore(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|max:150',
            'phone'         => 'nullable|max:20',
            'address'       => 'nullable|max:1000',
        ]);

        Supplier::create([
            'id_business'   => $this->businessId(),
            'supplier_name' => $request->supplier_name,
            'phone'         => $request->phone,
            'address'       => $request->address,
        ]);

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function supplierEdit($id)
    {
        $supplier = Supplier::where('id_business', $this->businessId())->findOrFail($id);

        return view('keuangan.supplier-form', compact('supplier'));
    }

    public function supplierUpdate(Request $request, $id)
    {
        $supplier = Supplier::where('id_business', $this->businessId())->findOrFail($id);

        $request->validate([
            'supplier_name' => 'required|max:150',
            'phone'         => 'nullable|max:20',
            'address'       => 'nullable|max:1000',
        ]);

        $supplier->update([
            'supplier_name' => $request->supplier_name,
            'phone'         => $request->phone,
            'address'       => $request->address,
        ]);

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function supplierDestroy($id)
    {
        $supplier = Supplier::where('id_business', $this->businessId())->findOrFail($id);

        if ($supplier->debts()->count() > 0) {
            return back()->with('error', 'Supplier tidak bisa dihapus karena masih punya catatan hutang.');
        }

        $supplier->delete();

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil dihapus.');
    }

    public function debtIndex(Request $request)
    {
        $bid = $this->businessId();

        $query = Debt::with('supplier')->where('id_business', $bid);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $debts = $query->orderByRaw("status = 'paid'")->orderByRaw('due_date IS NULL')->orderBy('due_date')
            ->paginate(10)->withQueryString();

        $outstanding = Debt::where('id_business', $bid)->where('status', '!=', 'paid')
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) AS total')->value('total');

        return view('keuangan.hutang', compact('debts', 'outstanding'));
    }

    public function debtCreate()
    {
        $debt      = new Debt(['debt_date' => now()]);
        $suppliers = Supplier::where('id_business', $this->businessId())->orderBy('supplier_name')->get();

        return view('keuangan.hutang-form', compact('debt', 'suppliers'));
    }

    public function debtStore(Request $request)
    {
        $bid = $this->businessId();

        $request->validate([
            'id_supplier' => 'required|exists:suppliers,id_supplier,id_business,' . $bid,
            'amount'      => 'required|numeric|min:1',
            'debt_date'   => 'required|date',
            'due_date'    => 'nullable|date|after_or_equal:debt_date',
            'note'        => 'nullable|max:255',
        ]);

        Debt::create([
            'id_business' => $bid,
            'id_supplier' => $request->id_supplier,
            'id_user'     => auth()->id(),
            'note'        => $request->note,
            'amount'      => $request->amount,
            'paid_amount' => 0,
            'debt_date'   => $request->debt_date,
            'due_date'    => $request->due_date,
            'status'      => 'unpaid',
        ]);

        return redirect()->route('hutang.index')->with('success', 'Hutang berhasil dicatat.');
    }

    public function debtShow($id)
    {
        $debt = Debt::with(['supplier', 'payments'])->where('id_business', $this->businessId())->findOrFail($id);

        return view('keuangan.hutang-detail', compact('debt'));
    }

    public function debtEdit($id)
    {
        $bid       = $this->businessId();
        $debt      = Debt::where('id_business', $bid)->findOrFail($id);
        $suppliers = Supplier::where('id_business', $bid)->orderBy('supplier_name')->get();

        return view('keuangan.hutang-form', compact('debt', 'suppliers'));
    }

    public function debtUpdate(Request $request, $id)
    {
        $bid  = $this->businessId();
        $debt = Debt::where('id_business', $bid)->findOrFail($id);

        $request->validate([
            'id_supplier' => 'required|exists:suppliers,id_supplier,id_business,' . $bid,
            'amount'      => 'required|numeric|min:1',
            'debt_date'   => 'required|date',
            'due_date'    => 'nullable|date|after_or_equal:debt_date',
            'note'        => 'nullable|max:255',
        ]);

        if ($request->amount < $debt->paid_amount) {
            return back()->withInput()->withErrors(['amount' => 'Total hutang tidak boleh kurang dari yang sudah dibayar (' . rupiah($debt->paid_amount) . ').']);
        }

        $debt->update([
            'id_supplier' => $request->id_supplier,
            'amount'      => $request->amount,
            'debt_date'   => $request->debt_date,
            'due_date'    => $request->due_date,
            'note'        => $request->note,
            'status'      => $this->tentukanStatus($request->amount, $debt->paid_amount),
        ]);

        return redirect()->route('hutang.show', $debt->id_debt)->with('success', 'Hutang berhasil diperbarui.');
    }

    public function debtDestroy($id)
    {
        $debt = Debt::where('id_business', $this->businessId())->findOrFail($id);
        $debt->delete();

        return redirect()->route('hutang.index')->with('success', 'Catatan hutang berhasil dihapus.');
    }

    public function debtPay(Request $request, $id)
    {
        $debt = Debt::where('id_business', $this->businessId())->findOrFail($id);

        $request->validate([
            'amount'       => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'note'         => 'nullable|max:255',
        ]);

        $sisa = $debt->amount - $debt->paid_amount;

        if ($request->amount > $sisa) {
            return back()->withInput()->withErrors(['amount' => 'Jumlah bayar tidak boleh melebihi sisa (' . rupiah($sisa) . ').']);
        }

        DebtPayment::create([
            'id_debt'      => $debt->id_debt,
            'id_user'      => auth()->id(),
            'amount'       => $request->amount,
            'payment_date' => $request->payment_date,
            'note'         => $request->note,
        ]);

        $paidBaru = $debt->paid_amount + $request->amount;

        $debt->update([
            'paid_amount' => $paidBaru,
            'status'      => $this->tentukanStatus($debt->amount, $paidBaru),
        ]);

        return back()->with('success', 'Pembayaran hutang berhasil dicatat.');
    }

    public function receivableIndex(Request $request)
    {
        $bid = $this->businessId();

        $query = Receivable::with('customer')->where('id_business', $bid);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $receivables = $query->orderByRaw("status = 'paid'")->orderByRaw('due_date IS NULL')->orderBy('due_date')
            ->paginate(10)->withQueryString();

        $outstanding = Receivable::where('id_business', $bid)->where('status', '!=', 'paid')
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) AS total')->value('total');

        return view('keuangan.piutang', compact('receivables', 'outstanding'));
    }

    public function receivableCreate()
    {
        $receivable = new Receivable(['receivable_date' => now()]);
        $customers  = Customer::where('id_business', $this->businessId())->orderBy('customer_name')->get();

        return view('keuangan.piutang-form', compact('receivable', 'customers'));
    }

    public function receivableStore(Request $request)
    {
        $bid = $this->businessId();

        $request->validate([
            'id_customer'     => 'required|exists:customers,id_customer,id_business,' . $bid,
            'amount'          => 'required|numeric|min:1',
            'receivable_date' => 'required|date',
            'due_date'        => 'nullable|date|after_or_equal:receivable_date',
            'note'            => 'nullable|max:255',
        ]);

        Receivable::create([
            'id_business'     => $bid,
            'id_customer'     => $request->id_customer,
            'id_user'         => auth()->id(),
            'note'            => $request->note,
            'amount'          => $request->amount,
            'paid_amount'     => 0,
            'receivable_date' => $request->receivable_date,
            'due_date'        => $request->due_date,
            'status'          => 'unpaid',
        ]);

        return redirect()->route('piutang.index')->with('success', 'Piutang berhasil dicatat.');
    }

    public function receivableShow($id)
    {
        $receivable = Receivable::with(['customer', 'payments', 'transaction'])
            ->where('id_business', $this->businessId())->findOrFail($id);

        return view('keuangan.piutang-detail', compact('receivable'));
    }

    public function receivableEdit($id)
    {
        $bid        = $this->businessId();
        $receivable = Receivable::where('id_business', $bid)->findOrFail($id);
        $customers  = Customer::where('id_business', $bid)->orderBy('customer_name')->get();

        return view('keuangan.piutang-form', compact('receivable', 'customers'));
    }

    public function receivableUpdate(Request $request, $id)
    {
        $bid        = $this->businessId();
        $receivable = Receivable::where('id_business', $bid)->findOrFail($id);

        $request->validate([
            'id_customer'     => 'required|exists:customers,id_customer,id_business,' . $bid,
            'amount'          => 'required|numeric|min:1',
            'receivable_date' => 'required|date',
            'due_date'        => 'nullable|date|after_or_equal:receivable_date',
            'note'            => 'nullable|max:255',
        ]);

        if ($receivable->id_transaction) {
            $receivable->update([
                'due_date' => $request->due_date,
                'note'     => $request->note,
            ]);

            return redirect()->route('piutang.show', $receivable->id_receivable)->with('success', 'Piutang berhasil diperbarui.');
        }

        if ($request->amount < $receivable->paid_amount) {
            return back()->withInput()->withErrors(['amount' => 'Total piutang tidak boleh kurang dari yang sudah dibayar (' . rupiah($receivable->paid_amount) . ').']);
        }

        $receivable->update([
            'id_customer'     => $request->id_customer,
            'amount'          => $request->amount,
            'receivable_date' => $request->receivable_date,
            'due_date'        => $request->due_date,
            'note'            => $request->note,
            'status'          => $this->tentukanStatus($request->amount, $receivable->paid_amount),
        ]);

        return redirect()->route('piutang.show', $receivable->id_receivable)->with('success', 'Piutang berhasil diperbarui.');
    }

    public function receivableDestroy($id)
    {
        $receivable = Receivable::where('id_business', $this->businessId())->findOrFail($id);

        if ($receivable->id_transaction) {
            return back()->with('error', 'Piutang ini berasal dari transaksi. Ubah atau batalkan transaksinya untuk menghapus piutang.');
        }

        $receivable->delete();

        return redirect()->route('piutang.index')->with('success', 'Catatan piutang berhasil dihapus.');
    }

    public function receivablePay(Request $request, $id)
    {
        $receivable = Receivable::where('id_business', $this->businessId())->findOrFail($id);

        $request->validate([
            'amount'       => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'note'         => 'nullable|max:255',
        ]);

        $sisa = $receivable->amount - $receivable->paid_amount;

        if ($request->amount > $sisa) {
            return back()->withInput()->withErrors(['amount' => 'Jumlah bayar tidak boleh melebihi sisa (' . rupiah($sisa) . ').']);
        }

        ReceivablePayment::create([
            'id_receivable' => $receivable->id_receivable,
            'id_user'       => auth()->id(),
            'amount'        => $request->amount,
            'payment_date'  => $request->payment_date,
            'note'          => $request->note,
        ]);

        $paidBaru = $receivable->paid_amount + $request->amount;

        $receivable->update([
            'paid_amount' => $paidBaru,
            'status'      => $this->tentukanStatus($receivable->amount, $paidBaru),
        ]);

        if ($receivable->id_transaction) {
            $transaction = Transaction::find($receivable->id_transaction);

            $transaction->paid_amount = $transaction->paid_amount + $request->amount;
            $transaction->payment_status = $transaction->paid_amount >= $transaction->total_amount ? 'paid' : 'down_payment';
            $transaction->save();
        }

        return back()->with('success', 'Pembayaran piutang berhasil dicatat.');
    }

    private function tentukanStatus($amount, $paid)
    {
        if ($paid >= $amount) {
            return 'paid';
        }

        if ($paid > 0) {
            return 'partial';
        }

        return 'unpaid';
    }
}
