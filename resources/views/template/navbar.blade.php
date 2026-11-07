@php $isOwner = auth()->user()->isOwner(); @endphp

<header class="navbar-custom">
  <div class="navbar-left">
    <button class="btn-desktop-toggle d-none d-xl-flex align-items-center justify-content-center me-3"
      id="desktop-sidebar-toggle" aria-label="Kecilkan Sidebar">
      <i class="bi bi-chevron-bar-left"></i>
    </button>
    <button class="sidebar-toggle-btn me-2" id="sidebar-toggle" aria-label="Buka Menu">
      <i class="bi bi-list"></i>
    </button>

    <div class="dropdown ms-2">
      <button class="btn-quick-action dropdown-toggle" type="button" data-bs-toggle="dropdown"
        aria-expanded="false" id="quick-actions-dropdown">
        <i class="bi bi-plus-lg"></i><span>Tambah</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-quick-action" aria-labelledby="quick-actions-dropdown">
        <li class="dropdown-header">Aksi Cepat</li>
        <li><a class="dropdown-item" href="{{ route('transaksi.create') }}"><i class="bi bi-cart-plus"></i> Penjualan Baru</a></li>
        <li><a class="dropdown-item" href="{{ route('pelanggan.create') }}"><i class="bi bi-person-plus"></i> Pelanggan Baru</a></li>
        @if ($isOwner)
          <li><a class="dropdown-item" href="{{ route('item.create') }}"><i class="bi bi-box-seam"></i> Item Baru</a></li>
          <li><a class="dropdown-item" href="{{ route('pengeluaran.create') }}"><i class="bi bi-wallet2"></i> Catat Pengeluaran</a></li>
        @endif
      </ul>
    </div>
  </div>

    <form action="{{ route('item.index') }}" method="GET" class="navbar-search-wrapper">
    <input type="text" name="q" class="navbar-search-input" placeholder="Cari item..." id="main-search" value="{{ request('q') }}">
    <button class="navbar-search-btn" aria-label="Cari"><i class="bi bi-search"></i></button>
  </form>

  <div class="navbar-actions">
    <button class="navbar-action-btn me-1" aria-label="Layar Penuh" id="btn-fullscreen">
      <i class="bi bi-arrows-fullscreen"></i>
    </button>

    <div class="dropdown">
      <button class="navbar-action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
        aria-expanded="false" id="btn-notifications" data-bs-auto-close="outside">
        <i class="bi bi-bell"></i>
        @if ($lowStockCount + $dueCount > 0)<span class="navbar-action-badge"></span>@endif
      </button>
      <div class="dropdown-menu dropdown-menu-end dropdown-menu-notification p-0" aria-labelledby="btn-notifications">
        <div class="notification-header">
          <h6 class="notification-title">Notifikasi</h6>
        </div>
        <div class="notification-list">
          @if ($lowStockCount > 0)
            <a href="{{ route('item.index', ['filter' => 'stok-menipis']) }}" class="notification-item">
              <div class="notification-icon bg-warning text-dark"><i class="bi bi-box-seam-fill"></i></div>
              <div class="notification-content">
                <p class="notification-text"><strong>{{ $lowStockCount }}</strong> item stoknya menipis</p>
                <span class="notification-time">Peringatan stok</span>
              </div>
            </a>
          @endif
          @if ($dueCount > 0)
            <a href="{{ route('hutang.index') }}" class="notification-item">
              <div class="notification-icon bg-danger text-white"><i class="bi bi-calendar-event"></i></div>
              <div class="notification-content">
                <p class="notification-text"><strong>{{ $dueCount }}</strong> hutang/piutang jatuh tempo &le; 7 hari</p>
                <span class="notification-time">Pengingat</span>
              </div>
            </a>
          @endif
          @if ($lowStockCount + $dueCount === 0)
            <div class="p-3 text-center text-muted-green">Tidak ada notifikasi.</div>
          @endif
        </div>
      </div>
    </div>

    <div class="dropdown ms-2">
      <button class="navbar-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
        aria-expanded="false" id="profile-dropdown">
        <img src="{{ asset('spark-template/assets/images/avatar.png') }}" alt="Profil" class="navbar-profile-img">
        <span class="navbar-profile-name d-none d-md-inline">{{ auth()->user()->name }}</span>
        <i class="bi bi-chevron-down navbar-profile-caret"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile" aria-labelledby="profile-dropdown">
        <li class="dropdown-header">{{ auth()->user()->role_label }}</li>
        <li><a class="dropdown-item" href="{{ route('profil') }}"><i class="bi bi-person"></i> Profil Saya</a></li>
        @if ($isOwner)
          <li><a class="dropdown-item" href="{{ route('usaha.show') }}"><i class="bi bi-shop"></i> Profil Usaha</a></li>
        @endif
        <li><a class="dropdown-item" href="{{ route('profil.password') }}"><i class="bi bi-lock"></i> Ubah Kata Sandi</a></li>
        <li><hr class="dropdown-divider"></li>
        <li>
                    <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Keluar</button>
          </form>
        </li>
      </ul>
    </div>
  </div>
</header>
