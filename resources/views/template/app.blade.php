<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') - UMKM Management</title>
  <link rel="icon" type="image/png" href="{{ asset('spark-template/assets/images/favicon.ico') }}">

  <link rel="stylesheet" href="{{ asset('spark-template/assets/libs/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('spark-template/assets/libs/bootstrap-icons/bootstrap-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('spark-template/assets/libs/apexcharts/apexcharts.css') }}">
  <link rel="stylesheet" href="{{ asset('spark-template/assets/libs/flatpickr/flatpickr.min.css') }}">
  <link rel="stylesheet" href="{{ asset('spark-template/assets/css/main.css') }}">
  <style>
    .btn-custom { white-space: nowrap; flex-shrink: 0; }
    .table-filter-group { flex-wrap: wrap; }
    .table-filter-group .form-select-custom,
    .table-filter-group .form-control-custom { width: auto; }
    .form-select-custom.form-control-custom-sm {
      padding: 0.35rem 2rem 0.35rem 0.75rem;
      font-size: 0.75rem;
      border-radius: var(--radius-md);
      background-position: right 0.75rem center;
    }
  </style>
  @stack('styles')
</head>

<body>
  @include('template.sidebar')

  <div class="main-wrapper">
    @include('template.navbar')

    <div class="page-header">
      <div>
        <h1 class="page-title">@yield('title', 'Dashboard')</h1>
        @hasSection('subtitle')
          <p class="page-subtitle">@yield('subtitle')</p>
        @endif
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted-green">Beranda</a></li>
          <li class="breadcrumb-item active text-main" aria-current="page">@yield('title', 'Dashboard')</li>
        </ol>
      </nav>
    </div>

    @include('template.alerts')

    @yield('content')

    @include('template.footer')
  </div>

  <div class="modal fade" id="modal-nota" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 360px">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title">Nota</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-0">
          <iframe id="frame-nota" style="width: 100%; height: 520px; border: 0"></iframe>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-custom btn-custom-light btn-custom-sm" data-bs-dismiss="modal">Tutup</button>
          <button type="button" class="btn-custom btn-custom-primary btn-custom-sm" onclick="cetakNota()"><i class="bi bi-printer"></i> Cetak</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function bukaNota(url) {
      document.getElementById('frame-nota').src = url;
      new bootstrap.Modal(document.getElementById('modal-nota')).show();
    }

    function cetakNota() {
      var frame = document.getElementById('frame-nota');
      frame.contentWindow.focus();
      frame.contentWindow.print();
    }
  </script>

  <script src="{{ asset('spark-template/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('spark-template/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
  <script src="{{ asset('spark-template/assets/libs/flatpickr/flatpickr.min.js') }}"></script>
  <script src="{{ asset('spark-template/assets/js/dashboard.js') }}"></script>
  @stack('scripts')
</body>

</html>
