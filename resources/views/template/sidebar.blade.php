@php
  $isOwner = auth()->user()->isOwner();

  $menus = [
    'Menu' => [
      ['Dashboard', 'bi-grid-fill', '/', ['/'], [], false],
    ],
    'Manajemen Usaha' => [
      ['Profil Usaha & Pengguna', 'bi-shop', null, ['usaha*', 'pengguna*'], [
        ['Profil Usaha', 'usaha'],
        ['Daftar Pengguna', 'pengguna'],
      ], true],
      ['Item & Stok', 'bi-box-seam-fill', null, ['item*', 'kategori', 'kategori/*', 'satuan*'],
        $isOwner
          ? [['Daftar Item', 'item'], ['Kategori', 'kategori'], ['Satuan', 'satuan']]
          : [['Daftar Item', 'item']],
        false],
      ['Transaksi Penjualan', 'bi-cart-check-fill', null, ['transaksi*', 'pelanggan*'], [
        ['Penjualan Baru', 'transaksi/create'],
        ['Riwayat Transaksi', 'transaksi'],
        ['Pelanggan', 'pelanggan'],
      ], false],
    ],
    'Keuangan' => [
      ['Pengeluaran', 'bi-wallet2', null, ['pengeluaran*', 'kategori-pengeluaran*'], [
        ['Daftar Pengeluaran', 'pengeluaran'],
        ['Kategori Pengeluaran', 'kategori-pengeluaran'],
      ], true],
      ['Hutang & Piutang', 'bi-cash-coin', null, ['hutang*', 'piutang*', 'supplier*'], [
        ['Hutang (Supplier)', 'hutang'],
        ['Piutang (Pelanggan)', 'piutang'],
        ['Data Supplier', 'supplier'],
      ], true],
      ['Laporan & Target', 'bi-graph-up-arrow', null, ['laporan*', 'target*'], [
        ['Laporan', 'laporan'],
        ['Target Omzet', 'target'],
      ], true],
    ],
  ];
@endphp

<div class="sidebar-wrapper" id="sidebar">
  <a href="{{ route('dashboard') }}" class="sidebar-brand">
    <i class="bi bi-shop-window"></i>
    <span>{{ auth()->user()->business->business_name ?? 'UMKM Management' }}</span>
  </a>

  <div class="flex-grow-1 overflow-y-auto">
    @foreach ($menus as $group => $items)
      @php $items = collect($items)->filter(fn ($m) => $isOwner || ! $m[5]); @endphp
      @continue($items->isEmpty())

      <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">{{ $group }}</div>
        <ul class="sidebar-menu-list">
          @foreach ($items as $i => $m)
            @php
              $active = request()->is(...$m[3]);
              $id = 'submenu-' . \Illuminate\Support\Str::slug($group) . '-' . $i;
            @endphp
            <li class="sidebar-menu-item">
              @if (empty($m[4]))
                <a href="{{ url($m[2]) }}" class="sidebar-menu-link {{ $active ? 'active' : '' }}" title="{{ $m[0] }}">
                  <i class="bi {{ $m[1] }}"></i><span>{{ $m[0] }}</span>
                </a>
              @else
                <a href="#{{ $id }}" class="sidebar-menu-link {{ $active ? 'active' : '' }}"
                  data-bs-toggle="collapse" role="button"
                  aria-expanded="{{ $active ? 'true' : 'false' }}" title="{{ $m[0] }}">
                  <i class="bi {{ $m[1] }}"></i><span>{{ $m[0] }}</span>
                  <i class="bi bi-chevron-down dropdown-caret"></i>
                </a>
                <ul class="sidebar-submenu collapse {{ $active ? 'show' : '' }}" id="{{ $id }}">
                  @foreach ($m[4] as $sub)
                    <li><a href="{{ url($sub[1]) }}" class="sidebar-submenu-link">{{ $sub[0] }}</a></li>
                  @endforeach
                </ul>
              @endif
            </li>
          @endforeach
        </ul>
      </div>
    @endforeach
  </div>

  <a href="{{ route('profil') }}" class="sidebar-profile text-decoration-none">
    <img src="{{ asset('spark-template/assets/images/avatar.png') }}" alt="Pengguna" class="sidebar-profile-img">
    <div class="sidebar-profile-info">
      <div class="sidebar-profile-name">{{ auth()->user()->name }}</div>
      <div class="sidebar-profile-email">{{ auth()->user()->role_label }}</div>
    </div>
  </a>
</div>
