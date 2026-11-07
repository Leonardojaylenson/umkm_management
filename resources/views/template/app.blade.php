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

  <script src="{{ asset('spark-template/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('spark-template/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
  <script src="{{ asset('spark-template/assets/libs/flatpickr/flatpickr.min.js') }}"></script>
  <script src="{{ asset('spark-template/assets/js/dashboard.js') }}"></script>
  @stack('scripts')
</body>

</html>
