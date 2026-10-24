# Dokumentasi AI Kasir Assistant (Gemini API)

Fitur AI Kasir Assistant digunakan untuk membantu owner/kasir menganalisis data POS, memberikan rekomendasi restock, dan menyajikan ringkasan kondisi stok produk. Fitur ini menggunakan Gemini API sebagai layanan pemrosesan bahasa alami (NLP).

## 1. Persiapan dan Konfigurasi

### Cara Mendapatkan Gemini API Key
1. Buka situs [Google AI Studio](https://aistudio.google.com/).
2. Login menggunakan akun Google Anda.
3. Klik tombol **Get API key** atau masuk ke menu **API keys**.
4. Klik **Create API key** dan pilih project (atau buat project baru).
5. Salin API key yang dihasilkan.

### Cara Mengisi `.env`
Buka file `.env` di root folder project, lalu tambahkan atau sesuaikan baris berikut:

```env
AI_PROVIDER=gemini
GEMINI_API_KEY=masukkan_api_key_anda_di_sini
GEMINI_MODEL=gemini-2.5-flash-lite
```

**Catatan Keamanan Penting:**
- JANGAN PERNAH menaruh API key secara langsung di frontend (JavaScript/Blade).
- File `.env` tidak boleh di-commit atau di-push ke GitHub atau repository publik manapun (pastikan `.env` ada di dalam `.gitignore`).

## 2. Menjalankan Aplikasi

Pastikan Anda sudah menginstal dependensi (jika belum, jalankan `composer install` dan `npm install`).
Jalankan server pengembangan Laravel:

```bash
php artisan serve
```

Jika Anda menggunakan build tool frontend seperti Vite, jalankan juga (di terminal terpisah):

```bash
npm run dev
```

## 3. Cara Menggunakan Fitur

1. Login ke aplikasi POS sebagai Owner (fitur ini khusus owner).
2. Buka menu **Barang & Stok** (`/products`).
3. Di sudut kanan bawah halaman, terdapat tombol mengambang (floating button) dengan ikon "AI Assistant".
4. Klik tombol tersebut untuk membuka panel percakapan (chat).
5. Anda bisa memilih dari template pertanyaan yang tersedia atau mengetikkan pertanyaan Anda sendiri, misalnya:
   - "Produk apa yang stoknya kritis?"
   - "Produk apa yang harus saya restock hari ini?"
   - "Apakah ada produk yang stoknya habis?"
   - "Buatkan ringkasan kondisi stok saat ini."

## 4. Limitasi dan Catatan

- **Batasan Token / Quota:** Gemini API memiliki batasan penggunaan pada paket gratis (free tier). Jika Anda sering memanggil API ini, mungkin Anda akan terkena rate limit (pembatasan akses harian/menit). Jika error terjadi, cobalah tunggu beberapa saat sebelum mencoba kembali.
- **Konteks Data:** Saat ini, AI hanya memproses dan menjawab pertanyaan berdasarkan 50 data produk dengan prioritas stok terendah untuk menghemat token dan menjaga relevansi.
- **Batasan Kemampuan:** AI ini dirancang khusus untuk analisis dan rekomendasi secara *read-only*. AI tidak dapat melakukan perubahan stok atau memanipulasi database Anda.
- **Koneksi:** Pastikan server Anda memiliki koneksi internet yang baik untuk bisa terhubung ke server API Google (`generativelanguage.googleapis.com`).
