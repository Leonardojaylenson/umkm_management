@props(['paginator'])

<div class="table-footer-control">
  <span class="table-pagination-info">
    Menampilkan {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data
  </span>

  @if ($paginator->hasPages())
    <nav aria-label="Halaman">
      <ul class="pagination mb-0 gap-1">
        @foreach ($paginator->linkCollection() as $link)
          @php
            $label = match (true) {
                str_contains($link['label'], 'Previous') => '<i class="bi bi-chevron-left"></i>',
                str_contains($link['label'], 'Next')     => '<i class="bi bi-chevron-right"></i>',
                default                                  => $link['label'],
            };
          @endphp
          <li class="page-item {{ $link['active'] ? 'active' : '' }} {{ $link['url'] ? '' : 'disabled' }}">
            <a class="page-link border-0" href="{{ $link['url'] ?? '#' }}">{!! $label !!}</a>
          </li>
        @endforeach
      </ul>
    </nav>
  @endif
</div>
