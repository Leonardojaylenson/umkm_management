<li class="sidebar-menu-item">

  @if (count($menu['sub']) == 0)
    <a href="{{ url($menu['url']) }}" class="sidebar-menu-link {{ request()->is(...$menu['pola']) ? 'active' : '' }}">
      <i class="bi {{ $menu['ikon'] }}"></i><span>{{ $menu['nama'] }}</span>
    </a>
  @else
    <a href="#submenu-{{ $kode }}" class="sidebar-menu-link {{ request()->is(...$menu['pola']) ? 'active' : '' }}"
      data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is(...$menu['pola']) ? 'true' : 'false' }}">
      <i class="bi {{ $menu['ikon'] }}"></i><span>{{ $menu['nama'] }}</span>
      <i class="bi bi-chevron-down dropdown-caret"></i>
    </a>
    <ul class="sidebar-submenu collapse {{ request()->is(...$menu['pola']) ? 'show' : '' }}" id="submenu-{{ $kode }}">
      @foreach ($menu['sub'] as $sub)
        <li><a href="{{ url($sub[1]) }}" class="sidebar-submenu-link">{{ $sub[0] }}</a></li>
      @endforeach
    </ul>
  @endif

</li>
