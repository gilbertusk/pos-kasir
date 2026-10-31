<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Expense;
use App\Support\SalesReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index()
    {
        $defaultEnd = now()->toDateString();
        $defaultStart = now()->subDays(6)->toDateString();

        return view('reports.index', compact('defaultStart', 'defaultEnd'));
    }

    public function getData(Request $request)
    {
        $startDate = $request->start_date ?? now()->subDays(6)->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $summary = Transaction::profitSummary($startDate, $endDate);

        // Daily revenue (after discount, including PPN)
        $dailyRevenue = Transaction::lunas()
            ->betweenDates($startDate, $endDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Daily expenses
        $dailyExpenses = Expense::query()->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->select('date', DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Payment methods
        $paymentMethods = Transaction::lunas()
            ->betweenDates($startDate, $endDate)
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'summary' => $summary,
            'daily_revenue' => $dailyRevenue,
            'daily_expenses' => $dailyExpenses,
            'payment_methods' => $paymentMethods,
        ]);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:pdf,csv',
            'report' => [Rule::requiredIf($request->input('format') === 'csv'), 'nullable', 'in:harian,transaksi,pengeluaran'],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $endDate = $validated['end_date'] ?? now()->toDateString();
        $startDate = $validated['start_date'] ?? now()->subDays(6)->toDateString();

        if (Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) > 366) {
            return back()->withErrors(['end_date' => 'Periode ekspor maksimal 367 hari.'])->withInput();
        }

        $report = new SalesReport($startDate, $endDate);

        if ($validated['format'] === 'csv') {
            return $report->downloadCsv($validated['report'] ?? 'harian');
        }

        return Pdf::loadView('reports.pdf', $report->pdfData())
            ->setPaper('a4', 'landscape')
            ->download("laporan_{$startDate}_{$endDate}.pdf");
    }
}
