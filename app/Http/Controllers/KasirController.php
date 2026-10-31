<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class KasirController extends Controller
{
    public function index()
    {
        $products = Product::active()->orderBy('name')->get();
        $categories = ['semua', 'makanan', 'minuman', 'lainnya'];
        $taxEnabled = Setting::getValue('tax_enabled', '1');
        $taxPercent = Setting::getValue('tax_percent', '11');
        $maxDiscount = Setting::getValue('max_discount', '50');

        return view('kasir.index', compact('products', 'categories', 'taxEnabled', 'taxPercent', 'maxDiscount'));
    }

    public function getProducts(Request $request)
    {
        $query = Product::active();

        if ($request->category && $request->category !== 'semua') {
            $query->where('category', $request->category);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        return response()->json($query->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        // Tax and discount limits always come from the store settings, never from the client.
        $maxDiscount = (float) Setting::getValue('max_discount', '50');
        $taxPercent = (bool) Setting::getValue('tax_enabled', '1')
            ? (float) Setting::getValue('tax_percent', '11')
            : 0.0;

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|distinct|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'discount_percent' => 'nullable|numeric|min:0|max:' . $maxDiscount,
            'payment_method' => 'required|in:tunai,qris,debit',
            'paid_amount' => 'required|numeric|min:0',
        ], [
            'items.*.product_id.distinct' => 'Barang yang sama tidak boleh muncul dua kali di keranjang.',
            'discount_percent.max' => "Diskon maksimal {$maxDiscount}%.",
        ]);

        try {
            $transaction = DB::transaction(fn () => $this->createTransaction($validated, $taxPercent));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan transaksi kasir', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan transaksi. Silakan coba lagi.',
            ], 500);
        }

        $transaction->load('items.product', 'user');

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil disimpan!',
            'transaction' => $transaction,
        ]);
    }

    /**
     * Price the cart from the database, check stock under a row lock, and persist the sale.
     * Must run inside a database transaction.
     *
     * @throws ValidationException when stock, product status or payment is not acceptable
     */
    private function createTransaction(array $validated, float $taxPercent): Transaction
    {
        $subtotal = 0;
        $itemsData = [];

        foreach ($validated['items'] as $item) {
            $product = Product::lockForUpdate()->findOrFail($item['product_id']);

            if (! $product->is_active) {
                throw ValidationException::withMessages([
                    'items' => "{$product->name} sedang tidak dijual.",
                ]);
            }

            if ($product->stock < $item['quantity']) {
                throw ValidationException::withMessages([
                    'items' => "Stok {$product->name} tidak mencukupi. Tersisa: {$product->stock}",
                ]);
            }

            $itemSubtotal = $product->sell_price * $item['quantity'];
            $subtotal += $itemSubtotal;

            $itemsData[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'price' => $product->sell_price,
                'buy_price' => $product->buy_price,
                'subtotal' => $itemSubtotal,
            ];
        }

        $discountPercent = (float) ($validated['discount_percent'] ?? 0);
        $discountAmount = round($subtotal * $discountPercent / 100);
        $afterDiscount = $subtotal - $discountAmount;

        $taxAmount = round($afterDiscount * $taxPercent / 100);
        $total = $afterDiscount + $taxAmount;

        // Non-cash payments are always settled for exactly the total.
        $paidAmount = $validated['payment_method'] === 'tunai'
            ? (float) $validated['paid_amount']
            : $total;

        if ($paidAmount < $total) {
            throw ValidationException::withMessages([
                'paid_amount' => 'Uang yang diterima kurang dari total belanja.',
            ]);
        }

        $transaction = Transaction::create([
            'transaction_number' => Transaction::generateNumber(),
            'user_id' => Auth::id(),
            'subtotal' => $subtotal,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'payment_method' => $validated['payment_method'],
            'paid_amount' => $paidAmount,
            'change_amount' => $paidAmount - $total,
            'status' => 'lunas',
        ]);

        foreach ($itemsData as $itemData) {
            TransactionItem::create(array_merge($itemData, [
                'transaction_id' => $transaction->id,
            ]));

            Product::whereKey($itemData['product_id'])->decrement('stock', $itemData['quantity']);
        }

        return $transaction;
    }
}
