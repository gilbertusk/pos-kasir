<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $category
 * @property string $unit
 * @property float $buy_price
 * @property float $sell_price
 * @property int $stock
 * @property int $min_stock
 * @property string|null $image
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product lowStock()
 */
class Product extends Model

{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'unit',
        'buy_price',
        'sell_price',
        'stock',
        'min_stock',
        'image',
        'description',
        'is_active',
    ];

    protected $casts = [
        'buy_price' => 'decimal:0',
        'sell_price' => 'decimal:0',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function scopeByCategory(\Illuminate\Database\Eloquent\Builder $query, string $category)

    {
        if ($category && $category !== 'semua') {
            return $query->where('category', $category);
        }
        return $query;
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Change the stock and record why. Call inside a database transaction on a row
     * read with lockForUpdate() (or created in that transaction), so stock_before is current.
     */
    public function moveStock(int $change, string $type, ?int $userId, ?string $note = null, ?int $transactionId = null): StockMovement
    {
        $before = $this->stock;
        $this->forceFill(['stock' => $before + $change])->save();

        return $this->stockMovements()->create([
            'user_id' => $userId,
            'transaction_id' => $transactionId,
            'type' => $type,
            'quantity' => $change,
            'stock_before' => $before,
            'stock_after' => $this->stock,
            'note' => $note,
        ]);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }
}
