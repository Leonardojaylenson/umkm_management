<footer class="footer-custom">
  <div class="footer-left">
    <span class="footer-logo"><i class="bi bi-shop-window"></i> UMKM Management</span>
    <span class="footer-separator">|</span>
    <span class="footer-copy">&copy; {{ date('Y') }} UMKM Management System. Template: Spark Admin.</span>
  </div>
  <div class="footer-right">
    <ul class="footer-links">
      <li><a href="{{ route('dashboard') }}" class="footer-link">Dashboard</a></li>
      <li><a href="{{ route('transaksi.index') }}" class="footer-link">Transaksi</a></li>
      @if (auth()->user()->isOwner())
        <li><a href="{{ route('laporan.index') }}" class="footer-link">Laporan</a></li>
      @endif
    </ul>
  </div>
</footer>
