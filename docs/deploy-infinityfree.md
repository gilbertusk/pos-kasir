# Panduan Deployment Laravel ke InfinityFree

Dokumen ini berisi panduan langkah demi langkah untuk menyebarkan (deploy) aplikasi **POS Kasir Makanan** Anda ke **InfinityFree** (shared hosting gratis). 

Karena Laravel dirancang untuk server dengan akses SSH (Terminal), ada beberapa penyesuaian khusus yang harus dilakukan saat mendeploy ke shared hosting seperti InfinityFree yang hanya menyediakan akses FTP dan phpMyAdmin.

---

## Ringkasan Alur Deployment

```mermaid
graph TD
    A[Persiapan Lokal] --> B[Build Frontend Assets npm run build]
    B --> C[Prune Composer Vendor vendor --no-dev]
    C --> D[Ekspor Database MySQL lokal]
    D --> E[Upload File via FTP ke htdocs]
    E --> F[Buat Database & Impor SQL di InfinityFree]
    F --> G[Konfigurasi .env di Server]
    G --> H[Jalankan Script Symlink Storage]
    H --> I[Cache Konfigurasi]
    I --> J[Daftar Periksa Production]
    J --> K[Aplikasi Siap Digunakan!]
```

---

## Langkah 1: Persiapan di Komputer Lokal (Local)

Sebelum mengunggah file, kita perlu menyiapkan file agar siap berjalan di lingkungan produksi (production).

### 1. Jalankan Build Assets (Vite)
Aplikasi ini menggunakan Vite dan Tailwind CSS. Anda harus melakukan kompilasi file aset CSS dan JS terlebih dahulu agar berjalan optimal di produksi.
Jalankan perintah berikut di terminal lokal Anda:
```bash
npm run build
```
Perintah ini akan menghasilkan folder `public/build` yang berisi file CSS & JS produksi. **Pastikan folder ini ada sebelum Anda mengunggah file.**

### 2. Bersihkan Dependensi Development (Composer)
Secara default, folder `vendor` hasil instalasi lokal berisi package untuk pengembangan dan pengujian (seperti PHPUnit, Mockery, dll.).
InfinityFree memiliki **batasan Inode (maksimal 30.000 file)**. Jika Anda mengunggah seluruh folder `vendor` apa adanya, Anda bisa dengan mudah melebihi batas ini.

Untuk menguranginya, bersihkan package development dengan menjalankan perintah ini di terminal lokal Anda:
```bash
composer install --no-dev --optimize-autoloader
```
*Catatan: Jika nanti Anda ingin mengembangkan lagi secara lokal, jalankan saja `composer install` kembali.*

### 3. Ekspor Database Lokal
1. Buka phpMyAdmin lokal Anda (`http://localhost/phpmyadmin`).
2. Pilih database `pos_kasir_makanan`.
3. Klik tab **Export** (Ekspor), lalu klik **Export** untuk mengunduh file `.sql` (misal: `pos_kasir_makanan.sql`).

---

## Langkah 2: Persiapan di Panel InfinityFree

1. Masuk ke **Client Area InfinityFree** Anda.
2. Buka **Control Panel (cPanel)** untuk akun hosting Anda.
3. **Buat Database MySQL Baru**:
   - Masuk ke menu **MySQL Databases**.
   - Buat database baru (misalnya dengan nama belakang `poskasir`). Nama lengkap database akan terlihat seperti `if0_XXXXXXXX_poskasir`.
   - Catat detail database: **DB_HOST**, **DB_NAME**, **DB_USER**, dan **DB_PASSWORD** (sesuai yang tertera di panel kontrol atau yang telah Anda konfigurasikan di file `.env.infinityfree`).
4. **Impor Database**:
   - Buka **phpMyAdmin** dari Control Panel InfinityFree untuk database baru tersebut.
   - Klik tab **Import** (Impor).
   - Pilih file `.sql` yang telah Anda ekspor dari lokal sebelumnya.
   - Klik **Go** / **Import** untuk mengunggah dan menjalankan skema database.

---

## Langkah 3: Mengunggah File ke InfinityFree

Karena InfinityFree tidak memiliki terminal SSH, Anda harus mengunggah file secara manual.

> [!WARNING]
> **JANGAN PERNAH mengunggah folder `node_modules`!** 
> Folder `node_modules` memiliki puluhan ribu file kecil yang akan langsung membuat kuota Inode akun Anda habis/di-suspend, dan folder ini sama sekali tidak dibutuhkan di server hosting karena aset sudah di-compile di langkah sebelumnya.

### Cara Terbaik: Menggunakan FTP (FileZilla)
1. Unduh dan instal [FileZilla Client](https://filezilla-project.org/).
2. Dapatkan kredensial FTP Anda dari panel InfinityFree (FTP Host, FTP Username, FTP Password).
3. Hubungkan FileZilla ke server InfinityFree.
4. Di panel kanan (Server), masuk ke dalam folder **`htdocs`**.
5. Unggah **semua file dan folder** dari proyek lokal Anda ke dalam folder **`htdocs`** tersebut, **KECUALI**:
   - `node_modules/` (Jangan diunggah)
   - `.git/` & `.github/` (Jangan diunggah)
   - `.env` lokal Anda (buat `.env` baru di server nanti)
   - `tests/` (Opsional, tidak perlu diunggah)

*Tips Keamanan & Struktur:*
Folder `htdocs` di InfinityFree akan bertindak sebagai root aplikasi Anda. File `.htaccess` di root proyek Anda sudah diatur untuk otomatis mengarahkan lalu lintas ke folder `public/`, sehingga Anda bisa mengunggah semuanya langsung di dalam `htdocs`.

---

## Langkah 4: Konfigurasi File `.env` di Server

1. Setelah semua file terunggah, cari file bernama `.env.infinityfree` di dalam folder `htdocs` server Anda menggunakan File Manager bawaan InfinityFree atau FileZilla.
2. **Ubah nama (Rename)** file `.env.infinityfree` menjadi **`.env`**.
3. Buka file `.env` tersebut dan pastikan konfigurasinya sudah benar:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://poskasir.infinityfreeapp.com` (Ganti dengan domain asli Anda di InfinityFree jika berbeda)
   - Konfigurasi database `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` harus sesuai dengan database MySQL yang Anda buat di InfinityFree.
   - Jika Anda memiliki API Key Gemini untuk fitur AI Assistant, isi di bagian `GEMINI_API_KEY`.

---

## Langkah 5: Membuat Symlink Storage (storage:link)

Laravel menyimpan berkas unggahan di `storage/app/public` dan membutuhkan link simbolis (symlink) di `public/storage` agar bisa diakses secara publik oleh web browser. Di server biasa, kita menjalankan `php artisan storage:link`. Di InfinityFree, kita bisa melakukannya menggunakan script PHP sederhana.

Kami telah membuat skrip pembantu otomatis untuk Anda. 

> [!IMPORTANT]
> Skrip pembantu (`storage-link.php`, `unzip.php`, `clear-cache.php`, `migrate.php`) disimpan di folder **`deploy/`** proyek, **bukan** di `public/`, karena siapa pun bisa menjalankannya tanpa login. Salin ke `public/` di server hanya saat dibutuhkan, lalu segera hapus lagi setelah dijalankan.

### Cara Membuat Symlink secara Otomatis:
1. Salin file **`deploy/storage-link.php`** dari proyek ke folder **`public/`** di server.
2. Akses skrip tersebut melalui browser Anda di URL:
   `https://poskasir.infinityfreeapp.com/storage-link.php` (Ganti dengan domain Anda).
3. Jika tertulis **"Symlink berhasil dibuat!"**, maka konfigurasi selesai.
4. **PENTING:** Demi keamanan, segera **HAPUS** file `storage-link.php` dari server setelah berhasil dijalankan agar tidak disalahgunakan oleh pihak lain.

---

## Langkah 6: Cache Konfigurasi

Supaya aplikasi lebih cepat, simpan konfigurasi, route, dan view di cache:

1. Buka `deploy/clear-cache.php`, isi `$secret` dengan teks acak minimal 20 karakter.
2. Salin ke folder `public/` di server.
3. Buka `https://domain-anda.com/clear-cache.php?key=ISI_SECRET`.
4. **Segera hapus** `clear-cache.php` dari server.

Skrip ini menjalankan `optimize:clear`, lalu `config:cache`, `route:cache`, dan `view:cache`. Jalankan lagi setiap kali `.env` di server diubah, karena konfigurasi yang sudah di-cache tidak membaca ulang `.env`.

---

## Memperbarui Aplikasi yang Sudah Online

Jangan impor ulang database saat update, karena data penjualan di server akan tertimpa. Ikuti urutan ini:

1. **Backup database server** lewat phpMyAdmin (tab **Export**). Simpan file `.sql`-nya.
2. Di komputer lokal, jalankan `npm run build` dan `composer install --no-dev --optimize-autoloader`.
3. Unggah file yang berubah lewat FTP (minimal `app/`, `config/`, `database/`, `resources/`, `routes/`, `public/build/`, dan `vendor/` jika dependensi berubah). Jangan timpa `.env` dan `storage/app/public/` di server.
4. Jalankan migration baru:
   - Buka `deploy/migrate.php`, isi `$secret`, salin ke `public/` di server.
   - Buka `https://domain-anda.com/migrate.php?key=ISI_SECRET` untuk melihat migration yang berstatus **Pending**.
   - Buka `https://domain-anda.com/migrate.php?key=ISI_SECRET&run=1` untuk menjalankannya.
   - **Segera hapus** `migrate.php` dari server.
5. Jalankan `clear-cache.php` seperti di Langkah 6, lalu hapus lagi.
6. Coba login, lakukan satu transaksi uji, cetak struknya, lalu batalkan transaksi itu dari Riwayat Transaksi.

> [!WARNING]
> Update Oktober 2026 menambah 3 migration: harga modal di item transaksi, counter nomor transaksi, dan data pembatalan. Tanpa migration ini halaman Kasir, Transaksi, dan Laporan akan error.

---

## Daftar Periksa Production

Centang semua sebelum aplikasi dipakai untuk usaha sungguhan.

**Konfigurasi `.env` di server**
- [ ] `APP_ENV=production` dan `APP_DEBUG=false`. Dengan debug aktif, pesan error menampilkan password database.
- [ ] `APP_KEY` terisi, dan sama dengan yang dipakai sejak awal. Mengganti `APP_KEY` membuat semua user ter-logout.
- [ ] `APP_URL` lengkap dengan `https://`, misalnya `https://poskasir.kesug.com`. Tanpa skema, link dan aset bisa salah alamat.
- [ ] `APP_TIMEZONE=Asia/Jakarta` (atau zona waktu toko).
- [ ] `LOG_LEVEL=error` supaya file log tidak cepat penuh.
- [ ] `DB_*` sesuai database InfinityFree (host seperti `sqlXXX.infinityfree.com`, bukan `localhost`).
- [ ] `CACHE_STORE` dan `SESSION_DRIVER` diisi `file` atau `database`. Batas request (rate limit) dan cache pengaturan memakai cache ini.
- [ ] `GEMINI_API_KEY` diisi jika AI Assistant dipakai. Setelah mengubahnya, jalankan `clear-cache.php`.

**Data dan akun**
- [ ] Semua migration sudah jalan (cek lewat `migrate.php` tanpa `&run=1`, tidak ada yang **Pending**).
- [ ] Password akun demo `owner@pos.com` dan `kasir@pos.com` sudah diganti, atau akunnya dinonaktifkan.
- [ ] Profil toko, PPN, format nomor transaksi, ukuran kertas, dan margin struk sudah diatur di halaman **Pengaturan**.
- [ ] Data contoh dari seeder (produk, transaksi, pengeluaran) dihapus jika tidak dipakai, supaya laporan tidak tercampur.

**File di server**
- [ ] Tidak ada skrip bantu yang tertinggal di `public/`: `storage-link.php`, `unzip.php`, `clear-cache.php`, `migrate.php`.
- [ ] `https://domain-anda.com/.env` menampilkan error 403/404, bukan isi file.
- [ ] Folder `node_modules/`, `.git/`, dan `tests/` tidak ikut diunggah.
- [ ] `public/build/` ada (hasil `npm run build`), dan foto produk tampil (symlink storage berhasil).

**Uji coba**
- [ ] Login sebagai kasir: menu Produk, Laporan, Pengeluaran, dan Pengaturan tidak muncul.
- [ ] Satu transaksi uji berhasil, struk tercetak pas di kertas printer, nomor transaksi sesuai format.
- [ ] Transaksi uji dibatalkan dengan alasan, stok kembali, dan transaksi tidak dihitung di Laporan.

**Rutin**
- [ ] Backup database lewat phpMyAdmin minimal seminggu sekali, dan selalu sebelum update.

---

## Troubleshooting & Tips Tambahan

* **Error 500 (Internal Server Error)**: 
  Biasanya disebabkan oleh versi PHP yang tidak cocok (pastikan versi PHP di InfinityFree diatur ke **PHP 8.2** atau **PHP 8.3** agar sesuai dengan kebutuhan Laravel 12) atau ada kesalahan penulisan di file `.env`.
* **Error "Database connection refused"**:
  Periksa kembali `DB_HOST` dan `DB_PASSWORD` di `.env`. Host database di InfinityFree bukan `127.0.0.1` atau `localhost`, melainkan nama host khusus seperti `sqlXXX.infinityfree.com`.
* **Perubahan `.env` atau pengaturan tidak terbaca**:
  Jalankan `deploy/clear-cache.php` (lihat Langkah 6). Ini juga diperlukan jika tabel `settings` diubah langsung lewat phpMyAdmin.
* **Error "Table ... doesn't exist" setelah update**:
  Ada migration yang belum dijalankan. Lihat bagian [Memperbarui Aplikasi yang Sudah Online](#memperbarui-aplikasi-yang-sudah-online).
* **Pesan "Terlalu banyak permintaan"**:
  Batas AI Assistant 10 permintaan per menit per akun, checkout 60 per menit. Tunggu satu menit.
