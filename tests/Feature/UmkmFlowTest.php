<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Receivable;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UmkmFlowTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return User::where('email', 'owner@example.com')->firstOrFail();
    }

    private function cashier(): User
    {
        return User::where('email', 'cashier@example.com')->firstOrFail();
    }

    private function sale(array $overrides = [], array $items = null)
    {
        $nasi = Item::where('item_name', 'Nasi Goreng')->first();

        return $this->post('/transaksi', array_merge([
            'transaction_date' => now()->format('Y-m-d\TH:i'),
            'payment_method'   => 'cash',
            'payment_status'   => 'paid',
            'items'            => $items ?? [['id_item' => $nasi->id_item, 'quantity' => 2]],
        ], $overrides));
    }

    public function test_login_and_logout(): void
    {
        $this->post('/login', ['email' => 'owner@example.com', 'password' => 'password'])->assertRedirect('/');
        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_wrong_password_and_inactive_account_cannot_login(): void
    {
        $this->post('/login', ['email' => 'owner@example.com', 'password' => 'salah'])->assertSessionHasErrors('email');

        $cashier = $this->cashier();
        $cashier->update(['is_active' => false]);
        $this->post('/login', ['email' => $cashier->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_register_owner_then_create_business(): void
    {
        $this->post('/register', [
            'name' => 'Owner Baru', 'email' => 'baru@example.com',
            'password' => 'rahasia1', 'password_confirmation' => 'rahasia1',
        ])->assertRedirect('/usaha/buat');

        $this->get('/')->assertRedirect('/usaha/buat');

        $this->post('/usaha', ['business_name' => 'Toko Baru', 'business_type' => 'Retail'])->assertRedirect('/');

        $user = User::where('email', 'baru@example.com')->first();
        $this->assertNotNull($user->id_business);
        $this->assertDatabaseHas('expense_categories', ['id_business' => $user->id_business, 'category_name' => 'Gaji']);
        $this->get('/')->assertOk();
    }

    public function test_owner_manages_employee_accounts(): void
    {
        $this->actingAs($this->owner());

        $this->post('/pengguna', [
            'name' => 'Kasir Dua', 'email' => 'kasir2@example.com', 'role' => 'cashier',
            'password' => 'rahasia1', 'password_confirmation' => 'rahasia1',
        ])->assertRedirect('/pengguna');

        $kasir = User::where('email', 'kasir2@example.com')->first();
        $this->assertEquals(1, $kasir->id_business);

        $this->patch("/pengguna/{$kasir->id_user}/status")->assertSessionHas('success');
        $this->assertFalse($kasir->fresh()->is_active);

        $this->delete("/pengguna/{$kasir->id_user}")->assertRedirect('/pengguna');
        $this->assertNull(User::find($kasir->id_user));

        $this->delete('/pengguna/' . $this->owner()->id_user)->assertSessionHas('error');
    }

    public function test_all_owner_pages_load(): void
    {
        $this->actingAs($this->owner());

        foreach ([
            '/', '/profil', '/profil/password', '/usaha', '/usaha/ubah', '/pengguna', '/pengguna/create',
            '/item', '/item/create', '/kategori', '/satuan', '/transaksi', '/transaksi/create',
            '/pelanggan', '/pelanggan/create', '/pengeluaran', '/pengeluaran/create', '/kategori-pengeluaran',
            '/supplier', '/supplier/create', '/hutang', '/hutang/create', '/piutang', '/piutang/create',
            '/laporan', '/laporan/create', '/target', '/target/create',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_cashier_cannot_open_owner_pages_but_can_sell(): void
    {
        $this->actingAs($this->cashier());

        foreach (['/pengguna', '/item/create', '/kategori', '/pengeluaran', '/hutang', '/piutang', '/laporan', '/target', '/usaha'] as $url) {
            $this->get($url)->assertForbidden();
        }

        foreach (['/', '/item', '/transaksi', '/transaksi/create', '/pelanggan'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->sale()->assertRedirect();
    }

    public function test_item_crud_and_stock_history(): void
    {
        $this->actingAs($this->owner());

        $this->post('/item', [
            'item_name' => 'Kopi', 'selling_price' => 10000, 'cost_price' => 4000, 'stock' => 20, 'min_stock' => 5,
        ])->assertRedirect('/item');

        $item = Item::where('item_name', 'Kopi')->first();
        $this->assertEquals(20, $item->stock);
        $this->assertDatabaseHas('stock_histories', ['id_item' => $item->id_item, 'type' => 'in', 'quantity' => 20]);

        $this->post("/item/{$item->id_item}/stok", ['type' => 'adjustment', 'quantity' => 15])->assertSessionHas('success');
        $this->assertEquals(15, $item->fresh()->stock);
        $this->assertDatabaseHas('stock_histories', ['id_item' => $item->id_item, 'type' => 'adjustment', 'quantity' => -5]);

        $this->get("/item/{$item->id_item}")->assertOk()->assertSee('Koreksi');
        $this->get('/item?q=Kopi')->assertSee('Kopi');
        $this->get('/item?filter=stok-menipis')->assertSee('Mie Ayam')->assertDontSee('Kopi');

        $this->delete("/item/{$item->id_item}")->assertRedirect('/item');
        $this->assertNull(Item::find($item->id_item));
    }

    public function test_service_item_has_no_stock(): void
    {
        $this->actingAs($this->owner());

        $this->post('/item', [
            'item_name' => 'Jasa Antar', 'selling_price' => 5000, 'cost_price' => 0, 'is_service' => 1, 'stock' => 99,
        ]);

        $item = Item::where('item_name', 'Jasa Antar')->first();
        $this->assertTrue($item->is_service);
        $this->assertEquals(0, $item->stock);
    }

    public function test_category_in_use_cannot_be_deleted_but_unused_can(): void
    {
        $this->actingAs($this->owner());

        $this->delete('/kategori/1')->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id_category' => 1]);

        $this->post('/kategori', ['category_name' => 'Snack'])->assertRedirect('/kategori');
        $this->post('/kategori', ['category_name' => 'Snack'])->assertSessionHasErrors('category_name');

        $id = \App\Models\Category::where('category_name', 'Snack')->value('id_category');
        $this->get("/kategori/$id/edit")->assertOk()->assertSee('Snack');
        $this->put("/kategori/$id", ['category_name' => 'Snack Ringan'])->assertRedirect('/kategori');
        $this->delete("/kategori/$id")->assertSessionHas('success');
    }

    public function test_sale_reduces_stock_and_creates_invoice(): void
    {
        $this->actingAs($this->owner());
        $nasi = Item::where('item_name', 'Nasi Goreng')->first();
        $before = (float) $nasi->stock;

        $this->sale()->assertRedirect();

        $trx = Transaction::latest('id_transaction')->first();
        $this->assertMatchesRegularExpression('/^INV-\d{8}-0001$/', $trx->invoice_number);
        $this->assertEquals(36000, $trx->total_amount);
        $this->assertEquals(36000, $trx->paid_amount);
        $this->assertEquals($before - 2, (float) $nasi->fresh()->stock);
        $this->assertDatabaseHas('stock_histories', ['id_transaction' => $trx->id_transaction, 'type' => 'sale', 'quantity' => -2]);

        $this->get("/transaksi/{$trx->id_transaction}")->assertOk()->assertSee($trx->invoice_number);
        $this->get("/transaksi/{$trx->id_transaction}/nota")->assertOk()->assertSee('TOTAL');

        $this->sale();
        $this->assertTrue(Transaction::where('invoice_number', 'like', '%-0002')->exists());
    }

    public function test_sale_cannot_exceed_stock_and_rolls_back(): void
    {
        $this->actingAs($this->owner());
        $mie = Item::where('item_name', 'Mie Ayam')->first();

        $this->sale([], [['id_item' => $mie->id_item, 'quantity' => 9]])->assertSessionHasErrors('items');

        $this->assertEquals(8, (float) $mie->fresh()->stock);
        $this->assertEquals(0, Transaction::count());
    }

    public function test_service_item_sale_does_not_touch_stock(): void
    {
        $this->actingAs($this->owner());
        $jasa = Item::where('item_name', 'Sewa Tempat')->first();

        $this->sale([], [['id_item' => $jasa->id_item, 'quantity' => 3]])->assertRedirect();

        $this->assertEquals(0, (float) $jasa->fresh()->stock);
        $this->assertEquals(150000, Transaction::first()->total_amount);
    }

    public function test_unpaid_sale_requires_customer_and_creates_receivable(): void
    {
        $this->actingAs($this->owner());

        $this->sale(['payment_status' => 'unpaid'])->assertSessionHasErrors('id_customer');
        $this->assertEquals(0, Transaction::count());

        $this->sale(['payment_status' => 'unpaid', 'id_customer' => 1])->assertRedirect();
        $trx = Transaction::first();
        $this->assertEquals('unpaid', $trx->payment_status);
        $this->assertDatabaseHas('receivables', ['id_transaction' => $trx->id_transaction, 'amount' => 36000, 'status' => 'unpaid']);
    }

    public function test_down_payment_and_installments_settle_the_sale(): void
    {
        $this->actingAs($this->owner());

        $this->sale(['payment_status' => 'down_payment', 'id_customer' => 1, 'paid_amount' => 36000])->assertSessionHasErrors('paid_amount');

        $this->sale(['payment_status' => 'down_payment', 'id_customer' => 1, 'paid_amount' => 10000]);
        $trx = Transaction::first();
        $receivable = Receivable::where('id_transaction', $trx->id_transaction)->first();
        $this->assertEquals(26000, $receivable->amount);

        $this->post("/piutang/{$receivable->id_receivable}/bayar", ['amount' => 6000, 'payment_date' => now()->toDateString()]);
        $this->assertEquals('partial', $receivable->fresh()->status);
        $this->assertEquals('down_payment', $trx->fresh()->payment_status);

        $this->post("/piutang/{$receivable->id_receivable}/bayar", ['amount' => 99999, 'payment_date' => now()->toDateString()])->assertSessionHasErrors('amount');
        $this->post("/piutang/{$receivable->id_receivable}/bayar", ['amount' => 20000, 'payment_date' => now()->toDateString()]);

        $this->assertEquals('paid', $receivable->fresh()->status);
        $this->assertEquals('paid', $trx->fresh()->payment_status);
        $this->assertEquals(36000, (float) $trx->fresh()->paid_amount);
    }

    public function test_cancel_transaction_restores_stock_and_removes_receivable(): void
    {
        $this->actingAs($this->owner());
        $nasi = Item::where('item_name', 'Nasi Goreng')->first();
        $before = (float) $nasi->stock;

        $this->sale(['payment_status' => 'unpaid', 'id_customer' => 1]);
        $trx = Transaction::first();
        $this->assertEquals($before - 2, (float) $nasi->fresh()->stock);

        $this->post("/transaksi/{$trx->id_transaction}/batal")->assertSessionHas('success');

        $this->assertEquals($before, (float) $nasi->fresh()->stock);
        $this->assertEquals('cancelled', $trx->fresh()->status);
        $this->assertDatabaseHas('stock_histories', ['id_transaction' => $trx->id_transaction, 'type' => 'cancellation', 'quantity' => 2]);
        $this->assertEquals(0, Receivable::where('id_transaction', $trx->id_transaction)->count());

        $this->post("/transaksi/{$trx->id_transaction}/batal")->assertSessionHasErrors('status');
        $this->assertEquals($before, (float) $nasi->fresh()->stock);
    }

    public function test_correct_transaction_payment_status(): void
    {
        $this->actingAs($this->owner());

        $this->sale(['payment_status' => 'unpaid', 'id_customer' => 1]);
        $trx = Transaction::first();

        $this->put("/transaksi/{$trx->id_transaction}", [
            'transaction_date' => now()->format('Y-m-d\TH:i'), 'payment_method' => 'qris',
            'payment_status' => 'paid', 'id_customer' => 1,
        ])->assertRedirect();

        $this->assertEquals('paid', $trx->fresh()->payment_status);
        $this->assertEquals('qris', $trx->fresh()->payment_method);
        $this->assertEquals(0, Receivable::where('id_transaction', $trx->id_transaction)->count());
    }

    public function test_customer_crud_and_delete_guard(): void
    {
        $this->actingAs($this->owner());

        $this->post('/pelanggan', ['customer_name' => 'Siti'])->assertRedirect('/pelanggan');
        $siti = \App\Models\Customer::where('customer_name', 'Siti')->first();
        $this->get("/pelanggan/{$siti->id_customer}")->assertOk()->assertSee('Siti');
        $this->put("/pelanggan/{$siti->id_customer}", ['customer_name' => 'Siti A'])->assertRedirect('/pelanggan');
        $this->delete("/pelanggan/{$siti->id_customer}")->assertSessionHas('success');

        $this->sale(['payment_status' => 'unpaid', 'id_customer' => 1]);
        $this->delete('/pelanggan/1')->assertSessionHas('error');
    }

    public function test_expense_crud_and_category_guard(): void
    {
        $this->actingAs($this->owner());

        $this->post('/pengeluaran', ['id_expense_category' => 1, 'expense_date' => now()->toDateString(), 'amount' => 50000, 'note' => 'Beras'])
            ->assertRedirect('/pengeluaran');
        $expense = \App\Models\Expense::first();
        $this->get('/pengeluaran')->assertSee('Beras');

        $this->delete('/kategori-pengeluaran/1')->assertSessionHas('error');

        $this->put("/pengeluaran/{$expense->id_expense}", ['id_expense_category' => 1, 'expense_date' => now()->toDateString(), 'amount' => 60000]);
        $this->assertEquals(60000, $expense->fresh()->amount);

        $this->delete("/pengeluaran/{$expense->id_expense}");
        $this->delete('/kategori-pengeluaran/1')->assertSessionHas('success');
    }

    public function test_debt_installments(): void
    {
        $this->actingAs($this->owner());

        $this->post('/hutang', ['id_supplier' => 1, 'amount' => 100000, 'debt_date' => now()->toDateString(), 'due_date' => now()->addDays(3)->toDateString()])
            ->assertRedirect('/hutang');
        $debt = \App\Models\Debt::first();

        $this->post("/hutang/{$debt->id_debt}/bayar", ['amount' => 40000, 'payment_date' => now()->toDateString()]);
        $this->assertEquals('partial', $debt->fresh()->status);

        $this->put("/hutang/{$debt->id_debt}", ['id_supplier' => 1, 'amount' => 30000, 'debt_date' => now()->toDateString()])->assertSessionHasErrors('amount');

        $this->post("/hutang/{$debt->id_debt}/bayar", ['amount' => 60000, 'payment_date' => now()->toDateString()]);
        $this->assertEquals('paid', $debt->fresh()->status);

        $this->delete('/supplier/1')->assertSessionHas('error');
    }

    public function test_manual_receivable_can_be_deleted_but_transaction_one_cannot(): void
    {
        $this->actingAs($this->owner());

        $this->post('/piutang', ['id_customer' => 1, 'amount' => 20000, 'receivable_date' => now()->toDateString()])->assertRedirect('/piutang');
        $manual = Receivable::whereNull('id_transaction')->first();
        $this->delete("/piutang/{$manual->id_receivable}")->assertSessionHas('success');

        $this->sale(['payment_status' => 'unpaid', 'id_customer' => 1]);
        $fromTrx = Receivable::whereNotNull('id_transaction')->first();
        $this->delete("/piutang/{$fromTrx->id_receivable}")->assertSessionHas('error');
        $this->get("/piutang/{$fromTrx->id_receivable}")->assertOk();
        $this->get("/piutang/{$fromTrx->id_receivable}/edit")->assertOk();
    }

    public function test_report_and_target_calculations(): void
    {
        $this->actingAs($this->owner());

        $this->sale();
        $this->post('/pengeluaran', ['id_expense_category' => 1, 'expense_date' => now()->toDateString(), 'amount' => 5000]);

        $this->post('/laporan', ['report_name' => 'Bulan Ini', 'period_type' => 'monthly', 'reference_date' => now()->toDateString()])->assertRedirect();
        $report = \App\Models\Report::first();
        $this->assertEquals(36000, $report->total_revenue);
        $this->assertEquals(18000, $report->total_cost);
        $this->assertEquals(5000, $report->total_expense);
        $this->assertEquals(13000, $report->net_profit);
        $this->get("/laporan/{$report->id_report}")->assertOk()->assertSee('Nasi Goreng');

        $this->put("/laporan/{$report->id_report}", ['report_name' => 'Bulan Ini', 'period_type' => 'monthly',
            'reference_date' => now()->toDateString(), 'id_category' => [2]])->assertRedirect();
        $this->assertEquals(0, $report->fresh()->total_revenue);
        $this->assertEquals(['id_category' => [2]], $report->fresh()->filters);

        $this->post('/transaksi/' . Transaction::first()->id_transaction . '/batal');
        $this->post('/laporan', ['report_name' => 'Setelah Batal', 'period_type' => 'daily', 'reference_date' => now()->toDateString()]);
        $this->assertEquals(0, \App\Models\Report::where('report_name', 'Setelah Batal')->value('total_revenue'));

        $this->post('/target', ['period_type' => 'monthly', 'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(), 'target_amount' => 1000000])->assertRedirect('/target');
        $this->get('/target')->assertOk();
        $this->get('/')->assertOk()->assertSee('Target Omzet');

        $this->delete("/laporan/{$report->id_report}");
        $this->assertNull(\App\Models\Report::find($report->id_report));
    }

    public function test_data_of_other_business_is_not_accessible(): void
    {
        $other = \App\Models\Business::create(['business_name' => 'Usaha Lain', 'business_type' => 'Jasa']);
        $item = Item::withoutGlobalScopes()->create(['id_business' => $other->id_business, 'item_name' => 'Rahasia', 'selling_price' => 1, 'cost_price' => 0]);

        $this->actingAs($this->owner());

        $this->get("/item/{$item->id_item}")->assertNotFound();
        $this->get('/item')->assertDontSee('Rahasia');

        $this->sale([], [['id_item' => $item->id_item, 'quantity' => 1]])->assertSessionHasErrors('items.0.id_item');
    }
}
