<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('subtotal', 15, 0)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 0)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 0)->default(0);
            $table->decimal('total', 15, 0)->default(0);
            $table->enum('payment_method', ['tunai', 'qris', 'debit'])->default('tunai');
            $table->decimal('paid_amount', 15, 0)->default(0);
            $table->decimal('change_amount', 15, 0)->default(0);
            $table->enum('status', ['lunas', 'batal'])->default('lunas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
