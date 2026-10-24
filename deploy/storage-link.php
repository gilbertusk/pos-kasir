<?php
/**
 * Script Pembantu untuk Membuat Symlink Storage di Shared Hosting (seperti InfinityFree)
 * 
 * CARA PENGGUNAAN:
 * 1. Unggah berkas ini ke dalam folder `public/` di server Anda.
 * 2. Akses berkas ini melalui peramban web: https://domain-anda.com/storage-link.php
 * 3. Setelah berhasil memunculkan pesan sukses, SEGERA HAPUS file ini demi keamanan.
 */

// Tentukan path absolut untuk folder target (storage asli) dan folder link (yang diakses publik)
$targetFolder = dirname(__DIR__) . '/storage/app/public';
$linkFolder = __DIR__ . '/storage';

echo "<h3>Pembuat Symlink Laravel Storage</h3>";
echo "Mencoba membuat link simbolis...<br><br>";

// Cek apakah target folder ada
if (!is_dir($targetFolder)) {
    echo "<span style='color: red;'>[ERROR] Folder target tidak ditemukan di: $targetFolder</span><br>";
    echo "Pastikan folder tersebut sudah diunggah dan memiliki izin baca/tulis.<br>";
    exit;
}

// Cek apakah link folder sudah ada
if (file_exists($linkFolder) || is_link($linkFolder)) {
    echo "<span style='color: orange;'>[WARNING] Folder atau symlink '/public/storage' sudah ada di: $linkFolder.</span><br>";
    echo "Jika ini adalah direktori biasa dan bukan link simbolis, Anda perlu menghapusnya terlebih dahulu agar symlink bisa dibuat.<br>";
    
    // Tampilkan informasi tipe
    if (is_link($linkFolder)) {
        echo "Status saat ini: Ini adalah LINK SIMBOLIS (Symlink). Berarti sudah terhubung dengan benar!<br>";
    } else {
        echo "Status saat ini: Ini adalah DIREKTORI BIASA. <span style='color: red;'>PENTING: Hapus folder '/public/storage' kosong ini terlebih dahulu dari hosting Anda, lalu jalankan script ini kembali!</span><br>";
    }
} else {
    // Jalankan perintah pembuatan symlink
    if (symlink($targetFolder, $linkFolder)) {
        echo "<span style='color: green; font-weight: bold;'>[SUKSES] Symlink berhasil dibuat!</span><br><br>";
        echo "<strong>Detail Link:</strong><br>";
        echo "- Dari (Target): <code>$targetFolder</code><br>";
        echo "- Ke (Link): <code>$linkFolder</code><br><br>";
        echo "<span style='color: red; font-weight: bold;'>PENTING: SEGERA HAPUS file 'storage-link.php' ini dari folder 'public/' di server hosting Anda demi keamanan!</span>";
    } else {
        echo "<span style='color: red;'>[ERROR] Gagal membuat symlink. Kemungkinan hosting Anda menonaktifkan fungsi php 'symlink()'.</span><br>";
        echo "Jika gagal, alternatifnya Anda dapat mengunggah file secara langsung atau melakukan konfigurasi manual.<br>";
    }
}
