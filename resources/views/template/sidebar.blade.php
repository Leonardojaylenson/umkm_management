<div class="sidebar-wrapper" id="sidebar">
  <a href="{{ route('dashboard') }}" class="sidebar-brand">
    <i class="bi bi-shop-window"></i>
    <span>{{ auth()->user()->business->business_name ?? 'UMKM Management' }}</span>
  </a>

  <div class="flex-grow-1 overflow-y-auto">
    <div class="sidebar-menu-section">
      <div class="sidebar-menu-title">Menu</div>
      <ul class="sidebar-menu-list">
        <li class="sidebar-menu-item">
          <a href="{{ route('dashboard') }}" class="sidebar-menu-link active" title="Dashboard">
            <i class="bi bi-grid-fill"></i><span>Dashboard</span>
          </a>
        </li>
      </ul>
    </div>
  </div>

  <div class="sidebar-profile">
    <img src="{{ asset('spark-template/assets/images/avatar.png') }}" alt="Pengguna" class="sidebar-profile-img">
    <div class="sidebar-profile-info">
      <div class="sidebar-profile-name">{{ auth()->user()->name }}</div>
      <div class="sidebar-profile-email">{{ auth()->user()->role_label }}</div>
    </div>
  </div>
</div>
