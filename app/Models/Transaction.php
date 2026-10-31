<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Support\TransactionNumberFormat;
use Carbon\CarbonInterface;
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
 * @property int|null $cancelled_by
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property string|null $cancel_reason
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
        'cancelled_by',
        'cancelled_at',
        'cancel_reason',
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
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Cancel a paid sale and put its stock back, exactly once.
     * The row is re-read under a lock, so a double click or two owners cancelling
     * at the same time can not restore stock twice.
     *
     * @return bool false when the sale was already cancelled
     */
    public function cancel(User $by, string $reason): bool
    {
        $cancelled = DB::transaction(function () use ($by, $reason) {
            $locked = static::whereKey($this->getKey())->lockForUpdate()->first();

            if (! $locked || $locked->status === 'batal') {
                return false;
            }

            $locked->update([
                'status' => 'batal',
                'cancelled_by' => $by->id,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ]);

            foreach ($locked->items()->get() as $item) {
                Product::whereKey($item->product_id)->increment('stock', $item->quantity);
            }

            return true;
        }, 3);

        $this->refresh();

        return $cancelled;
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

    /**
     * Issue the next transaction number using the format and reset period from Settings.
     * The period counter row is locked, so concurrent checkouts never get the same number.
     * Call inside the database transaction that saves the sale so the lock is held until commit.
     */
    public static function generateNumber(): string
    {
        $settings = Setting::getMultiple(['transaction_format', 'reset_number']);

        $format = TransactionNumberFormat::isValid($settings['transaction_format'])
            ? $settings['transaction_format']
            : TransactionNumberFormat::DEFAULT_FORMAT;
        $period = TransactionNumberFormat::effectivePeriod(
            $format,
            $settings['reset_number'] ?? TransactionNumberFormat::DEFAULT_RESET
        );

        $now = now();
        $key = TransactionNumberFormat::periodKey($period, $now);

        return DB::transaction(function () use ($format, $now, $key) {
            DB::table('transaction_sequences')->insertOrIgnore([
                'period' => $key,
                'last_number' => static::highestIssuedNumber($format, $now),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $sequence = DB::table('transaction_sequences')->where('period', $key)->lockForUpdate()->first();
            $next = $sequence->last_number;

            // Skip numbers taken outside the counter, e.g. imported data or a changed format.
            do {
                $next++;
                $number = TransactionNumberFormat::render($format, $now, $next);
            } while (static::where('transaction_number', $number)->exists());

            DB::table('transaction_sequences')->where('id', $sequence->id)->update([
                'last_number' => $next,
                'updated_at' => $now,
            ]);

            return $number;
        });
    }

    /**
     * Highest counter already used by existing numbers in this format, so a new period row
     * continues after data created before the counter table existed.
     */
    private static function highestIssuedNumber(string $format, CarbonInterface $date): int
    {
        $prefix = TransactionNumberFormat::prefix($format, $date);
        $pattern = TransactionNumberFormat::pattern($format, $date);

        return static::query()
            ->where('transaction_number', 'like', addcslashes($prefix, '\\%_') . '%')
            ->pluck('transaction_number')
            ->map(fn (string $number) => preg_match($pattern, $number, $m) ? (int) $m[1] : 0)
            ->max() ?? 0;
    }
}
