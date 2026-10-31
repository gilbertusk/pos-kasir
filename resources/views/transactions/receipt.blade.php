@php
    $store = \App\Models\Setting::getMultiple([
        'store_name', 'store_address', 'store_phone', 'store_whatsapp', 'store_npwp', 'store_logo',
        'printer_size', 'printer_margin', 'auto_print',
    ]);

    $paperSize = \App\Support\ReceiptPaper::normalize($store['printer_size']);
    $width = \App\Support\ReceiptPaper::printableWidth($paperSize);
    $margin = min(max((float) ($store['printer_margin'] ?? 0), 0), \App\Support\ReceiptPaper::maxMargin($paperSize));

    // ?print=1 forces printing (reprint buttons), ?print=0 suppresses it (preview on the cashier screen).
    $shouldPrint = request()->has('print')
        ? request()->boolean('print')
        : ($store['auto_print'] ?? '1') === '1';

    $cancelled = $transaction->status === 'batal';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Struk - {{ $transaction->transaction_number }}</title>
<style>
    @page { size: {{ $paperSize }} auto; margin: 0; }
    * { box-sizing: border-box; }
    html { background: #fff; }
    body { width: {{ $width }}mm; padding: 0 {{ $margin }}mm; margin: 0 auto; font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.35; color: #000; }
    .receipt { padding: 4mm 0; }
    p { margin: 0; }
    hr { border: 0; border-top: 1px dashed #000; margin: 6px 0; }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    .row { display: flex; justify-content: space-between; gap: 8px; }
    .row span:last-child { text-align: right; white-space: nowrap; }
    .store-name { font-size: 15px; font-weight: bold; margin-bottom: 2px; }
    .logo { display: block; max-width: 60%; max-height: 18mm; margin: 0 auto 4px; filter: grayscale(1); }
    .item { margin-bottom: 4px; }
    .total { font-size: 14px; font-weight: bold; }
    .void { border: 2px solid #000; padding: 4px; margin: 6px 0; text-align: center; font-size: 14px; font-weight: bold; letter-spacing: 2px; }
</style>
</head>
<body>
<div class="receipt">
    <div class="center">
        @if($store['store_logo'])
            <img src="{{ asset('storage/' . $store['store_logo']) }}" alt="" class="logo">
        @endif
        <p class="store-name">{{ $store['store_name'] ?: config('app.name') }}</p>
        @if($store['store_address'])<p>{{ $store['store_address'] }}</p>@endif
        @if($store['store_phone'])<p>Telp {{ $store['store_phone'] }}</p>@endif
        @if($store['store_whatsapp'])<p>WA {{ $store['store_whatsapp'] }}</p>@endif
        @if($store['store_npwp'])<p>NPWP {{ $store['store_npwp'] }}</p>@endif
    </div>
    <hr>

    @if($cancelled)
        <div class="void">DIBATALKAN</div>
        @if($transaction->cancel_reason)<p class="center">{{ $transaction->cancel_reason }}</p>@endif
        <hr>
    @endif

    <p>No: {{ $transaction->transaction_number }}</p>
    <p>Kasir: {{ $transaction->user->name ?? '-' }}</p>
    <p>Tanggal: {{ $transaction->created_at->format('d/m/Y H:i') }}</p>
    <hr>

    @foreach($transaction->items as $item)
        <div class="item">
            <p class="bold">{{ $item->product_name }}</p>
            <div class="row"><span>{{ $item->quantity }} x {{ formatRupiah($item->price) }}</span><span>{{ formatRupiah($item->subtotal) }}</span></div>
        </div>
    @endforeach
    <hr>

    <div class="row"><span>Subtotal</span><span>{{ formatRupiah($transaction->subtotal) }}</span></div>
    @if($transaction->discount_amount > 0)
        <div class="row"><span>Diskon</span><span>-{{ formatRupiah($transaction->discount_amount) }}</span></div>
    @endif
    @if($transaction->tax_amount > 0)
        <div class="row"><span>PPN {{ (float) $transaction->tax_percent }}%</span><span>{{ formatRupiah($transaction->tax_amount) }}</span></div>
    @endif
    <div class="row total"><span>TOTAL</span><span>{{ formatRupiah($transaction->total) }}</span></div>
    <hr>

    <div class="row"><span>{{ strtoupper($transaction->payment_method) }}</span><span>{{ formatRupiah($transaction->paid_amount) }}</span></div>
    @if($transaction->change_amount > 0)
        <div class="row"><span>Kembali</span><span>{{ formatRupiah($transaction->change_amount) }}</span></div>
    @endif
    <hr>

    <p class="center">Terima kasih atas kunjungan Anda!</p>
</div>
@if($shouldPrint)
<script>window.addEventListener('load', () => window.print());</script>
@endif
</body>
</html>
