<?php

use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UsahaController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login',     [UsahaController::class, 'showLogin'])->name('login');
    Route::post('login',    [UsahaController::class, 'login']);
    Route::get('register',  [UsahaController::class, 'showRegister'])->name('register');
    Route::post('register', [UsahaController::class, 'register']);
});

Route::middleware('auth')->group(function () {

    Route::post('logout', [UsahaController::class, 'logout'])->name('logout');

    Route::get('usaha/buat', [UsahaController::class, 'usahaCreate'])->name('usaha.create');
    Route::post('usaha',     [UsahaController::class, 'usahaStore'])->name('usaha.store');

    Route::middleware('business')->group(function () {

        Route::get('/', [LaporanController::class, 'dashboard'])->name('dashboard');

        Route::get('profil',          [UsahaController::class, 'profil'])->name('profil');
        Route::put('profil',          [UsahaController::class, 'profilUpdate'])->name('profil.update');
        Route::get('profil/password', [UsahaController::class, 'passwordForm'])->name('profil.password');
        Route::put('profil/password', [UsahaController::class, 'passwordUpdate'])->name('profil.password.update');

        Route::middleware('akses:item')->group(function () {
            Route::get('item',              [StokController::class, 'itemIndex'])->name('item.index');
            Route::get('item/create',       [StokController::class, 'itemCreate'])->name('item.create');
            Route::post('item',             [StokController::class, 'itemStore'])->name('item.store');
            Route::get('item/{id}',       [StokController::class, 'itemShow'])->name('item.show');
            Route::get('item/{id}/edit',  [StokController::class, 'itemEdit'])->name('item.edit');
            Route::put('item/{id}',       [StokController::class, 'itemUpdate'])->name('item.update');
            Route::delete('item/{id}',    [StokController::class, 'itemDestroy'])->name('item.destroy');
            Route::post('item/{id}/stok', [StokController::class, 'itemAdjustStock'])->name('item.stock');

            Route::get('kategori',                 [StokController::class, 'categoryIndex'])->name('kategori.index');
            Route::post('kategori',                [StokController::class, 'categoryStore'])->name('kategori.store');
            Route::get('kategori/{id}/edit', [StokController::class, 'categoryEdit'])->name('kategori.edit');
            Route::put('kategori/{id}',      [StokController::class, 'categoryUpdate'])->name('kategori.update');
            Route::delete('kategori/{id}',   [StokController::class, 'categoryDestroy'])->name('kategori.destroy');

            Route::get('satuan',             [StokController::class, 'unitIndex'])->name('satuan.index');
            Route::post('satuan',            [StokController::class, 'unitStore'])->name('satuan.store');
            Route::get('satuan/{id}/edit', [StokController::class, 'unitEdit'])->name('satuan.edit');
            Route::put('satuan/{id}',      [StokController::class, 'unitUpdate'])->name('satuan.update');
            Route::delete('satuan/{id}',   [StokController::class, 'unitDestroy'])->name('satuan.destroy');
        });

        Route::middleware('akses:transaksi')->group(function () {
            Route::get('transaksi',                      [TransaksiController::class, 'index'])->name('transaksi.index');
            Route::get('transaksi/create',               [TransaksiController::class, 'create'])->name('transaksi.create');
            Route::post('transaksi',                     [TransaksiController::class, 'store'])->name('transaksi.store');
            Route::get('transaksi/{id}',        [TransaksiController::class, 'show'])->name('transaksi.show');
            Route::get('transaksi/{id}/edit',   [TransaksiController::class, 'edit'])->name('transaksi.edit');
            Route::put('transaksi/{id}',        [TransaksiController::class, 'update'])->name('transaksi.update');
            Route::get('transaksi/{id}/nota',   [TransaksiController::class, 'print'])->name('transaksi.print');
            Route::post('transaksi/{id}/batal', [TransaksiController::class, 'cancel'])->name('transaksi.cancel');

            Route::get('pelanggan',                 [TransaksiController::class, 'customerIndex'])->name('pelanggan.index');
            Route::get('pelanggan/create',          [TransaksiController::class, 'customerCreate'])->name('pelanggan.create');
            Route::post('pelanggan',                [TransaksiController::class, 'customerStore'])->name('pelanggan.store');
            Route::get('pelanggan/{id}',      [TransaksiController::class, 'customerShow'])->name('pelanggan.show');
            Route::get('pelanggan/{id}/edit', [TransaksiController::class, 'customerEdit'])->name('pelanggan.edit');
            Route::put('pelanggan/{id}',      [TransaksiController::class, 'customerUpdate'])->name('pelanggan.update');
            Route::delete('pelanggan/{id}',   [TransaksiController::class, 'customerDestroy'])->name('pelanggan.destroy');
        });

        Route::middleware('akses:pengeluaran')->group(function () {
            Route::get('pengeluaran',                [KeuanganController::class, 'expenseIndex'])->name('pengeluaran.index');
            Route::get('pengeluaran/create',         [KeuanganController::class, 'expenseCreate'])->name('pengeluaran.create');
            Route::post('pengeluaran',               [KeuanganController::class, 'expenseStore'])->name('pengeluaran.store');
            Route::get('pengeluaran/{id}/edit', [KeuanganController::class, 'expenseEdit'])->name('pengeluaran.edit');
            Route::put('pengeluaran/{id}',      [KeuanganController::class, 'expenseUpdate'])->name('pengeluaran.update');
            Route::delete('pengeluaran/{id}',   [KeuanganController::class, 'expenseDestroy'])->name('pengeluaran.destroy');

            Route::get('kategori-pengeluaran',                        [KeuanganController::class, 'expenseCategoryIndex'])->name('kategori-pengeluaran.index');
            Route::post('kategori-pengeluaran',                       [KeuanganController::class, 'expenseCategoryStore'])->name('kategori-pengeluaran.store');
            Route::get('kategori-pengeluaran/{id}/edit', [KeuanganController::class, 'expenseCategoryEdit'])->name('kategori-pengeluaran.edit');
            Route::put('kategori-pengeluaran/{id}',      [KeuanganController::class, 'expenseCategoryUpdate'])->name('kategori-pengeluaran.update');
            Route::delete('kategori-pengeluaran/{id}',   [KeuanganController::class, 'expenseCategoryDestroy'])->name('kategori-pengeluaran.destroy');
        });

        Route::middleware('akses:hutang')->group(function () {
            Route::get('supplier',                 [KeuanganController::class, 'supplierIndex'])->name('supplier.index');
            Route::get('supplier/create',          [KeuanganController::class, 'supplierCreate'])->name('supplier.create');
            Route::post('supplier',                [KeuanganController::class, 'supplierStore'])->name('supplier.store');
            Route::get('supplier/{id}/edit', [KeuanganController::class, 'supplierEdit'])->name('supplier.edit');
            Route::put('supplier/{id}',      [KeuanganController::class, 'supplierUpdate'])->name('supplier.update');
            Route::delete('supplier/{id}',   [KeuanganController::class, 'supplierDestroy'])->name('supplier.destroy');

            Route::get('hutang',               [KeuanganController::class, 'debtIndex'])->name('hutang.index');
            Route::get('hutang/create',        [KeuanganController::class, 'debtCreate'])->name('hutang.create');
            Route::post('hutang',              [KeuanganController::class, 'debtStore'])->name('hutang.store');
            Route::get('hutang/{id}',        [KeuanganController::class, 'debtShow'])->name('hutang.show');
            Route::get('hutang/{id}/edit',   [KeuanganController::class, 'debtEdit'])->name('hutang.edit');
            Route::put('hutang/{id}',        [KeuanganController::class, 'debtUpdate'])->name('hutang.update');
            Route::delete('hutang/{id}',     [KeuanganController::class, 'debtDestroy'])->name('hutang.destroy');
            Route::post('hutang/{id}/bayar', [KeuanganController::class, 'debtPay'])->name('hutang.pay');

            Route::get('piutang',                     [KeuanganController::class, 'receivableIndex'])->name('piutang.index');
            Route::get('piutang/create',              [KeuanganController::class, 'receivableCreate'])->name('piutang.create');
            Route::post('piutang',                    [KeuanganController::class, 'receivableStore'])->name('piutang.store');
            Route::get('piutang/{id}',        [KeuanganController::class, 'receivableShow'])->name('piutang.show');
            Route::get('piutang/{id}/edit',   [KeuanganController::class, 'receivableEdit'])->name('piutang.edit');
            Route::put('piutang/{id}',        [KeuanganController::class, 'receivableUpdate'])->name('piutang.update');
            Route::delete('piutang/{id}',     [KeuanganController::class, 'receivableDestroy'])->name('piutang.destroy');
            Route::post('piutang/{id}/bayar', [KeuanganController::class, 'receivablePay'])->name('piutang.pay');
        });

        Route::middleware('akses:laporan')->group(function () {
            Route::get('laporan',               [LaporanController::class, 'reportIndex'])->name('laporan.index');
            Route::get('laporan/create',        [LaporanController::class, 'reportCreate'])->name('laporan.create');
            Route::post('laporan',              [LaporanController::class, 'reportStore'])->name('laporan.store');
            Route::get('laporan/{id}',      [LaporanController::class, 'reportShow'])->name('laporan.show');
            Route::get('laporan/{id}/edit', [LaporanController::class, 'reportEdit'])->name('laporan.edit');
            Route::put('laporan/{id}',      [LaporanController::class, 'reportUpdate'])->name('laporan.update');
            Route::delete('laporan/{id}',   [LaporanController::class, 'reportDestroy'])->name('laporan.destroy');

            Route::get('target',               [LaporanController::class, 'targetIndex'])->name('target.index');
            Route::get('target/create',        [LaporanController::class, 'targetCreate'])->name('target.create');
            Route::post('target',              [LaporanController::class, 'targetStore'])->name('target.store');
            Route::get('target/{id}/edit', [LaporanController::class, 'targetEdit'])->name('target.edit');
            Route::put('target/{id}',      [LaporanController::class, 'targetUpdate'])->name('target.update');
            Route::delete('target/{id}',   [LaporanController::class, 'targetDestroy'])->name('target.destroy');
        });

        Route::middleware('akses:pemilik')->group(function () {
            Route::get('usaha',      [UsahaController::class, 'usahaShow'])->name('usaha.show');
            Route::get('usaha/ubah', [UsahaController::class, 'usahaEdit'])->name('usaha.edit');
            Route::put('usaha',      [UsahaController::class, 'usahaUpdate'])->name('usaha.update');

            Route::get('pengguna',                     [UsahaController::class, 'userIndex'])->name('pengguna.index');
            Route::get('pengguna/create',              [UsahaController::class, 'userCreate'])->name('pengguna.create');
            Route::post('pengguna',                    [UsahaController::class, 'userStore'])->name('pengguna.store');
            Route::get('pengguna/{id}',          [UsahaController::class, 'userShow'])->name('pengguna.show');
            Route::get('pengguna/{id}/edit',     [UsahaController::class, 'userEdit'])->name('pengguna.edit');
            Route::put('pengguna/{id}',          [UsahaController::class, 'userUpdate'])->name('pengguna.update');
            Route::patch('pengguna/{id}/status', [UsahaController::class, 'userToggle'])->name('pengguna.toggle');
            Route::delete('pengguna/{id}',       [UsahaController::class, 'userDestroy'])->name('pengguna.destroy');

            Route::get('level',              [UsahaController::class, 'levelIndex'])->name('level.index');
            Route::post('level',             [UsahaController::class, 'levelStore'])->name('level.store');
            Route::get('level/{id}/edit', [UsahaController::class, 'levelEdit'])->name('level.edit');
            Route::put('level/{id}',      [UsahaController::class, 'levelUpdate'])->name('level.update');
            Route::delete('level/{id}',   [UsahaController::class, 'levelDestroy'])->name('level.destroy');
        });
    });
});
