<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'category',
        'description',
        'amount',
        'attachment',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:0',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByCategory($query, $category)
    {
        if ($category && $category !== 'semua') {
            return $query->where('category', $category);
        }
        return $query;
    }

    public static function getCategoryBadgeColor(string $category): string
    {
        return match ($category) {
            'listrik' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'gaji' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'perlengkapan' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            'sewa' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };
    }
}
