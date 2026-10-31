<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('user', 'items')->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('transaction_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($q2) use ($request) {
                      $q2->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->paginate(10);

        $summary = Transaction::profitSummary();

        return view('transactions.index', [
            'transactions' => $transactions,
            'totalTransactions' => $summary['total_transactions'],
            'totalRevenue' => $summary['total_revenue'],
            'totalExpenses' => $summary['total_expenses'],
            'grossProfit' => $summary['gross_profit'],
        ]);
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
