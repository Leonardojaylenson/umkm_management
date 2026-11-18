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
          <a href="{{ route('dashboard') }}" class="sidebar-menu-link {{ request()->is('/') ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i><span>Dashboard</span>
          </a>
        </li>

        @foreach (config('menu.bisa_diatur') as $kode => $menu)
          @if (auth()->user()->canAccess($kode))
            @include('template.sidebar-item', ['kode' => $kode, 'menu' => $menu])
          @endif
        @endforeach

      </ul>
    </div>

    @if (auth()->user()->isOwner())
      <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">Pengaturan</div>
        <ul class="sidebar-menu-list">
          @foreach (config('menu.khusus_pemilik') as $kode => $menu)
            @include('template.sidebar-item', ['kode' => $kode, 'menu' => $menu])
          @endforeach
        </ul>
      </div>
    @endif

  </div>

  <a href="{{ route('profil') }}" class="sidebar-profile text-decoration-none">
    <img src="{{ asset('spark-template/assets/images/avatar.png') }}" alt="Pengguna" class="sidebar-profile-img">
    <div class="sidebar-profile-info">
      <div class="sidebar-profile-name">{{ auth()->user()->name }}</div>
      <div class="sidebar-profile-email">{{ auth()->user()->level->level_name ?? '-' }}</div>
    </div>
  </a>

</div>
