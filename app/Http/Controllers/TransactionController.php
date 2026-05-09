<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Expense;
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

        $totalTransactions = Transaction::lunas()->count();
        $totalRevenue = Transaction::lunas()->sum('total');
        $totalExpenses = Expense::sum('amount');
        $grossProfit = $totalRevenue - $totalExpenses;

        return view('transactions.index', compact(
            'transactions', 'totalTransactions', 'totalRevenue', 'totalExpenses', 'grossProfit'
        ));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load('items.product', 'user');
        return response()->json($transaction);
    }

    public function cancel(Transaction $transaction)
    {
        if ($transaction->status === 'batal') {
            return redirect()->back()->with('error', 'Transaksi sudah dibatalkan.');
        }

        $transaction->update(['status' => 'batal']);

        // Kembalikan stok
        foreach ($transaction->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dibatalkan.');
    }

    public function receipt(Transaction $transaction)
    {
        $transaction->load('items', 'user');
        return view('transactions.receipt', compact('transaction'));
    }
}
