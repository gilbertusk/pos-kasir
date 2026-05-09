<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function scopeByCategory($query, $category)
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

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }
}
