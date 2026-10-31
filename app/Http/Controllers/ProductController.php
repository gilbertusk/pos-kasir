<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->category && $request->category !== 'semua') {
            $query->where('category', $request->category);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        $products = $query->orderBy('name')->paginate(10);
        $lowStockCount = Product::lowStock()->count();
        $categories = ['semua', 'makanan', 'minuman', 'lainnya'];

        return view('products.index', compact('products', 'lowStockCount', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:products,code',
            'name' => 'required|string|max:255',
            'category' => 'required|in:makanan,minuman,lainnya',
            'unit' => 'required|string|max:50',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'description' => 'nullable|string',
        ]);

        $data = $request->except('image', 'stock');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($data, $request) {
            $product = Product::create($data + ['stock' => 0]);
            $openingStock = (int) $request->input('stock');

            if ($openingStock > 0) {
                $product->moveStock($openingStock, 'awal', $request->user()->id);
            }
        });

        return redirect()->route('products.index')->with('success', 'Barang berhasil ditambahkan!');
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'code' => 'required|string|unique:products,code,' . $product->id,
            'name' => 'required|string|max:255',
            'category' => 'required|in:makanan,minuman,lainnya',
            'unit' => 'required|string|max:50',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'description' => 'nullable|string',
        ]);

        // Stock only changes through adjustStock(), so every change is recorded.
        $data = $request->except('image', 'stock');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Barang berhasil diperbarui!');
    }

    public function destroy(Product $product)
    {
        if ($product->transactionItems()->exists()) {
            return redirect()->route('products.index')->with(
                'error',
                'Barang sudah memiliki riwayat transaksi sehingga tidak bisa dihapus. Nonaktifkan saja agar tidak tampil di kasir.'
            );
        }

        $product->delete();
        return redirect()->route('products.index')->with('success', 'Barang berhasil dihapus!');
    }

    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        return redirect()->route('products.index')->with('success', 'Status barang berhasil diubah!');
    }

    /**
     * Record incoming stock ("masuk", adds the quantity) or a stock count ("penyesuaian", sets the counted stock).
     */
    public function adjustStock(Request $request, Product $product)
    {
        $type = $request->input('type');

        $validated = $request->validate([
            'type' => 'required|in:masuk,penyesuaian',
            'quantity' => [Rule::requiredIf($type === 'masuk'), 'nullable', 'integer', 'min:1', 'max:100000'],
            'actual_stock' => [Rule::requiredIf($type === 'penyesuaian'), 'nullable', 'integer', 'min:0', 'max:1000000'],
            'note' => [Rule::requiredIf($type === 'penyesuaian'), 'nullable', 'string', 'max:255'],
        ], [
            'quantity.min' => 'Jumlah stok masuk minimal 1.',
            'actual_stock.min' => 'Stok hasil hitung tidak boleh negatif.',
            'note.required' => 'Tulis alasan penyesuaian stok.',
        ]);

        $movement = DB::transaction(function () use ($validated, $product, $request) {
            $locked = Product::lockForUpdate()->findOrFail($product->id);

            $change = $validated['type'] === 'masuk'
                ? (int) $validated['quantity']
                : (int) $validated['actual_stock'] - $locked->stock;

            if ($change === 0) {
                throw ValidationException::withMessages([
                    'actual_stock' => "Stok {$locked->name} sudah {$locked->stock}, tidak ada yang berubah.",
                ]);
            }

            return $locked->moveStock($change, $validated['type'], $request->user()->id, $validated['note'] ?? null);
        });

        $sign = $movement->quantity > 0 ? '+' : '';

        return back()->with('success', "Stok {$product->name} {$sign}{$movement->quantity}, sekarang {$movement->stock_after}.");
    }

    public function stockHistory(Request $request)
    {
        $request->validate([
            'product_id' => 'nullable|integer',
            'type' => ['nullable', Rule::in(array_keys(StockMovement::TYPES))],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $movements = StockMovement::with('product:id,name,code,unit', 'user:id,name', 'transaction:id,transaction_number')
            ->when($request->product_id, fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->start_date, fn ($q, $date) => $q->where('created_at', '>=', Carbon::parse($date)->startOfDay()))
            ->when($request->end_date, fn ($q, $date) => $q->where('created_at', '<=', Carbon::parse($date)->endOfDay()))
            ->latest('id')
            ->paginate(25);

        $products = Product::orderBy('name')->get(['id', 'name', 'code']);
        $types = StockMovement::TYPES;

        return view('products.stock-history', compact('movements', 'products', 'types'));
    }

    public function exportPdf(Request $request)
    {
        $query = Product::query();

        if ($request->category && $request->category !== 'semua') {
            $query->where('category', $request->category);
        }

        $products = $query->orderBy('name')->get();
        $pdf = Pdf::loadView('products.pdf', compact('products'));

        return $pdf->download('daftar-barang-' . date('Y-m-d') . '.pdf');
    }
}
