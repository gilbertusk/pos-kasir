<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $transaction_number
 * @property int $user_id
 * @property float $subtotal
 * @property float $discount_percent
 * @property float $discount_amount
 * @property float $tax_percent
 * @property float $tax_amount
 * @property float $total
 * @property string $payment_method
 * @property float $paid_amount
 * @property float $change_amount
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TransactionItem[] $items
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction lunas()
 */
class Transaction extends Model

{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'user_id',
        'subtotal',
        'discount_percent',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'total',
        'payment_method',
        'paid_amount',
        'change_amount',
        'status',
    ];

    protected $casts = [
        'subtotal' => 'decimal:0',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:0',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:0',
        'total' => 'decimal:0',
        'paid_amount' => 'decimal:0',
        'change_amount' => 'decimal:0',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function scopeLunas(Builder $query)
    {
        return $query->where('status', 'lunas');
    }

    public function scopeBatal(Builder $query)
    {
        return $query->where('status', 'batal');
    }

    /**
     * Limit to sales created between two dates (inclusive). Null bounds are ignored.
     */
    public function scopeBetweenDates(Builder $query, ?string $startDate, ?string $endDate)
    {
        return $query
            ->when($startDate, fn ($q) => $q->whereDate('transactions.created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('transactions.created_at', '<=', $endDate));
    }

    /**
     * Revenue, cost of goods sold, expenses and profit for paid sales in a date range.
     * Revenue is after discount and excludes PPN, which is owed to the tax office.
     *
     * @return array{total_transactions: int, total_revenue: float, total_cogs: float, gross_profit: float, total_expenses: float, net_profit: float}
     */
    public static function profitSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $sales = static::lunas()->betweenDates($startDate, $endDate);

        $totalTransactions = (clone $sales)->count();
        $totalRevenue = (float) (clone $sales)->sum(DB::raw('total - tax_amount'));

        $totalCogs = (float) TransactionItem::query()
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.status', 'lunas')
            ->when($startDate, fn ($q) => $q->whereDate('transactions.created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('transactions.created_at', '<=', $endDate))
            ->sum(DB::raw('transaction_items.buy_price * transaction_items.quantity'));

        $totalExpenses = (float) Expense::query()
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('amount');

        $grossProfit = $totalRevenue - $totalCogs;

        return [
            'total_transactions' => $totalTransactions,
            'total_revenue' => $totalRevenue,
            'total_cogs' => $totalCogs,
            'gross_profit' => $grossProfit,
            'total_expenses' => $totalExpenses,
            'net_profit' => $grossProfit - $totalExpenses,
        ];
    }

    public static function generateNumber(): string
    {
        $prefix = 'TRX-' . date('Y') . '-';
        $lastTransaction = static::where('transaction_number', 'like', $prefix . '%')
            ->orderBy('transaction_number', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastNumber = (int) str_replace($prefix, '', $lastTransaction->transaction_number);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
