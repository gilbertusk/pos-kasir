<x-app-layout>
    @section('title', 'Laporan')
    <div x-data="reportApp()" x-init="loadData()">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-4 py-2.5 bg-white rounded-xl border border-slate-200 shadow-sm">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <input type="date" x-model="startDate" @change="loadData()" class="border-0 text-sm font-bold text-slate-700 focus:ring-0 p-0 bg-transparent cursor-pointer">
                    <span class="text-slate-300 font-bold">—</span>
                    <input type="date" x-model="endDate" @change="loadData()" class="border-0 text-sm font-bold text-slate-700 focus:ring-0 p-0 bg-transparent cursor-pointer">
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a :href="exportUrl('pdf')" class="btn-secondary !rounded-xl !py-3 shadow-sm">Ekspor PDF</a>
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" @click.outside="open = false" class="btn-secondary !rounded-xl !py-3 shadow-sm">Ekspor CSV <span aria-hidden="true">▾</span></button>
                    <div x-show="open" x-cloak class="absolute right-0 z-20 mt-2 w-60 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl">
                        <a :href="exportUrl('csv', 'harian')" class="block px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Ringkasan per hari</a>
                        <a :href="exportUrl('csv', 'transaksi')" class="block px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Rincian transaksi</a>
                        <a :href="exportUrl('csv', 'pengeluaran')" class="block px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Rincian pengeluaran</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="card p-5 flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center border border-emerald-100"><svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div><p class="text-xs font-medium text-slate-400 mb-1">Pendapatan</p><p class="tabular text-2xl font-bold text-emerald-600 leading-none" x-text="formatRp(summary.total_revenue)"></p></div>
            </div>
            <div class="card p-5 flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center border border-indigo-100"><svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
                <div><p class="text-xs font-medium text-slate-400 mb-1">Modal barang terjual</p><p class="tabular text-2xl font-bold text-slate-900 leading-none" x-text="formatRp(summary.total_cogs)"></p></div>
            </div>
            <div class="card p-5 flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center border border-amber-100"><svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
                <div><p class="text-xs font-medium text-slate-400 mb-1">Laba kotor</p><p class="tabular text-2xl font-bold text-amber-600 leading-none" x-text="formatRp(summary.gross_profit)"></p></div>
            </div>
            <div class="card p-5 flex items-center gap-4">
                <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center border border-rose-100"><svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
                <div><p class="text-xs font-medium text-slate-400 mb-1">Laba bersih</p><p class="tabular text-2xl font-bold leading-none" :class="summary.net_profit < 0 ? 'text-rose-600' : 'text-emerald-600'" x-text="formatRp(summary.net_profit)"></p></div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-1 mb-6 bg-white rounded-xl border border-slate-200 p-1 w-fit shadow-sm">
            <button @click="activeTab = 'pendapatan'" :class="activeTab === 'pendapatan' ? 'bg-primary-600 text-white shadow-md shadow-primary-200' : 'text-slate-500 hover:bg-slate-50'" class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">Pendapatan</button>
            <button @click="activeTab = 'pengeluaran'" :class="activeTab === 'pengeluaran' ? 'bg-primary-600 text-white shadow-md shadow-primary-200' : 'text-slate-500 hover:bg-slate-50'" class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">Pengeluaran</button>
            <button @click="activeTab = 'perbandingan'" :class="activeTab === 'perbandingan' ? 'bg-primary-600 text-white shadow-md shadow-primary-200' : 'text-slate-500 hover:bg-slate-50'" class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">Perbandingan</button>
        </div>

        {{-- Charts --}}
        <div class="card p-8 mb-8" x-show="activeTab === 'pendapatan' || activeTab === 'pengeluaran'">
            <h3 class="text-sm font-bold text-slate-400 mb-6" x-text="activeTab === 'pendapatan' ? 'Pendapatan harian' : 'Pengeluaran harian'"></h3>
            <canvas id="lineChart" height="100"></canvas>
        </div>

        <div x-show="activeTab === 'perbandingan'" class="card p-8 mb-8">
            <h3 class="text-sm font-bold text-slate-400 mb-6">Pendapatan vs pengeluaran</h3>
            <canvas id="barChart" height="100"></canvas>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
            <div class="card p-8">
                <h3 class="text-sm font-bold text-slate-400 mb-6">Metode pembayaran</h3>
                <canvas id="donutChart" height="200"></canvas>
            </div>
            <div class="card p-8">
                <h3 class="text-sm font-bold text-slate-400 mb-6">Ringkasan angka</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-sm font-bold text-slate-500">Total transaksi</span>
                        <span class="text-xl font-bold text-slate-900" x-text="summary.total_transactions"></span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                        <span class="text-sm font-bold text-emerald-600">Pendapatan</span>
                        <span class="tabular text-xl font-bold text-emerald-600" x-text="formatRp(summary.total_revenue)"></span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-sm font-bold text-slate-500">− Modal barang terjual</span>
                        <span class="tabular text-xl font-bold text-slate-900" x-text="formatRp(summary.total_cogs)"></span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-amber-50 rounded-2xl border border-amber-100">
                        <span class="text-sm font-bold text-amber-600">= Laba kotor</span>
                        <span class="tabular text-xl font-bold text-amber-600" x-text="formatRp(summary.gross_profit)"></span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-rose-50 rounded-2xl border border-rose-100">
                        <span class="text-sm font-bold text-rose-600">− Pengeluaran</span>
                        <span class="tabular text-xl font-bold text-rose-600" x-text="formatRp(summary.total_expenses)"></span>
                    </div>
                    <div class="flex justify-between items-center p-4 rounded-2xl border" :class="summary.net_profit < 0 ? 'bg-rose-50 border-rose-100' : 'bg-emerald-50 border-emerald-100'">
                        <span class="text-sm font-bold" :class="summary.net_profit < 0 ? 'text-rose-600' : 'text-emerald-600'">= Laba bersih</span>
                        <span class="tabular text-xl font-bold" :class="summary.net_profit < 0 ? 'text-rose-600' : 'text-emerald-600'" x-text="formatRp(summary.net_profit)"></span>
                    </div>
                </div>
                <p class="mt-4 text-xs text-slate-400">Pendapatan dihitung setelah diskon dan sudah termasuk PPN. Transaksi batal tidak dihitung.</p>
            </div>
        </div>
    </div>

    <script>
    function reportApp() {
        return {
            startDate: @js($defaultStart),
            endDate: @js($defaultEnd),
            activeTab: 'pendapatan',
            summary: { total_transactions: 0, total_revenue: 0, total_cogs: 0, gross_profit: 0, total_expenses: 0, net_profit: 0 },
            charts: {},

            formatRp(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); },

            exportUrl(format, report = '') {
                const params = new URLSearchParams({ format, start_date: this.startDate, end_date: this.endDate });
                if (report) params.set('report', report);
                return `{{ route('reports.export') }}?${params.toString()}`;
            },

            async loadData() {
                const res = await fetch(`{{ route('reports.data') }}?start_date=${this.startDate}&end_date=${this.endDate}`);
                const data = await res.json();
                this.summary = data.summary;
                this.renderCharts(data);
            },

            renderCharts(data) {
                // Destroy existing
                Object.values(this.charts).forEach(c => c.destroy());

                const labels = data.daily_revenue.map(d => new Date(d.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }));
                const revenueData = data.daily_revenue.map(d => d.total);
                const expenseLabels = data.daily_expenses.map(d => new Date(d.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }));
                const expenseData = data.daily_expenses.map(d => d.total);

                // Line Chart
                const lineCtx = document.getElementById('lineChart');
                if (lineCtx) {
                    this.charts.line = new Chart(lineCtx, {
                        type: 'line',
                        data: { labels, datasets: [{ label: 'Pendapatan', data: revenueData, borderColor: '#1F4D3A', backgroundColor: 'rgba(31,77,58,0.08)', fill: true, tension: 0.4, pointRadius: 5 }] },
                        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + (v/1000).toLocaleString('id-ID') + 'k' } } } }
                    });
                }

                // Bar Chart
                const barCtx = document.getElementById('barChart');
                if (barCtx) {
                    const allLabels = [...new Set([...labels, ...expenseLabels])];
                    this.charts.bar = new Chart(barCtx, {
                        type: 'bar',
                        data: { labels: allLabels, datasets: [
                            { label: 'Pendapatan', data: allLabels.map(l => { const found = data.daily_revenue.find(d => new Date(d.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) === l); return found ? found.total : 0; }), backgroundColor: '#1F4D3A' },
                            { label: 'Pengeluaran', data: allLabels.map(l => { const found = data.daily_expenses.find(d => new Date(d.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) === l); return found ? found.total : 0; }), backgroundColor: '#B42318' }
                        ] },
                        options: { responsive: true, scales: { y: { beginAtZero: true } } }
                    });
                }

                // Donut Chart
                const donutCtx = document.getElementById('donutChart');
                if (donutCtx) {
                    const pmLabels = data.payment_methods.map(p => p.payment_method.charAt(0).toUpperCase() + p.payment_method.slice(1));
                    const pmData = data.payment_methods.map(p => p.count);
                    const pmTotal = pmData.reduce((a, b) => a + b, 0);
                    this.charts.donut = new Chart(donutCtx, {
                        type: 'doughnut',
                        data: { labels: pmLabels, datasets: [{ data: pmData, backgroundColor: ['#1F4D3A', '#F2B33D', '#8DB9A0'] }] },
                        options: { responsive: true, plugins: { legend: { position: 'right', labels: { generateLabels: (chart) => chart.data.labels.map((l, i) => ({ text: `${l}  ${pmData[i]} (${(pmData[i]/pmTotal*100).toFixed(1)}%)`, fillStyle: chart.data.datasets[0].backgroundColor[i], index: i })) } } } }
                    });
                }
            }
        }
    }
    </script>
</x-app-layout>
