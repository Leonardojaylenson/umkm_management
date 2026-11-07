<header class="navbar-custom">
  <div class="navbar-left">
    <button class="btn-desktop-toggle d-none d-xl-flex align-items-center justify-content-center me-3"
      id="desktop-sidebar-toggle" aria-label="Kecilkan Sidebar">
      <i class="bi bi-chevron-bar-left"></i>
    </button>
    <button class="sidebar-toggle-btn me-2" id="sidebar-toggle" aria-label="Buka Menu">
      <i class="bi bi-list"></i>
    </button>
  </div>

  <div class="navbar-actions ms-auto">
    <button class="navbar-action-btn me-1" aria-label="Layar Penuh" id="btn-fullscreen">
      <i class="bi bi-arrows-fullscreen"></i>
    </button>

    <div class="dropdown">
      <button class="navbar-action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
        aria-expanded="false" id="btn-notifications" data-bs-auto-close="outside">
        <i class="bi bi-bell"></i>
        @if ($lowStockCount > 0)<span class="navbar-action-badge"></span>@endif
      </button>
      <div class="dropdown-menu dropdown-menu-end dropdown-menu-notification p-0" aria-labelledby="btn-notifications">
        <div class="notification-header">
          <h6 class="notification-title">Notifikasi</h6>
        </div>
        <div class="notification-list">
          @if ($lowStockCount > 0)
            <div class="notification-item">
              <div class="notification-icon bg-warning text-dark"><i class="bi bi-box-seam-fill"></i></div>
              <div class="notification-content">
                <p class="notification-text"><strong>{{ $lowStockCount }}</strong> item stoknya menipis</p>
                <span class="notification-time">Peringatan stok</span>
              </div>
            </div>
          @else
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
