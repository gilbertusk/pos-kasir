<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function getData(Request $request)
    {
        $startDate = $request->start_date ?? now()->subDays(6)->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $summary = Transaction::profitSummary($startDate, $endDate);

        // Daily revenue (after discount, excluding PPN)
        $dailyRevenue = Transaction::lunas()
            ->betweenDates($startDate, $endDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total - tax_amount) as total'))
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
}
