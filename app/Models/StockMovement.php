<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $product_id
 * @property int|null $user_id
 * @property int|null $transaction_id
 * @property string $type
 * @property int $quantity Signed change: positive adds stock, negative removes it
 * @property int $stock_before
 * @property int $stock_after
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class StockMovement extends Model
{
    public const TYPES = [
        'awal' => 'Stok awal',
        'masuk' => 'Stok masuk',
        'penyesuaian' => 'Penyesuaian',
        'penjualan' => 'Penjualan',
        'batal' => 'Batal transaksi',
    ];

    protected $fillable = [
        'product_id',
        'user_id',
        'transaction_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'note',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
