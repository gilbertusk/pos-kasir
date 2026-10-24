<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deleting a user or product must never silently wipe transactions,
 * transaction items or expenses that reference it.
 */
return new class extends Migration
{
    private const FOREIGN_KEYS = [
        ['transactions', 'user_id', 'users'],
        ['expenses', 'user_id', 'users'],
        ['transaction_items', 'product_id', 'products'],
    ];

    public function up(): void
    {
        foreach (self::FOREIGN_KEYS as [$table, $column, $references]) {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $references) {
                $blueprint->dropForeign([$column]);
                $blueprint->foreign($column)->references('id')->on($references)->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (self::FOREIGN_KEYS as [$table, $column, $references]) {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $references) {
                $blueprint->dropForeign([$column]);
                $blueprint->foreign($column)->references('id')->on($references)->cascadeOnDelete();
            });
        }
    }
};
