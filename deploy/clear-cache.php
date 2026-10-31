<?php
/**
 * Bersihkan cache Laravel di shared hosting tanpa SSH (misalnya InfinityFree).
 *
 * CARA PENGGUNAAN:
 * 1. Isi $secret di bawah dengan teks acak yang panjang (minimal 20 karakter).
 * 2. Unggah file ini ke folder `public/` di server.
 * 3. Buka https://domain-anda.com/clear-cache.php?key=ISI_SECRET
 * 4. SEGERA HAPUS file ini dari server setelah selesai.
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

foreach (['optimize:clear', 'config:cache', 'route:cache', 'view:cache'] as $command) {
    $kernel->call($command);
    echo "== {$command}\n" . $kernel->output() . "\n";
}

echo "Selesai. HAPUS file clear-cache.php ini dari server sekarang.\n";
