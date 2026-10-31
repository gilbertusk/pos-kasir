<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan {{ $period }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1b2620; }
    h1 { font-size: 16px; margin: 0; }
    h2 { font-size: 12px; margin: 18px 0 6px; }
    .sub { color: #666; font-size: 10px; margin: 2px 0; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 5px 7px; text-align: left; }
    th { background: #f3f4f2; }
    .r { text-align: right; }
    .neg { color: #b42318; }
    .total td { font-weight: bold; background: #f9faf9; }
    .summary td { width: 50%; }
    .note { color: #666; font-size: 9px; margin-top: 14px; }
</style>
</head>
<body>
    <h1>Laporan Penjualan – {{ $storeName }}</h1>
    <p class="sub">Periode: {{ $period }}</p>
    <p class="sub">Dicetak: {{ now()->format('d/m/Y H:i') }}</p>

    <h2>Ringkasan</h2>
    <table class="summary">
        <tr><td>Jumlah transaksi</td><td class="r">{{ $summary['total_transactions'] }}</td></tr>
        <tr><td>Pendapatan</td><td class="r">{{ formatRupiah($summary['total_revenue']) }}</td></tr>
        <tr><td>− Modal barang terjual</td><td class="r">{{ formatRupiah($summary['total_cogs']) }}</td></tr>
        <tr class="total"><td>= Laba kotor</td><td class="r">{{ formatRupiah($summary['gross_profit']) }}</td></tr>
        <tr><td>− Pengeluaran</td><td class="r">{{ formatRupiah($summary['total_expenses']) }}</td></tr>
        <tr class="total"><td>= Laba bersih</td><td class="r {{ $summary['net_profit'] < 0 ? 'neg' : '' }}">{{ formatRupiah($summary['net_profit']) }}</td></tr>
    </table>

    <h2>Metode pembayaran</h2>
    <table>
        <thead><tr><th>Metode</th><th class="r">Transaksi</th><th class="r">Total</th></tr></thead>
        <tbody>
            @forelse($paymentMethods as $method)
                <tr><td>{{ $method['label'] }}</td><td class="r">{{ $method['count'] }}</td><td class="r">{{ formatRupiah($method['total']) }}</td></tr>
            @empty
                <tr><td colspan="3">Tidak ada penjualan</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Per hari</h2>
    <table>
        <thead><tr><th>Tanggal</th><th class="r">Transaksi</th><th class="r">Pendapatan</th><th class="r">Modal</th><th class="r">Laba kotor</th><th class="r">Pengeluaran</th><th class="r">Laba bersih</th></tr></thead>
        <tbody>
            @foreach($days as $day)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($day['date'])->format('d/m/Y') }}</td>
                    <td class="r">{{ $day['transactions'] }}</td>
                    <td class="r">{{ formatRupiah($day['revenue']) }}</td>
                    <td class="r">{{ formatRupiah($day['cogs']) }}</td>
                    <td class="r">{{ formatRupiah($day['gross_profit']) }}</td>
                    <td class="r">{{ formatRupiah($day['expenses']) }}</td>
                    <td class="r {{ $day['net_profit'] < 0 ? 'neg' : '' }}">{{ formatRupiah($day['net_profit']) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total</td>
                <td class="r">{{ $summary['total_transactions'] }}</td>
                <td class="r">{{ formatRupiah($summary['total_revenue']) }}</td>
                <td class="r">{{ formatRupiah($summary['total_cogs']) }}</td>
                <td class="r">{{ formatRupiah($summary['gross_profit']) }}</td>
                <td class="r">{{ formatRupiah($summary['total_expenses']) }}</td>
                <td class="r {{ $summary['net_profit'] < 0 ? 'neg' : '' }}">{{ formatRupiah($summary['net_profit']) }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Pengeluaran per kategori</h2>
    <table>
        <thead><tr><th>Kategori</th><th class="r">Total</th></tr></thead>
        <tbody>
            @forelse($expenseCategories as $category => $total)
                <tr><td>{{ ucfirst($category) }}</td><td class="r">{{ formatRupiah($total) }}</td></tr>
            @empty
                <tr><td colspan="2">Tidak ada pengeluaran</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="note">Pendapatan dihitung setelah diskon, sudah termasuk PPN dan pembulatan. Transaksi batal tidak dihitung. Modal memakai harga beli saat barang terjual.</p>
</body>
</html>
