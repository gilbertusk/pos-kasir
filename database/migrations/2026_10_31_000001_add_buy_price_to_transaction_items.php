<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Store the purchase price on every sold line so profit stays correct
 * after a product's buy price changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->decimal('buy_price', 12, 0)->default(0)->after('price');
        });

        // Older sales did not record the cost; the current product cost is the best estimate available.
        DB::table('transaction_items')->update([
            'buy_price' => DB::raw('COALESCE((SELECT products.buy_price FROM products WHERE products.id = transaction_items.product_id), 0)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn('buy_price');
        });
    }
};
