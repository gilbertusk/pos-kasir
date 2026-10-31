# G Pos — Sistem Kasir untuk Usaha Makanan

G Pos adalah aplikasi kasir (point of sale) berbasis web untuk warung, rumah makan, dan kafe. Kasir bisa mencatat pesanan dengan cepat dari laptop, tablet, maupun ponsel, sementara owner memantau stok, transaksi, pengeluaran, dan laporan penjualan dari satu tempat.

Dibangun dengan Laravel 12, Blade, Alpine.js, dan Tailwind CSS.

---

## Daftar isi

- [Fitur](#fitur)
- [Hak akses per role](#hak-akses-per-role)
- [Teknologi](#teknologi)
- [Kebutuhan sistem](#kebutuhan-sistem)
- [Instalasi](#instalasi)
- [Akun demo](#akun-demo)
- [Konfigurasi](#konfigurasi)
- [Menjalankan test](#menjalankan-test)
- [Struktur project](#struktur-project)
- [Desain dan palet warna](#desain-dan-palet-warna)
- [Deploy](#deploy)
- [Keamanan](#keamanan)
- [Lisensi](#lisensi)

---

## Fitur

### Kasir
- Grid menu dengan foto, harga, dan sisa stok; filter kategori (makanan, minuman, lainnya) dan pencarian nama atau kode.
- Keranjang dengan jumlah per item, diskon persen (dibatasi maksimal sesuai pengaturan), dan PPN otomatis.
- Pembayaran tunai, QRIS, atau debit. Untuk tunai tersedia tombol uang pas, saran nominal, dan hitung kembalian.
- Struk langsung tampil setelah bayar dan bisa dicetak.
- Tampilan menyesuaikan layar: kolom pesanan di desktop, panel pesanan dari bawah di tablet dan ponsel.

### Inventaris
- Tambah, ubah, hapus, dan aktif/nonaktifkan barang, lengkap dengan foto.
- Peringatan stok menipis (stok ≤ stok minimum).
- Barang yang sudah pernah terjual tidak bisa dihapus agar riwayat penjualan tetap utuh; cukup dinonaktifkan.
- Ekspor daftar barang ke PDF.
- AI Assistant (Gemini) untuk menanyakan kondisi stok dan rekomendasi restock. Lihat [docs/ai-assistant-gemini.md](docs/ai-assistant-gemini.md).

### Transaksi
- Riwayat transaksi dengan filter tanggal dan pencarian nomor transaksi atau nama kasir.
- Detail transaksi dan cetak ulang struk.
- Pembatalan transaksi (owner) dari modal detail, wajib dengan alasan. Stok dikembalikan tepat satu kali, dan tercatat siapa serta kapan membatalkan.

### Laporan dan pengeluaran
- Ringkasan per rentang tanggal: pendapatan (setelah diskon, termasuk PPN), modal barang terjual, laba kotor, pengeluaran, dan laba bersih.
- Harga beli disimpan di setiap item transaksi, jadi perubahan harga beli barang tidak mengubah laba transaksi lama.
- Grafik pendapatan harian, pengeluaran harian, perbandingan keduanya, dan komposisi metode pembayaran.
- Pencatatan pengeluaran (listrik, gaji, perlengkapan, sewa) dengan lampiran bukti.

### Pengaturan
- Profil toko (nama, alamat, telepon, WhatsApp, NPWP, logo). Semua tampil di struk (WhatsApp dan NPWP hanya jika diisi), nama toko juga di sidebar.
- Struk: ukuran kertas 58mm/80mm, margin, dan cetak otomatis setelah bayar. Layar kasir dan cetak ulang memakai halaman struk yang sama. Struk transaksi batal ditandai "DIBATALKAN".
- Pajak dan diskon: aktif/nonaktif PPN, persentase PPN, batas diskon maksimal.
- Format nomor transaksi (`{YYYY}`, `{YY}`, `{MM}`, `{DD}`, `{XXX}`) dengan reset harian, bulanan, tahunan, atau tidak pernah. Nomor dijamin unik walau beberapa kasir membayar bersamaan.
- Pengaturan disimpan di cache dan langsung diperbarui setiap kali disimpan dari halaman Pengaturan. Jika tabel `settings` diubah langsung di database (misalnya lewat phpMyAdmin), jalankan `php artisan cache:clear` supaya perubahan terbaca.
- Manajemen pengguna: tambah akun owner/kasir dan nonaktifkan akun. Akun yang dinonaktifkan langsung tidak bisa login.

---

## Hak akses per role

| Halaman / aksi | Owner | Kasir |
| --- | :---: | :---: |
| Kasir (mencatat penjualan) | ✓ | ✓ |
| Riwayat transaksi, detail, cetak struk | ✓ | ✓ |
| Ringkasan laba di halaman transaksi | ✓ | – |
| Batalkan transaksi | ✓ | – |
| Inventaris, ekspor PDF, AI Assistant | ✓ | – |
| Analitik / laporan | ✓ | – |
| Pengeluaran | ✓ | – |
| Pengaturan dan manajemen pengguna | ✓ | – |
| Profil sendiri | ✓ | ✓ |

Pendaftaran akun publik dinonaktifkan. Akun baru hanya dibuat oleh owner melalui **Pengaturan → Pengguna**.

---

## Teknologi

| Lapisan | Teknologi |
| --- | --- |
| Backend | PHP 8.2+, Laravel 12, Laravel Breeze (autentikasi) |
| Database | MySQL / MariaDB (SQLite in-memory untuk test) |
| Frontend | Blade, Alpine.js 3, Tailwind CSS 3, Chart.js 4 |
| Build | Vite 7 |
| PDF | barryvdh/laravel-dompdf |
| AI | Google Gemini API (opsional) |
| Test | PHPUnit 11 |

---

## Kebutuhan sistem

- PHP 8.2 atau lebih baru dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`
- Composer 2
- Node.js 18+ dan npm
- MySQL 8 / MariaDB 10.4+ (misalnya dari XAMPP atau Laragon)

---

## Instalasi

```bash
# 1. Clone repository
git clone https://github.com/gilbertusk/pos-kasir.git
cd pos-kasir

# 2. Install dependency PHP dan JavaScript
composer install
npm install

# 3. Siapkan file environment
cp .env.example .env
php artisan key:generate
```

Buat database kosong (misalnya `pos_kasir_makanan`), lalu sesuaikan `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_kasir_makanan
DB_USERNAME=root
DB_PASSWORD=
```

Lanjutkan:

```bash
# 4. Buat tabel dan isi data contoh (menu, transaksi, pengaturan, akun demo)
php artisan migrate --seed

# 5. Hubungkan folder upload agar foto produk bisa diakses
php artisan storage:link

# 6. Build aset frontend
npm run build

# 7. Jalankan aplikasi
php artisan serve
```

Buka `http://localhost:8000`. Saat mengubah tampilan, jalankan `npm run dev` di terminal terpisah agar perubahan CSS/JS langsung terlihat.

> Foto contoh menu dibaca dari `storage/app/public/products/`. Jika foto tidak muncul, pastikan langkah 5 sudah dijalankan.

---

## Akun demo

Seeder membuat dua akun untuk mencoba aplikasi:

| Role | Email | Password |
| --- | --- | --- |
| Owner | `owner@pos.com` | `password` |
| Kasir | `kasir@pos.com` | `password` |

**Ganti password atau hapus akun demo ini sebelum aplikasi dipakai sungguhan.**

---

## Konfigurasi

Variabel penting di `.env`:

| Variabel | Fungsi | Contoh |
| --- | --- | --- |
| `APP_NAME` | Nama aplikasi di judul tab, sidebar, dan halaman login | `"G Pos"` |
| `APP_TIMEZONE` | Zona waktu pencatatan transaksi dan struk | `Asia/Jakarta` |
| `APP_URL` | URL aplikasi | `http://localhost:8000` |
| `DB_*` | Koneksi database | lihat [Instalasi](#instalasi) |
| `GEMINI_API_KEY` | API key untuk AI Assistant (opsional) | dari [Google AI Studio](https://aistudio.google.com/) |
| `GEMINI_MODEL` | Model Gemini yang dipakai | `gemini-2.5-flash-lite` |

Nama toko, pajak, dan batas diskon diatur dari halaman **Pengaturan** di dalam aplikasi, bukan dari `.env`.

---

## Menjalankan test

Test memakai SQLite in-memory, jadi tidak menyentuh database lokal Anda.

```bash
php artisan test
```

Cakupan test utama:

| File | Yang diuji |
| --- | --- |
| `tests/Feature/CheckoutTest.php` | Perhitungan total, diskon, PPN dari pengaturan, uang kurang ditolak, stok tidak cukup, barang nonaktif |
| `tests/Feature/RoleAccessTest.php` | Pembatasan halaman owner vs kasir |
| `tests/Feature/ProductManagementTest.php` | Hapus barang aman untuk riwayat, toggle status, pesan validasi tampil |
| `tests/Feature/UserManagementTest.php` | Proteksi hapus/nonaktifkan akun sendiri dan akun yang punya riwayat |
| `tests/Feature/TransactionTest.php` | Detail transaksi, pembatalan dengan alasan, stok dikembalikan sekali, tombol batal hanya untuk owner |
| `tests/Feature/TransactionNumberTest.php` | Format dan reset nomor transaksi, nomor tidak bentrok, validasi pengaturan |
| `tests/Feature/SettingCacheTest.php` | Pengaturan dibaca dari database sekali, perubahan langsung terlihat |
| `tests/Feature/RateLimitTest.php` | Batas request AI (10/menit, per akun) dan checkout (60/menit), reset setelah satu menit |
| `tests/Feature/ReceiptTest.php` | Struk membaca profil toko, ukuran kertas, margin, cetak otomatis, tanda batal; satu sumber struk di kasir dan cetak ulang |
| `tests/Feature/ProfitReportTest.php` | Harga beli tersimpan saat jual, laba kotor dan laba bersih, transaksi batal tidak dihitung, pendapatan termasuk PPN |
| `tests/Feature/AppConfigurationTest.php` | Zona waktu, skrip deploy tidak di `public/` dan wajib secret, tidak ada `env()` di kode aplikasi, konfigurasi Gemini |
| `tests/Feature/Auth/*` | Login, akun nonaktif, registrasi dinonaktifkan, reset password |

---

## Struktur project

```
app/
├── Http/
│   ├── Controllers/        # Kasir, Product, Transaction, Report, Expense, Setting, AiAssistant
│   ├── Middleware/         # EnsureUserHasRole (role:owner), EnsureUserIsActive
│   └── Requests/Auth/      # LoginRequest (menolak akun nonaktif)
├── Models/                 # Product, Transaction, TransactionItem, Expense, Setting, User
└── Helpers/helpers.php     # formatRupiah()
database/
├── migrations/             # Skema tabel + proteksi foreign key riwayat penjualan
├── factories/              # UserFactory, ProductFactory (untuk test)
└── seeders/                # Data contoh dan akun demo
resources/
├── css/app.css             # Komponen dasar (tombol, input, kartu)
└── views/                  # Blade: kasir, products, transactions, reports, expenses, settings
routes/web.php              # Rute aplikasi, dikelompokkan per role
tailwind.config.js          # Token warna palet Blirik
docs/                       # Panduan AI Assistant dan deploy InfinityFree
deploy/                     # Skrip bantu deploy shared hosting (jangan taruh di public/)
tests/Feature/              # Test fitur
```

---

## Desain dan palet warna

Tampilan memakai palet **Blirik**, terinspirasi piring dan gelas enamel hijau-putih khas warung. Semua warna didefinisikan sekali di [`tailwind.config.js`](tailwind.config.js), jadi mengganti palet cukup di satu file.

| Token | Warna | Dipakai untuk |
| --- | --- | --- |
| `brand` (hijau blirik) | `#1F4D3A` | Sidebar, navigasi, tombol utama, status lunas |
| `kunyit` | `#F2B33D` | Tombol **Bayar**, peringatan stok menipis |
| `enamel` | `#F6F7F3` – `#1B2620` | Latar, permukaan, garis, teks |
| `sambal` | `#B42318` | Hanya untuk bahaya: stok habis, error, hapus |

Font: [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans). Angka rupiah memakai `tabular-nums` agar sejajar.

Setelah mengubah view atau `tailwind.config.js`, jalankan `npm run build` sebelum deploy.

---

## Deploy

Panduan lengkap untuk shared hosting gratis (InfinityFree) ada di [docs/deploy-infinityfree.md](docs/deploy-infinityfree.md). Ringkasnya:

```bash
npm run build
composer install --no-dev --optimize-autoloader
```

Lalu unggah file, impor database, sesuaikan `.env` di server (`APP_ENV=production`, `APP_DEBUG=false`), dan buat symlink storage. Saat update aplikasi yang sudah online, jalankan migration lewat `deploy/migrate.php`, jangan impor ulang database.

Untuk server dengan akses SSH:

```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

### Daftar periksa production

Versi lengkap ada di [docs/deploy-infinityfree.md](docs/deploy-infinityfree.md#daftar-periksa-production).

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` lengkap dengan `https://`, `LOG_LEVEL=error`.
- [ ] Semua migration sudah jalan, tidak ada yang **Pending**.
- [ ] Password akun demo diganti atau akunnya dinonaktifkan.
- [ ] Profil toko, PPN, format nomor transaksi, dan ukuran kertas struk diatur di **Pengaturan**.
- [ ] Tidak ada skrip `deploy/*.php` yang tertinggal di `public/`.
- [ ] Konfigurasi di-cache (`php artisan optimize` atau `deploy/clear-cache.php`), dan diulang setiap `.env` berubah.
- [ ] Satu transaksi uji berhasil, struk tercetak pas, lalu dibatalkan.
- [ ] Backup database rutin, dan selalu sebelum update.

---

## Keamanan

- Jangan pernah meng-commit `.env`, file `.env.*` berisi kredensial, dump database, atau arsip ZIP berisi project. Semuanya sudah dimasukkan ke `.gitignore`.
- Matikan `APP_DEBUG` di production.
- Skrip di folder `deploy/` tidak memakai login aplikasi; salin ke server hanya saat dibutuhkan dan langsung hapus setelahnya. `clear-cache.php` dan `migrate.php` menolak akses sampai `$secret` diisi (minimal 20 karakter) dan dikirim lewat `?key=`.
- Kode aplikasi membaca `config()`, bukan `env()`, supaya tetap jalan setelah `config:cache`. Test `AppConfigurationTest` menjaga aturan ini.
- Ganti atau hapus akun demo sebelum dipakai untuk usaha sungguhan.
- Batas jumlah request per akun: AI Assistant (chat dan ringkasan stok digabung) 10 per menit untuk menjaga kuota Gemini, checkout 60 per menit. Request yang melewati batas mendapat status 429 dengan pesan berbahasa Indonesia.

---

## Lisensi

Dirilis di bawah lisensi [MIT](https://opensource.org/licenses/MIT).
