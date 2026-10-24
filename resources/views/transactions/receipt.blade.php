<!DOCTYPE html>
<html><head><title>Struk - {{ $transaction->transaction_number }}</title>
<style>body{font-family:monospace;font-size:12px;max-width:300px;margin:0 auto;padding:10px;}hr{border-style:dashed;}.text-right{text-align:right;}.text-center{text-align:center;}.bold{font-weight:bold;}.flex{display:flex;justify-content:space-between;}</style>
</head><body onload="window.print()">
<div class="text-center"><h2>{{ \App\Models\Setting::getValue('store_name', config('app.name')) }}</h2><p>{{ \App\Models\Setting::getValue('store_address', '') }}</p><p>{{ \App\Models\Setting::getValue('store_phone', '') }}</p></div><hr>
<p>No: {{ $transaction->transaction_number }}</p><p>Kasir: {{ $transaction->user->name ?? '-' }}</p><p>Tanggal: {{ $transaction->created_at->format('d/m/Y H:i') }}</p><hr>
@foreach($transaction->items as $item)<p class="bold">{{ $item->product_name }}</p><div class="flex"><span>{{ $item->quantity }} x {{ formatRupiah($item->price) }}</span><span>{{ formatRupiah($item->subtotal) }}</span></div>@endforeach<hr>
<div class="flex"><span>Subtotal</span><span>{{ formatRupiah($transaction->subtotal) }}</span></div>
@if($transaction->discount_amount > 0)<div class="flex"><span>Diskon</span><span>-{{ formatRupiah($transaction->discount_amount) }}</span></div>@endif
@if($transaction->tax_amount > 0)<div class="flex"><span>PPN {{ $transaction->tax_percent }}%</span><span>{{ formatRupiah($transaction->tax_amount) }}</span></div>@endif
<div class="flex bold" style="font-size:14px;"><span>TOTAL</span><span>{{ formatRupiah($transaction->total) }}</span></div><hr>
<div class="flex"><span>{{ strtoupper($transaction->payment_method) }}</span><span>{{ formatRupiah($transaction->paid_amount) }}</span></div>
@if($transaction->change_amount > 0)<div class="flex"><span>Kembali</span><span>{{ formatRupiah($transaction->change_amount) }}</span></div>@endif<hr>
<p class="text-center">Terima kasih atas kunjungan Anda!</p>
</body></html>
