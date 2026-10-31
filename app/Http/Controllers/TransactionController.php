<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'end_date.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]);

        $query = Transaction::with('user', 'items')->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('transaction_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($q2) use ($request) {
                      $q2->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $query->betweenDates($request->start_date, $request->end_date);

        $transactions = $query->paginate(10);

        // Summary follows the date filter only; search narrows the list, not the store figures.
        $summary = Transaction::profitSummary($request->start_date, $request->end_date);

        return view('transactions.index', [
            'transactions' => $transactions,
            'totalTransactions' => $summary['total_transactions'],
            'totalRevenue' => $summary['total_revenue'],
            'totalExpenses' => $summary['total_expenses'],
            'grossProfit' => $summary['gross_profit'],
            'summaryPeriod' => $this->periodLabel($request->start_date, $request->end_date),
        ]);
    }

    private function periodLabel(?string $startDate, ?string $endDate): string
    {
        $format = fn (string $date) => Carbon::parse($date)->format('d/m/Y');

        return match (true) {
            $startDate && $endDate => $format($startDate) . ' – ' . $format($endDate),
            (bool) $startDate => 'Sejak ' . $format($startDate),
            (bool) $endDate => 'Sampai ' . $format($endDate),
            default => 'Semua tanggal',
        };
    }

    public function show(Transaction $transaction)
    {
        $transaction->load('items.product', 'user', 'canceller:id,name');
        return response()->json($transaction);
    }

    public function cancel(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'cancel_reason' => 'required|string|max:255',
        ], [
            'cancel_reason.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        if (! $transaction->cancel($request->user(), $validated['cancel_reason'])) {
            return redirect()->back()->with('error', 'Transaksi sudah dibatalkan.');
        }

        return redirect()->back()->with('success', "Transaksi {$transaction->transaction_number} dibatalkan dan stok dikembalikan.");
    }

    public function receipt(Transaction $transaction)
    {
        $transaction->load('items', 'user');
        return view('transactions.receipt', compact('transaction'));
    }
}
