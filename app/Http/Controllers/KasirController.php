<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:tunai,qris,debit',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok {$product->name} tidak mencukupi. Tersisa: {$product->stock}"
                    ], 422);
                }

                $itemSubtotal = $product->sell_price * $item['quantity'];
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $product->sell_price,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $discountPercent = $request->discount_percent ?? 0;
            $discountAmount = round($subtotal * $discountPercent / 100);
            $afterDiscount = $subtotal - $discountAmount;

            $taxPercent = $request->tax_percent ?? 0;
            $taxAmount = round($afterDiscount * $taxPercent / 100);

            $total = $afterDiscount + $taxAmount;
            $paidAmount = $request->paid_amount;
            $changeAmount = max(0, $paidAmount - $total);

            $transaction = Transaction::create([
                'transaction_number' => Transaction::generateNumber(),
                'user_id' => Auth::id(),
                'subtotal' => $subtotal,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'status' => 'lunas',
            ]);

            foreach ($itemsData as $itemData) {
                TransactionItem::create(array_merge($itemData, [
                    'transaction_id' => $transaction->id,
                ]));

                // Kurangi stok
                Product::where('id', $itemData['product_id'])
                    ->decrement('stock', $itemData['quantity']);
            }

            DB::commit();

            $transaction->load('items.product', 'user');

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan!',
                'transaction' => $transaction,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
