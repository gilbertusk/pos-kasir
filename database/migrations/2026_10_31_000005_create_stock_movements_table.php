<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Every change to a product's stock: opening stock, restock, stock count adjustment, sale and cancelled sale.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // Null only for the opening rows created by this migration.
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('type', 20);
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });

        // Existing stock has no history yet; start it from today's numbers.
        $now = now();
        DB::table('products')->where('stock', '>', 0)->orderBy('id')->each(function ($product) use ($now) {
            DB::table('stock_movements')->insert([
                'product_id' => $product->id,
                'type' => 'awal',
                'quantity' => $product->stock,
                'stock_before' => 0,
                'stock_after' => $product->stock,
                'note' => 'Stok saat riwayat mulai dicatat',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
