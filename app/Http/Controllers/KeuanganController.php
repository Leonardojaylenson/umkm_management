<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesMasterData;
use App\Models\Customer;
use App\Models\Debt;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Receivable;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KeuanganController extends Controller
{
    use ManagesMasterData;

    public function expenseIndex(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $base = Expense::whereBetween('expense_date', [$from, $to])
            ->when($request->category, fn ($q, $id) => $q->where('id_expense_category', $id));

        return view('keuangan.pengeluaran', [
            'expenses'    => (clone $base)->with('category')->latest('expense_date')->latest('id_expense')->paginate(10)->withQueryString(),
            'total'       => (clone $base)->sum('amount'),
            'perCategory' => Expense::whereBetween('expense_date', [$from, $to])
                ->selectRaw('id_expense_category, SUM(amount) AS total')
                ->groupBy('id_expense_category')->with('category')->get(),
            'categories'  => ExpenseCategory::orderBy('category_name')->get(),
            'from'        => $from,
            'to'          => $to,
        ]);
    }

    public function expenseCreate()
    {
        return view('keuangan.pengeluaran-form', $this->expenseFormData(new Expense(['expense_date' => now()])));
    }

    public function expenseStore(Request $request)
    {
        Expense::create($this->validateExpense($request) + ['id_user' => auth()->id()]);

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function expenseEdit(Expense $expense)
    {
        return view('keuangan.pengeluaran-form', $this->expenseFormData($expense));
    }

    public function expenseUpdate(Request $request, Expense $expense)
    {
        $expense->update($this->validateExpense($request));

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function expenseDestroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('pengeluaran.index')->with('success', 'Catatan pengeluaran berhasil dihapus.');
    }

    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'id_expense_category' => ['required', $this->existsInBusiness('expense_categories', 'id_expense_category')],
            'expense_date'        => 'required|date',
            'amount'              => 'required|numeric|min:1',
            'note'                => 'nullable|string|max:255',
        ]);
    }

    private function expenseFormData(Expense $expense): array
    {
        return ['expense' => $expense, 'categories' => ExpenseCategory::orderBy('category_name')->get()];
    }

    private function expenseCategoryConfig(): array
    {
        return ['model' => ExpenseCategory::class, 'field' => 'category_name', 'base' => 'kategori-pengeluaran',
                'view' => 'keuangan.kategori-pengeluaran', 'usage' => 'expenses', 'usageLabel' => 'catatan pengeluaran',
                'label' => 'Kategori pengeluaran'];
    }

    public function expenseCategoryIndex()                       { return $this->masterPage($this->expenseCategoryConfig()); }
    public function expenseCategoryEdit(ExpenseCategory $expenseCategory) { return $this->masterPage($this->expenseCategoryConfig(), $expenseCategory); }
    public function expenseCategoryStore(Request $request)       { return $this->masterStore($request, $this->expenseCategoryConfig()); }
    public function expenseCategoryUpdate(Request $request, ExpenseCategory $expenseCategory) { return $this->masterUpdate($request, $this->expenseCategoryConfig(), $expenseCategory); }
    public function expenseCategoryDestroy(ExpenseCategory $expenseCategory) { return $this->masterDestroy($this->expenseCategoryConfig(), $expenseCategory); }

    public function supplierIndex(Request $request)
    {
        $suppliers = Supplier::withCount('debts')
            ->when($request->q, fn ($q, $s) => $q->where('supplier_name', 'like', "%$s%"))
            ->orderBy('supplier_name')
            ->paginate(10)->withQueryString();

        return view('keuangan.supplier', compact('suppliers'));
    }

    public function supplierCreate()
    {
        return view('keuangan.supplier-form', ['supplier' => new Supplier()]);
    }

    public function supplierStore(Request $request)
    {
        Supplier::create($this->validateSupplier($request));

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function supplierEdit(Supplier $supplier)
    {
        return view('keuangan.supplier-form', compact('supplier'));
    }

    public function supplierUpdate(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validateSupplier($request));

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function supplierDestroy(Supplier $supplier)
    {
        if ($supplier->debts()->exists()) {
            return back()->with('error', 'Supplier tidak bisa dihapus karena masih punya catatan hutang.');
        }

        $supplier->delete();

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil dihapus.');
    }

    private function validateSupplier(Request $request): array
    {
        return $request->validate([
            'supplier_name' => 'required|string|max:150',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:1000',
        ]);
    }

    public function debtIndex(Request $request)
    {
        $debts = Debt::with('supplier')
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->orderByRaw("status = 'paid'")
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->paginate(10)->withQueryString();

        $outstanding = Debt::where('status', '!=', 'paid')
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) AS total')->value('total');

        return view('keuangan.hutang', compact('debts', 'outstanding'));
    }

    public function debtCreate()
    {
        return view('keuangan.hutang-form', $this->debtFormData(new Debt(['debt_date' => now()])));
    }

    public function debtStore(Request $request)
    {
        Debt::create($this->validateDebt($request) + ['id_user' => auth()->id(), 'status' => 'unpaid', 'paid_amount' => 0]);

        return redirect()->route('hutang.index')->with('success', 'Hutang berhasil dicatat.');
    }

    public function debtShow(Debt $debt)
    {
        $debt->load(['supplier', 'payments']);

        return view('keuangan.hutang-detail', compact('debt'));
    }

    public function debtEdit(Debt $debt)
    {
        return view('keuangan.hutang-form', $this->debtFormData($debt));
    }

    public function debtUpdate(Request $request, Debt $debt)
    {
        $data = $this->validateDebt($request);

        if ($data['amount'] < (float) $debt->paid_amount) {
            throw ValidationException::withMessages([
                'amount' => 'Total hutang tidak boleh kurang dari yang sudah dibayar (' . rupiah($debt->paid_amount) . ').',
            ]);
        }

        $debt->update($data);
        $debt->refreshStatus();

        return redirect()->route('hutang.show', $debt)->with('success', 'Hutang berhasil diperbarui.');
    }

    public function debtDestroy(Debt $debt)
    {
        $debt->delete();

        return redirect()->route('hutang.index')->with('success', 'Catatan hutang berhasil dihapus.');
    }

    public function debtPay(Request $request, Debt $debt)
    {
        $debt->addPayment(...$this->validatePayment($request));

        return back()->with('success', 'Pembayaran hutang berhasil dicatat.');
    }

    private function validateDebt(Request $request): array
    {
        return $request->validate([
            'id_supplier' => ['required', $this->existsInBusiness('suppliers', 'id_supplier')],
            'amount'      => 'required|numeric|min:1',
            'debt_date'   => 'required|date',
            'due_date'    => 'nullable|date|after_or_equal:debt_date',
            'note'        => 'nullable|string|max:255',
        ]);
    }

    private function debtFormData(Debt $debt): array
    {
        return ['debt' => $debt, 'suppliers' => Supplier::orderBy('supplier_name')->get()];
    }

    public function receivableIndex(Request $request)
    {
        $receivables = Receivable::with('customer')
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->orderByRaw("status = 'paid'")
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->paginate(10)->withQueryString();

        $outstanding = Receivable::where('status', '!=', 'paid')
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) AS total')->value('total');

        return view('keuangan.piutang', compact('receivables', 'outstanding'));
    }

    public function receivableCreate()
    {
        return view('keuangan.piutang-form', $this->receivableFormData(new Receivable(['receivable_date' => now()])));
    }

    public function receivableStore(Request $request)
    {
        Receivable::create($this->validateReceivable($request) + ['id_user' => auth()->id(), 'status' => 'unpaid', 'paid_amount' => 0]);

        return redirect()->route('piutang.index')->with('success', 'Piutang berhasil dicatat.');
    }

    public function receivableShow(Receivable $receivable)
    {
        $receivable->load(['customer', 'payments', 'transaction']);

        return view('keuangan.piutang-detail', compact('receivable'));
    }

    public function receivableEdit(Receivable $receivable)
    {
        return view('keuangan.piutang-form', $this->receivableFormData($receivable));
    }

    public function receivableUpdate(Request $request, Receivable $receivable)
    {
        $data = $this->validateReceivable($request);

        if ($receivable->id_transaction) {
            $data = collect($data)->only(['due_date', 'note'])->all();
        } elseif ($data['amount'] < (float) $receivable->paid_amount) {
            throw ValidationException::withMessages([
                'amount' => 'Total piutang tidak boleh kurang dari yang sudah dibayar (' . rupiah($receivable->paid_amount) . ').',
            ]);
        }

        $receivable->update($data);
        $receivable->refreshStatus();

        return redirect()->route('piutang.show', $receivable)->with('success', 'Piutang berhasil diperbarui.');
    }

    public function receivableDestroy(Receivable $receivable)
    {
        if ($receivable->id_transaction) {
            return back()->with('error', 'Piutang ini berasal dari transaksi. Ubah atau batalkan transaksinya untuk menghapus piutang.');
        }

        $receivable->delete();

        return redirect()->route('piutang.index')->with('success', 'Catatan piutang berhasil dihapus.');
    }

    public function receivablePay(Request $request, Receivable $receivable)
    {
        $receivable->addPayment(...$this->validatePayment($request));

        return back()->with('success', 'Pembayaran piutang berhasil dicatat.');
    }

    private function validateReceivable(Request $request): array
    {
        return $request->validate([
            'id_customer'     => ['required', $this->existsInBusiness('customers', 'id_customer')],
            'amount'          => 'required|numeric|min:1',
            'receivable_date' => 'required|date',
            'due_date'        => 'nullable|date|after_or_equal:receivable_date',
            'note'            => 'nullable|string|max:255',
        ]);
    }

    private function receivableFormData(Receivable $receivable): array
    {
        return ['receivable' => $receivable, 'customers' => Customer::orderBy('customer_name')->get()];
    }

    private function validatePayment(Request $request): array
    {
        $data = $request->validate([
            'amount'       => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'note'         => 'nullable|string|max:255',
        ]);

        return [(float) $data['amount'], $data['payment_date'], $data['note'] ?? null];
    }
}
