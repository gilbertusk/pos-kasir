<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantController extends Controller
{
    /**
     * Method untuk mengambil ringkasan stok.
     */
    public function insight()
    {
        try {
            $products = Product::all();
            
            $total_products = $products->count();
            
            // Mendeteksi produk dengan stok 0 (habis)
            $empty_products_list = $products->filter(function ($product) {
                return $product->stock == 0;
            });
            $empty_products_count = $empty_products_list->count();
            
            // Mendeteksi produk kritis (stok <= min_stock dan stok > 0)
            $critical_products_list = $products->filter(function ($product) {
                // If min_stock is not set, use 5 as default based on prompt
                $min = $product->min_stock ?? 5;
                return $product->stock > 0 && $product->stock <= $min;
            });
            $critical_products_count = $critical_products_list->count();
            
            // Menghitung jumlah produk aman
            $safe_products_count = $total_products - $empty_products_count - $critical_products_count;

            $empty_names = $empty_products_list->pluck('name')->toArray();
            $critical_names = $critical_products_list->pluck('name')->toArray();
            
            // Menyusun summary
            $summary_parts = [];
            $total_attention = $empty_products_count + $critical_products_count;
            
            if ($total_attention > 0) {
                $summary_parts[] = "Terdapat {$total_attention} produk yang perlu diperhatikan.";
                
                if ($empty_products_count > 0) {
                    $names = implode(', ', array_slice($empty_names, 0, 3));
                    if ($empty_products_count > 3) $names .= " dan " . ($empty_products_count - 3) . " lainnya";
                    $summary_parts[] = "{$names} habis.";
                }
                
                if ($critical_products_count > 0) {
                    $names = implode(', ', array_slice($critical_names, 0, 3));
                    if ($critical_products_count > 3) $names .= " dan " . ($critical_products_count - 3) . " lainnya";
                    $summary_parts[] = "{$names} berada di bawah batas minimum stok.";
                }
            } else {
                $summary_parts[] = "Semua stok produk saat ini dalam kondisi aman.";
            }
            
            $summary = implode(' ', $summary_parts);

            return response()->json([
                'success' => true,
                'total_products' => $total_products,
                'safe_products_count' => $safe_products_count,
                'critical_products_count' => $critical_products_count,
                'empty_products_count' => $empty_products_count,
                'critical_products' => $critical_products_list->values()->toArray(),
                'empty_products' => $empty_products_list->values()->toArray(),
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error('AI Assistant Insight Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil ringkasan stok. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Method POST untuk menerima pertanyaan user dan meneruskan ke Gemini API.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ], [
            'message.required' => 'Pesan tidak boleh kosong.',
            'message.max' => 'Pesan maksimal 1000 karakter.'
        ]);

        $apiKey = env('GEMINI_API_KEY');
        $model = env('GEMINI_MODEL', 'gemini-2.5-flash-lite');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'AI Assistant belum dapat digunakan. Periksa konfigurasi Gemini API Key.'
            ], 400);
        }

        try {
            // Ambil maksimal 50 produk, prioritaskan stok rendah/kritis
            // Sort by (stock - min_stock) ascending, so lowest relative stock comes first
            $products = Product::all()->sortBy(function($product) {
                $min = $product->min_stock ?? 5;
                return $product->stock - $min;
            })->take(50);

            if ($products->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data produk masih kosong, AI belum bisa memberikan analisis.'
                ]);
            }

            // Susun context data produk dalam bentuk teks ringkas
            $contextText = "Data Produk Saat Ini:\n";
            foreach ($products as $product) {
                $status = $product->is_active ? 'aktif' : 'nonaktif';
                $minStock = $product->min_stock ?? 5;
                $buyPrice = 'Rp' . number_format($product->buy_price, 0, ',', '.');
                $sellPrice = 'Rp' . number_format($product->sell_price, 0, ',', '.');
                
                $contextText .= "- {$product->name} | kategori: {$product->category} | satuan: {$product->unit} | stok: {$product->stock} | minimum stok: {$minStock} | harga modal: {$buyPrice} | harga jual: {$sellPrice} | status: {$status}\n";
            }

            $systemPrompt = "Anda adalah AI Assistant untuk sistem POS restoran.
Tugas Anda:
1. Membantu owner/kasir memahami kondisi stok produk.
2. Memberikan rekomendasi restock.
3. Memberikan ringkasan kondisi stok.

Aturan Format Jawaban:
1. Gunakan bahasa Indonesia yang singkat dan jelas.
2. Jangan gunakan markdown berlebihan.
3. Jangan tampilkan teks mentah seperti **produk** jika frontend belum mendukung.
4. Jika menyebut produk, gunakan format (tanpa bold/tanda bintang):
   - Nama Produk
     Stok saat ini: [jumlah]
     Minimum stok: [jumlah]
     Status: [status]
5. Jika ada rekomendasi, tulis bagian:
   Rekomendasi:
6. Jangan membuat tabel panjang di chat.
7. Jangan mengarang data.
8. Jawab hanya berdasarkan data POS yang dikirim backend.
9. Jika data tidak tersedia, jawab: \"Data belum tersedia pada sistem POS.\"

Berikut adalah data produk:
{$contextText}


Pertanyaan user: {$request->message}";

            $response = Http::withHeaders([
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $systemPrompt
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $reply = $data['candidates'][0]['content']['parts'][0]['text'];
                    return response()->json([
                        'success' => true,
                        'reply' => $reply
                    ]);
                } else {
                    Log::error('Gemini API Error: Format response tidak sesuai', $data);
                    return response()->json([
                        'success' => false,
                        'message' => 'Format balasan dari AI tidak sesuai.'
                    ], 500);
                }
            } else {
                Log::error('Gemini API Error: ' . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal terhubung ke layanan AI. ' . ($response->json('error.message') ?? 'Silakan coba beberapa saat lagi.')
                ], $response->status());
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gemini API Timeout/Connection Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Koneksi ke AI timeout. Silakan coba lagi nanti.'
            ], 504);
        } catch (\Exception $e) {
            Log::error('AI Assistant Chat Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.'
            ], 500);
        }
    }
}
