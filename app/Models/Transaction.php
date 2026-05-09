<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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
