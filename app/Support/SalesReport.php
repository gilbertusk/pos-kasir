<?php

namespace App\Support;

use App\Models\Expense;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Sales, profit and expense figures for a date range, for download as CSV or PDF.
 * Totals use the same rules as Transaction::profitSummary(): paid sales only, revenue after discount incl. PPN.
 */
class SalesReport
{
    public const PAYMENT_LABELS = ['tunai' => 'Tunai', 'qris' => 'QRIS', 'debit' => 'Debit'];

    public function __construct(
        public readonly string $startDate,
        public readonly string $endDate,
    ) {}

    public function periodLabel(): string
    {
        return Carbon::parse($this->startDate)->format('d/m/Y') . ' – ' . Carbon::parse($this->endDate)->format('d/m/Y');
    }

    /**
     * One row per calendar day in the range, including days without sales or expenses.
     *
     * @return array<int, array{date: string, transactions: int, revenue: float, cogs: float, gross_profit: float, expenses: float, net_profit: float}>
     */
    public function daily(): array
    {
        $sales = Transaction::lunas()
            ->betweenDates($this->startDate, $this->endDate)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as transactions, SUM(total) as revenue')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $cogs = TransactionItem::query()
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.status', 'lunas')
            ->whereDate('transactions.created_at', '>=', $this->startDate)
            ->whereDate('transactions.created_at', '<=', $this->endDate)
            ->selectRaw('DATE(transactions.created_at) as day, SUM(transaction_items.buy_price * transaction_items.quantity) as cogs')
            ->groupBy('day')
            ->pluck('cogs', 'day');

        $expenses = $this->expenseQuery()
            ->selectRaw('DATE(date) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $rows = [];
        foreach (CarbonPeriod::create($this->startDate, $this->endDate) as $day) {
            $key = $day->toDateString();
            $revenue = (float) ($sales[$key]->revenue ?? 0);
            $dayCogs = (float) ($cogs[$key] ?? 0);
            $dayExpenses = (float) ($expenses[$key] ?? 0);

            $rows[] = [
                'date' => $key,
                'transactions' => (int) ($sales[$key]->transactions ?? 0),
                'revenue' => $revenue,
                'cogs' => $dayCogs,
                'gross_profit' => $revenue - $dayCogs,
                'expenses' => $dayExpenses,
                'net_profit' => $revenue - $dayCogs - $dayExpenses,
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array{method: string, label: string, count: int, total: float}>
     */
    public function paymentMethods(): array
    {
        return Transaction::lunas()
            ->betweenDates($this->startDate, $this->endDate)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'method' => $row->payment_method,
                'label' => self::PAYMENT_LABELS[$row->payment_method] ?? ucfirst($row->payment_method),
                'count' => (int) $row->count,
                'total' => (float) $row->total,
            ])
            ->all();
    }

    /**
     * @return array<string, float> category => total
     */
    public function expensesByCategory(): array
    {
        return $this->expenseQuery()
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->pluck('total', 'category')
            ->map(fn ($total) => (float) $total)
            ->all();
    }

    public function downloadCsv(string $report): StreamedResponse
    {
        [$header, $rows] = match ($report) {
            'transaksi' => $this->transactionRows(),
            'pengeluaran' => $this->expenseRows(),
            default => $this->dailyRows(),
        };

        $filename = "laporan-{$report}_{$this->startDate}_{$this->endDate}.csv";

        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            // BOM so Excel opens the file as UTF-8; semicolons because Indonesian Excel uses the comma for decimals.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $header, ';');
            foreach ($rows as $row) {
                fputcsv($out, array_map([self::class, 'csvCell'], $row), ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array<string, mixed>
     */
    public function pdfData(): array
    {
        return [
            'storeName' => Setting::getValue('store_name') ?: config('app.name'),
            'period' => $this->periodLabel(),
            'summary' => Transaction::profitSummary($this->startDate, $this->endDate),
            'days' => $this->daily(),
            'paymentMethods' => $this->paymentMethods(),
            'expenseCategories' => $this->expensesByCategory(),
        ];
    }

    public function toPdfView(): View
    {
        return view('reports.pdf', $this->pdfData());
    }

    /**
     * Text starting with = + - @ would run as a formula in Excel; numbers are left alone.
     */
    public static function csvCell(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && ! is_numeric($value) && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }

    private function expenseQuery()
    {
        return Expense::query()
            ->whereDate('date', '>=', $this->startDate)
            ->whereDate('date', '<=', $this->endDate);
    }

    private function dailyRows(): array
    {
        $days = $this->daily();
        $rows = array_map(fn ($day) => [
            Carbon::parse($day['date'])->format('d/m/Y'),
            $day['transactions'],
            (int) $day['revenue'],
            (int) $day['cogs'],
            (int) $day['gross_profit'],
            (int) $day['expenses'],
            (int) $day['net_profit'],
        ], $days);

        $rows[] = ['TOTAL', ...array_map(
            fn ($column) => (int) array_sum(array_column($days, $column)),
            ['transactions', 'revenue', 'cogs', 'gross_profit', 'expenses', 'net_profit']
        )];

        return [['Tanggal', 'Jumlah transaksi', 'Pendapatan', 'Modal barang terjual', 'Laba kotor', 'Pengeluaran', 'Laba bersih'], $rows];
    }

    private function transactionRows(): array
    {
        $header = ['Nomor transaksi', 'Waktu', 'Kasir', 'Metode', 'Subtotal', 'Diskon', 'PPN', 'Pembulatan', 'Total', 'Modal', 'Laba kotor', 'Status', 'Alasan batal'];

        $rows = function () {
            $query = Transaction::with('user:id,name', 'items:id,transaction_id,buy_price,quantity')
                ->betweenDates($this->startDate, $this->endDate)
                ->orderBy('id');

            foreach ($query->lazy(500) as $transaction) {
                $cogs = (int) $transaction->items->sum(fn ($item) => $item->buy_price * $item->quantity);

                yield [
                    $transaction->transaction_number,
                    $transaction->created_at->format('d/m/Y H:i'),
                    $transaction->user->name ?? '-',
                    self::PAYMENT_LABELS[$transaction->payment_method] ?? $transaction->payment_method,
                    (int) $transaction->subtotal,
                    (int) $transaction->discount_amount,
                    (int) $transaction->tax_amount,
                    (int) $transaction->rounding_amount,
                    (int) $transaction->total,
                    $cogs,
                    (int) $transaction->total - $cogs,
                    $transaction->status === 'batal' ? 'Batal' : 'Lunas',
                    $transaction->cancel_reason ?? '',
                ];
            }
        };

        return [$header, $rows()];
    }

    private function expenseRows(): array
    {
        $expenses = $this->expenseQuery()->with('user:id,name')->orderBy('date')->orderBy('id')->get();

        $rows = $expenses->map(fn ($expense) => [
            $expense->date->format('d/m/Y'),
            ucfirst($expense->category),
            $expense->description ?? '',
            (int) $expense->amount,
            $expense->user->name ?? '-',
        ])->all();

        $rows[] = ['TOTAL', '', '', (int) $expenses->sum('amount'), ''];

        return [['Tanggal', 'Kategori', 'Deskripsi', 'Nominal', 'Dicatat oleh'], $rows];
    }
}
