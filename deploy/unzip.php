<?php
/**
 * Unzip Helper for Shared Hosting (seperti InfinityFree)
 * 
 * Script ini membantu mengekstrak file ZIP proyek Laravel Anda langsung di server.
 * Ini menghemat waktu berjam-jam dibandingkan mengunggah ribuan file vendor satu per satu melalui FTP.
 * 
 * CARA PENGGUNAAN:
 * 1. Kompres semua file proyek Anda (termasuk folder vendor, bootstrap, public, dll) menjadi file ZIP.
 *    (Kecuali folder node_modules dan .git agar ukuran file kecil).
 * 2. Unggah file ZIP tersebut dan file `unzip.php` ini ke folder `htdocs` di server InfinityFree Anda.
 * 3. Akses berkas ini melalui peramban: https://domain-anda.com/unzip.php
 * 4. Klik tombol "Ekstrak Sekarang".
 * 5. Setelah berhasil, SEGERA klik tombol "Hapus Script Ini" demi keamanan!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 menit batas waktu eksekusi

$currentDir = __DIR__;
$zipFiles = glob("*.zip");
$message = '';
$status = '';

if (isset($_POST['action']) && $_POST['action'] === 'unzip') {
    $selectedZip = isset($_POST['zip_file']) ? $_POST['zip_file'] : '';
    
    if (empty($selectedZip)) {
        $message = "Silakan pilih file ZIP terlebih dahulu.";
        $status = "error";
    } elseif (!file_exists($selectedZip)) {
        $message = "File ZIP '$selectedZip' tidak ditemukan.";
        $status = "error";
    } else {
        if (!class_exists('ZipArchive')) {
            $message = "Fungsi PHP 'ZipArchive' tidak aktif di server ini. Silakan hubungi dukungan hosting Anda atau ekstrak file secara manual.";
            $status = "error";
        } else {
            $zip = new ZipArchive;
            $res = $zip->open($selectedZip);
            if ($res === TRUE) {
                // Ekstrak ke direktori saat ini
                if ($zip->extractTo($currentDir)) {
                    $zip->close();
                    $message = "<strong>Berhasil!</strong> Semua file dari <strong>$selectedZip</strong> telah diekstrak dengan sukses ke direktori utama.";
                    $status = "success";
                    
                    // Coba hapus file zip setelah ekstraksi jika dipilih
                    if (isset($_POST['delete_zip']) && $_POST['delete_zip'] == '1') {
                        @unlink($selectedZip);
                        $message .= "<br>File ZIP asal telah dihapus untuk menghemat ruang penyimpanan.";
                    }
                } else {
                    $zip->close();
                    $message = "Gagal mengekstrak file. Pastikan folder memiliki izin tulis (write permission).";
                    $status = "error";
                }
            } else {
                $message = "Gagal membuka file ZIP. File mungkin rusak atau tidak valid (Error code: " . $res . ").";
                $status = "error";
            }
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'delete_self') {
    if (@unlink(__FILE__)) {
        // Redirect ke index atau tampilkan pesan sukses
        header("Location: index.php");
        exit;
    } else {
        $message = "Gagal menghapus file `unzip.php`. Silakan hapus secara manual melalui File Manager atau FTP.";
        $status = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Easy Unzipper - InfinityFree Deployment</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(22, 30, 49, 0.7);
            --card-border: rgba(255, 255, 255, 0.08);
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(79, 70, 229, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.1) 0%, transparent 40%);
            background-attachment: fixed;
        }

        .container {
            width: 100%;
            max-width: 580px;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #a5b4fc 0%, #818cf8 50%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 0.95rem;
            color: var(--text-muted);
        }

        .alert {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 0.95rem;
            line-height: 1.5;
            border-left: 4px solid transparent;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border-color: var(--success);
            color: #a7f3d0;
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border-color: var(--error);
            color: #fecaca;
        }

        .alert-info {
            background-color: rgba(79, 70, 229, 0.1);
            border-color: var(--primary);
            color: #c7d2fe;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 8px;
            color: #d1d5db;
        }

        select, input[type="text"] {
            width: 100%;
            padding: 12px 16px;
            background: rgba(17, 24, 39, 0.5);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            color: var(--text-main);
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            outline: none;
        }

        select:focus, input[type="text"]:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.25);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--text-muted);
            cursor: pointer;
            margin-top: 10px;
        }

        .checkbox-group input {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 14px 24px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn-danger {
            background: var(--error);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            margin-top: 10px;
        }

        .btn-danger:hover {
            background: #dc2626;
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
        }

        .directory-info {
            background: rgba(17, 24, 39, 0.3);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .directory-info code {
            color: #cbd5e1;
            font-family: monospace;
            background: rgba(0, 0, 0, 0.2);
            padding: 2px 6px;
            border-radius: 4px;
        }

        .empty-state {
            text-align: center;
            padding: 20px;
            background: rgba(239, 68, 68, 0.05);
            border: 1px dashed rgba(239, 68, 68, 0.2);
            border-radius: 12px;
            color: #fca5a5;
            font-size: 0.9rem;
            margin-bottom: 24px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-muted);
            border-top: 1px solid var(--card-border);
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Laravel Easy Unzipper</h1>
            <p>InfinityFree Deployment Helper Script</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $status; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="directory-info">
            <strong>Direktori Ekstraksi:</strong> <code><?php echo $currentDir; ?></code><br>
            <em>Catatan: Semua file di dalam ZIP akan diekstrak langsung ke folder ini.</em>
        </div>

        <?php if ($status === 'success'): ?>
            <div class="alert alert-info">
                <strong>Langkah Selanjutnya:</strong><br>
                1. Konfigurasi file <code>.env</code> Anda sesuai dengan database InfinityFree.<br>
                2. Jalankan script <code>storage-link.php</code> di peramban Anda untuk membuat symlink storage.<br>
                3. Hapus script <code>unzip.php</code> ini untuk mencegah orang lain mengekstrak file lain secara ilegal!
            </div>
            
            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus script ini? Tindakan ini tidak dapat dibatalkan.');">
                <input type="hidden" name="action" value="delete_self">
                <button type="submit" class="btn btn-danger">🗑️ Hapus Script Ini Sekarang</button>
            </form>
        <?php else: ?>
            <?php if (empty($zipFiles)): ?>
                <div class="empty-state">
                    <strong>Tidak ada file ZIP ditemukan!</strong><br>
                    Unggah file ZIP proyek Anda (misalnya: <code>project.zip</code>) ke folder yang sama dengan file <code>unzip.php</code> ini terlebih dahulu.
                </div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="action" value="unzip">
                    
                    <div class="form-group">
                        <label for="zip_file">Pilih File ZIP Proyek Anda:</label>
                        <select name="zip_file" id="zip_file" required>
                            <?php foreach ($zipFiles as $file): ?>
                                <option value="<?php echo htmlspecialchars($file); ?>">
                                    <?php echo htmlspecialchars($file); ?> (<?php echo round(filesize($file) / 1024 / 1024, 2); ?> MB)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-group">
                            <input type="checkbox" name="delete_zip" value="1" checked>
                            Hapus file ZIP secara otomatis setelah ekstraksi selesai (Direkomendasikan)
                        </label>
                    </div>

                    <button type="submit" class="btn">🚀 Mulai Ekstrak Proyek</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <div class="footer">
            Dibuat khusus untuk mempercepat deployment Laravel ke Shared Hosting.
        </div>
    </div>
</body>
</html>
