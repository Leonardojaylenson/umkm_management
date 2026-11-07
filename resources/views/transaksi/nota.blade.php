<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Nota {{ $transaction->invoice_number }}</title>
  <style>
    body { font-family: 'Courier New', monospace; font-size: 13px; width: 300px; margin: 16px auto; color: #000; }
    h2, p { margin: 0; text-align: center; }
    hr { border: 0; border-top: 1px dashed #000; margin: 8px 0; }
    table { width: 100%; border-collapse: collapse; }
    td { vertical-align: top; padding: 1px 0; }
    .r { text-align: right; }
    .stamp { text-align: center; font-weight: bold; margin: 6px 0; }
    @media print { .no-print { display: none; } }
  </style>
</head>
<body onload="window.print()">
  <h2>{{ $business->business_name }}</h2>
  <p>{{ $business->address }}</p>
  @if ($business->phone)<p>Telp {{ $business->phone }}</p>@endif
  <hr>

  <table>
    <tr><td>No</td><td class="r">{{ $transaction->invoice_number }}</td></tr>
    <tr><td>Tanggal</td><td class="r">{{ tanggal($transaction->transaction_date, true) }}</td></tr>
    <tr><td>Kasir</td><td class="r">{{ $transaction->cashier->name ?? '-' }}</td></tr>
    <tr><td>Pelanggan</td><td class="r">{{ $transaction->customer->customer_name ?? 'Umum' }}</td></tr>
  </table>
  <hr>

  <table>
    @foreach ($transaction->details as $d)
      <tr><td colspan="2">{{ $d->item_name }}</td></tr>
      <tr><td>{{ qty($d->quantity) }} x {{ number_format($d->selling_price, 0, ',', '.') }}</td><td class="r">{{ number_format($d->subtotal, 0, ',', '.') }}</td></tr>
    @endforeach
  </table>
  <hr>

  <table>
    <tr><td><strong>TOTAL</strong></td><td class="r"><strong>{{ rupiah($transaction->total_amount) }}</strong></td></tr>
    <tr><td>Dibayar ({{ strtoupper($transaction->payment_method) }})</td><td class="r">{{ rupiah($transaction->paid_amount) }}</td></tr>
    @if ($transaction->remaining > 0)
      <tr><td>Sisa</td><td class="r">{{ rupiah($transaction->remaining) }}</td></tr>
    @endif
  </table>

  @if ($transaction->isCancelled())<p class="stamp">*** DIBATALKAN ***</p>@endif
  <hr>
  <p>Terima kasih!</p>

  <p class="no-print" style="margin-top:12px"><a href="javascript:window.close()">Tutup</a></p>
</body>
</html>
