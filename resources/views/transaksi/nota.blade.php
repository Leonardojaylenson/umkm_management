<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Nota {{ $transaction->invoice_number }}</title>
  <style>
    @page { size: 80mm auto; margin: 3mm; }

    html { background: #e9eeec; }

    body {
      font-family: 'Courier New', monospace;
      font-size: 12px;
      color: #000;
      width: 72mm;
      margin: 12px auto;
      padding: 14px 12px;
      background: #fff;
      border-radius: 6px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.12);
    }

    .tengah { text-align: center; }
    .kanan { text-align: right; }
    .nama-usaha { font-size: 16px; font-weight: bold; margin-bottom: 2px; }
    .garis { border: 0; border-top: 1px dashed #000; margin: 8px 0; }
    .garis-tebal { border: 0; border-top: 2px solid #000; margin: 8px 0; }
    table { width: 100%; border-collapse: collapse; }
    td { vertical-align: top; padding: 1px 0; }
    .nama-item { font-weight: bold; padding-top: 4px; }
    .total td { font-size: 14px; font-weight: bold; padding: 3px 0; }
    .status { text-align: center; font-weight: bold; font-size: 14px; border: 2px solid #000; padding: 3px; margin: 8px 0; }

    @media print {
      html { background: none; }
      body { margin: 0 auto; padding: 0; box-shadow: none; border-radius: 0; }
    }
  </style>
</head>
<body>

  <div class="tengah">
    <div class="nama-usaha">{{ $business->business_name }}</div>
    @if ($business->address)<div>{{ $business->address }}</div>@endif
    @if ($business->phone)<div>Telp {{ $business->phone }}</div>@endif
  </div>

  <hr class="garis-tebal">

  <table>
    <tr><td>No. Nota</td><td class="kanan">{{ $transaction->invoice_number }}</td></tr>
    <tr><td>Tanggal</td><td class="kanan">{{ tanggal($transaction->transaction_date, true) }}</td></tr>
    <tr><td>Kasir</td><td class="kanan">{{ $transaction->cashier->name ?? '-' }}</td></tr>
    <tr><td>Pelanggan</td><td class="kanan">{{ $transaction->customer->customer_name ?? 'Umum' }}</td></tr>
  </table>

  <hr class="garis">

  <table>
    @foreach ($transaction->details as $d)
      <tr><td colspan="2" class="nama-item">{{ $d->item_name }}</td></tr>
      <tr>
        <td>{{ qty($d->quantity) }} x {{ number_format($d->selling_price, 0, ',', '.') }}</td>
        <td class="kanan">{{ number_format($d->subtotal, 0, ',', '.') }}</td>
      </tr>
    @endforeach
  </table>

  <hr class="garis">

  <table>
    <tr class="total"><td>TOTAL</td><td class="kanan">{{ rupiah($transaction->total_amount) }}</td></tr>
    <tr><td>Bayar ({{ strtoupper($transaction->payment_method) }})</td><td class="kanan">{{ rupiah($transaction->paid_amount) }}</td></tr>
    @if ($transaction->total_amount > $transaction->paid_amount)
      <tr><td>Sisa</td><td class="kanan">{{ rupiah($transaction->total_amount - $transaction->paid_amount) }}</td></tr>
    @endif
  </table>

  @if ($transaction->status == 'cancelled')
    <div class="status">DIBATALKAN</div>
  @elseif ($transaction->payment_status == 'paid')
    <div class="status">LUNAS</div>
  @elseif ($transaction->payment_status == 'down_payment')
    <div class="status">DP</div>
  @else
    <div class="status">BELUM LUNAS</div>
  @endif

  @if ($transaction->note)
    <div>Catatan: {{ $transaction->note }}</div>
  @endif

  <hr class="garis">

  <div class="tengah">Terima kasih atas kunjungan Anda!</div>

</body>
</html>
