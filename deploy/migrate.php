<?php
/**
 * Jalankan migration Laravel di shared hosting tanpa SSH (misalnya InfinityFree).
 * Dipakai saat update aplikasi menambah migration baru, supaya tidak perlu impor ulang database.
 *
 * CARA PENGGUNAAN:
 * 1. BACKUP database dulu lewat phpMyAdmin (tab Export).
 * 2. Isi $secret di bawah dengan teks acak yang panjang (minimal 20 karakter).
 * 3. Unggah file ini ke folder `public/` di server.
 * 4. Buka https://domain-anda.com/migrate.php?key=ISI_SECRET untuk melihat migration yang belum jalan.
 * 5. Buka https://domain-anda.com/migrate.php?key=ISI_SECRET&run=1 untuk menjalankannya.
 * 6. SEGERA HAPUS file ini dari server setelah selesai.
 */

$secret = '';

header('Content-Type: text/plain; charset=utf-8');

if (strlen($secret) < 20 || ! hash_equals($secret, (string) ($_GET['key'] ?? ''))) {
    http_response_code(403);
    exit("Akses ditolak. Isi \$secret (minimal 20 karakter) di file ini, lalu buka dengan ?key=ISI_SECRET.\n");
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$kernel->call('migrate:status');
echo "== Status migration\n" . $kernel->output() . "\n";

if (($_GET['run'] ?? '') !== '1') {
    exit("Belum ada yang dijalankan. Tambahkan &run=1 di URL untuk menjalankan migration yang berstatus Pending.\n");
}

$kernel->call('migrate', ['--force' => true]);
echo "== migrate\n" . $kernel->output() . "\n";

// Settings and config may have changed with the new code.
$kernel->call('optimize:clear');
echo "== optimize:clear\n" . $kernel->output() . "\n";

echo "Selesai. HAPUS file migrate.php ini dari server sekarang.\n";
