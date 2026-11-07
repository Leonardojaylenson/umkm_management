<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title') - UMKM Management</title>
  <link rel="icon" type="image/png" href="{{ asset('spark-template/assets/images/favicon.ico') }}">
  <link rel="stylesheet" href="{{ asset('spark-template/assets/libs/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('spark-template/assets/libs/bootstrap-icons/bootstrap-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('spark-template/assets/css/main.css') }}">
  <style>.login-brand i { animation: none; }</style>
</head>

<body>
  <div class="login-wrapper">
    <div class="login-bg-shape login-bg-shape-1"></div>
    <div class="login-bg-shape login-bg-shape-2"></div>

    <div class="login-card">
      <a href="{{ url('/') }}" class="login-brand text-decoration-none">
        <i class="bi bi-shop-window"></i>
        <span>UMKM Management</span>
      </a>
      <p class="login-subtitle">@yield('subtitle')</p>

      @yield('content')
    </div>
  </div>

  <script src="{{ asset('spark-template/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('spark-template/assets/js/auth.js') }}"></script>
</body>

</html>
