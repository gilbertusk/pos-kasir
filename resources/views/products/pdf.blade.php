<!DOCTYPE html>
<html><head><title>Daftar Barang</title>
<style>body{font-family:sans-serif;font-size:12px;} table{width:100%;border-collapse:collapse;margin-top:10px;} th,td{border:1px solid #ddd;padding:6px 8px;text-align:left;} th{background:#f5f5f5;font-weight:bold;} .text-right{text-align:right;} .text-center{text-align:center;} h2{margin:0;} .sub{color:#666;font-size:11px;}</style>
</head><body>
<h2>Daftar Barang - {{ config('app.name') }}</h2>
<p class="sub">Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
<table>
<thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Satuan</th><th class="text-right">Harga Beli</th><th class="text-right">Harga Jual</th><th class="text-center">Stok</th><th class="text-center">Status</th></tr></thead>
<tbody>
@foreach($products as $p)
<tr><td>{{ $p->code }}</td><td>{{ $p->name }}</td><td>{{ ucfirst($p->category) }}</td><td>{{ $p->unit }}</td><td class="text-right">{{ formatRupiah($p->buy_price) }}</td><td class="text-right">{{ formatRupiah($p->sell_price) }}</td><td class="text-center">{{ $p->stock }}</td><td class="text-center">{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}</td></tr>
@endforeach
</tbody></table>
<p class="sub" style="margin-top:10px;">Total: {{ $products->count() }} barang</p>
</body></html>
