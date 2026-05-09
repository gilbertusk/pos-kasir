<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // === USERS ===
        $owner = User::create([
            'name' => 'Akbar Hidayat',
            'email' => 'owner@pos.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $kasir = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'kasir@pos.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'is_active' => true,
        ]);

        // === PRODUCTS ===
        $products = [
            [
                'code' => 'MK-001',
                'name' => 'Nasi Goreng Spesial',
                'category' => 'makanan',
                'unit' => 'Porsi',
                'buy_price' => 12000,
                'sell_price' => 18000,
                'stock' => 50,
                'min_stock' => 10,
                'description' => 'Nasi goreng dengan telur, ayam, dan sayuran',
            ],
            [
                'code' => 'MK-002',
                'name' => 'Mie Goreng Telur + Sosis + Bakso',
                'category' => 'makanan',
                'unit' => 'Porsi',
                'buy_price' => 10000,
                'sell_price' => 15000,
                'stock' => 40,
                'min_stock' => 10,
                'description' => 'Mie goreng lengkap dengan telur, sosis, dan bakso',
            ],
            [
                'code' => 'MK-003',
                'name' => 'Ayam Geprek',
                'category' => 'makanan',
                'unit' => 'Porsi',
                'buy_price' => 13000,
                'sell_price' => 20000,
                'stock' => 30,
                'min_stock' => 8,
                'description' => 'Ayam geprek sambal pedas dengan nasi',
            ],
            [
                'code' => 'MN-001',
                'name' => 'Es Teh Manis',
                'category' => 'minuman',
                'unit' => 'Gelas',
                'buy_price' => 2000,
                'sell_price' => 5000,
                'stock' => 100,
                'min_stock' => 20,
                'description' => 'Teh manis dingin segar',
            ],
            [
                'code' => 'MN-002',
                'name' => 'Ocha 500ml',
                'category' => 'minuman',
                'unit' => 'Botol',
                'buy_price' => 4000,
                'sell_price' => 8000,
                'stock' => 3,
                'min_stock' => 12,
                'description' => 'Teh ocha kemasan botol 500ml',
            ],
            [
                'code' => 'MK-004',
                'name' => 'Bakso Malang Komplit',
                'category' => 'makanan',
                'unit' => 'Porsi',
                'buy_price' => 11000,
                'sell_price' => 17000,
                'stock' => 25,
                'min_stock' => 8,
                'description' => 'Bakso malang dengan tahu, siomay, dan mie',
            ],
            [
                'code' => 'MN-003',
                'name' => 'Jus Alpukat',
                'category' => 'minuman',
                'unit' => 'Gelas',
                'buy_price' => 7000,
                'sell_price' => 12000,
                'stock' => 20,
                'min_stock' => 5,
                'description' => 'Jus alpukat segar dengan susu coklat',
            ],
            [
                'code' => 'MK-005',
                'name' => 'Martabak Telur',
                'category' => 'makanan',
                'unit' => 'Porsi',
                'buy_price' => 15000,
                'sell_price' => 25000,
                'stock' => 4,
                'min_stock' => 5,
                'description' => 'Martabak telur isi daging dan sayuran',
            ],
            [
                'code' => 'LN-001',
                'name' => 'Kerupuk Udang',
                'category' => 'lainnya',
                'unit' => 'Bungkus',
                'buy_price' => 3000,
                'sell_price' => 5000,
                'stock' => 60,
                'min_stock' => 15,
                'description' => 'Kerupuk udang renyah',
            ],
            [
                'code' => 'MN-004',
                'name' => 'Kopi Susu Gula Aren',
                'category' => 'minuman',
                'unit' => 'Gelas',
                'buy_price' => 6000,
                'sell_price' => 10000,
                'stock' => 35,
                'min_stock' => 10,
                'description' => 'Kopi susu dengan gula aren premium',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // === TRANSACTIONS ===
        // Transaction 1
        $trx1 = Transaction::create([
            'transaction_number' => 'TRX-' . date('Y') . '-001',
            'user_id' => $owner->id,
            'subtotal' => 34000,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'tax_percent' => 11,
            'tax_amount' => 3740,
            'total' => 37740,
            'payment_method' => 'tunai',
            'paid_amount' => 50000,
            'change_amount' => 12260,
            'status' => 'lunas',
        ]);

        TransactionItem::create([
            'transaction_id' => $trx1->id,
            'product_id' => 1,
            'product_name' => 'Nasi Goreng Spesial',
            'quantity' => 1,
            'price' => 18000,
            'subtotal' => 18000,
        ]);

        TransactionItem::create([
            'transaction_id' => $trx1->id,
            'product_id' => 5,
            'product_name' => 'Ocha 500ml',
            'quantity' => 2,
            'price' => 8000,
            'subtotal' => 16000,
        ]);

        // Transaction 2
        $trx2 = Transaction::create([
            'transaction_number' => 'TRX-' . date('Y') . '-002',
            'user_id' => $owner->id,
            'subtotal' => 45000,
            'discount_percent' => 10,
            'discount_amount' => 4500,
            'tax_percent' => 11,
            'tax_amount' => 4455,
            'total' => 44955,
            'payment_method' => 'qris',
            'paid_amount' => 44955,
            'change_amount' => 0,
            'status' => 'lunas',
        ]);

        TransactionItem::create([
            'transaction_id' => $trx2->id,
            'product_id' => 3,
            'product_name' => 'Ayam Geprek',
            'quantity' => 1,
            'price' => 20000,
            'subtotal' => 20000,
        ]);

        TransactionItem::create([
            'transaction_id' => $trx2->id,
            'product_id' => 7,
            'product_name' => 'Jus Alpukat',
            'quantity' => 1,
            'price' => 12000,
            'subtotal' => 12000,
        ]);

        TransactionItem::create([
            'transaction_id' => $trx2->id,
            'product_id' => 2,
            'product_name' => 'Mie Goreng Telur + Sosis + Bakso',
            'quantity' => 1,
            'price' => 15000,
            'subtotal' => 15000,
        ]);

        // Transaction 3
        $trx3 = Transaction::create([
            'transaction_number' => 'TRX-' . date('Y') . '-003',
            'user_id' => $owner->id,
            'subtotal' => 52000,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'tax_percent' => 11,
            'tax_amount' => 5720,
            'total' => 57720,
            'payment_method' => 'debit',
            'paid_amount' => 57720,
            'change_amount' => 0,
            'status' => 'lunas',
        ]);

        TransactionItem::create([
            'transaction_id' => $trx3->id,
            'product_id' => 8,
            'product_name' => 'Martabak Telur',
            'quantity' => 2,
            'price' => 25000,
            'subtotal' => 50000,
        ]);

        TransactionItem::create([
            'transaction_id' => $trx3->id,
            'product_id' => 4,
            'product_name' => 'Es Teh Manis',
            'quantity' => 1,
            'price' => 5000,
            'subtotal' => 5000,
        ]);

        // === EXPENSES ===
        Expense::create([
            'user_id' => $owner->id,
            'date' => now()->subDays(3),
            'category' => 'listrik',
            'description' => 'Bayar listrik bulan ini',
            'amount' => 850000,
        ]);

        Expense::create([
            'user_id' => $owner->id,
            'date' => now()->subDays(2),
            'category' => 'gaji',
            'description' => 'Gaji mingguan karyawan 1-15',
            'amount' => 1500000,
        ]);

        Expense::create([
            'user_id' => $owner->id,
            'date' => now()->subDays(1),
            'category' => 'perlengkapan',
            'description' => 'Beli kantong plastik',
            'amount' => 250000,
        ]);

        Expense::create([
            'user_id' => $owner->id,
            'date' => now(),
            'category' => 'sewa',
            'description' => 'Sewa toko bulan ini',
            'amount' => 5000000,
        ]);

        // === SETTINGS ===
        $settings = [
            'store_name' => 'Pos System',
            'store_address' => 'Jl. Merdeka No. 123, Jakarta',
            'store_phone' => '021-12345678',
            'store_whatsapp' => '081234567890',
            'store_npwp' => '12.345.678.9-012.000',
            'store_logo' => null,
            'tax_enabled' => '1',
            'tax_percent' => '11',
            'max_discount' => '50',
            'price_rounding' => '0',
            'transaction_format' => 'TRX-{YYYY}-{XXX}',
            'reset_number' => 'monthly',
            'printer_size' => '80mm',
            'printer_margin' => '5',
            'auto_print' => '1',
        ];

        foreach ($settings as $key => $value) {
            Setting::create(['key' => $key, 'value' => $value]);
        }
    }
}
