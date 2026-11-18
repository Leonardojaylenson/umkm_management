<?php

return [

    'bisa_diatur' => [

        'item' => [
            'nama' => 'Item & Stok',
            'ikon' => 'bi-box-seam-fill',
            'url'  => 'item',
            'pola' => ['item*', 'kategori*', 'satuan*'],
            'sub'  => [
                ['Daftar Item', 'item'],
                ['Kategori', 'kategori'],
                ['Satuan', 'satuan'],
            ],
        ],

        'transaksi' => [
            'nama' => 'Transaksi Penjualan',
            'ikon' => 'bi-cart-check-fill',
            'url'  => 'transaksi',
            'pola' => ['transaksi*', 'pelanggan*'],
            'sub'  => [
                ['Penjualan Baru', 'transaksi/create'],
                ['Riwayat Transaksi', 'transaksi'],
                ['Pelanggan', 'pelanggan'],
            ],
        ],

        'pengeluaran' => [
            'nama' => 'Pengeluaran',
            'ikon' => 'bi-wallet2',
            'url'  => 'pengeluaran',
            'pola' => ['pengeluaran*', 'kategori-pengeluaran*'],
            'sub'  => [
                ['Daftar Pengeluaran', 'pengeluaran'],
                ['Kategori Pengeluaran', 'kategori-pengeluaran'],
            ],
        ],

        'hutang' => [
            'nama' => 'Hutang & Piutang',
            'ikon' => 'bi-cash-coin',
            'url'  => 'hutang',
            'pola' => ['hutang*', 'piutang*', 'supplier*'],
            'sub'  => [
                ['Hutang (Supplier)', 'hutang'],
                ['Piutang (Pelanggan)', 'piutang'],
                ['Data Supplier', 'supplier'],
            ],
        ],

        'laporan' => [
            'nama' => 'Laporan & Target',
            'ikon' => 'bi-graph-up-arrow',
            'url'  => 'laporan',
            'pola' => ['laporan*', 'target*'],
            'sub'  => [
                ['Laporan', 'laporan'],
                ['Target Omzet', 'target'],
            ],
        ],
    ],

    'khusus_pemilik' => [

        'usaha' => [
            'nama' => 'Profil Usaha',
            'ikon' => 'bi-shop',
            'url'  => 'usaha',
            'pola' => ['usaha*'],
            'sub'  => [],
        ],

        'pengguna' => [
            'nama' => 'Daftar Pengguna',
            'ikon' => 'bi-people-fill',
            'url'  => 'pengguna',
            'pola' => ['pengguna*'],
            'sub'  => [],
        ],

        'level' => [
            'nama' => 'Pengaturan Level',
            'ikon' => 'bi-shield-lock-fill',
            'url'  => 'level',
            'pola' => ['level*'],
            'sub'  => [],
        ],
    ],
];
