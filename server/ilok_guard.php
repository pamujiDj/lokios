<?php
/**
 * ILOK GUARD - hanya mengizinkan akses ujian dari aplikasi IKHLAS LOCK (ILOK).
 *
 * CARA PAKAI (website PHP, mis. CodeIgniter / Laravel / PHP biasa):
 * 1. Upload file ini ke folder web utama (sejajar index.php).
 * 2. Pada BARIS PERTAMA index.php (setelah <?php) tambahkan:
 *        require __DIR__ . '/ilok_guard.php';
 * 3. Samakan ILOK_SECRET dengan Config.SECRET di aplikasi, dan ILOK_WINDOW dengan Config.TOKEN_WINDOW_SEC.
 * 4. Isi ILOK_ALLOW_PATHS / ILOK_ALLOW_IPS agar halaman admin & guru tetap bisa dibuka lewat browser biasa.
 *
 * Set ILOK_ENFORCE = false untuk MODE PANTAU: tidak memblokir, hanya mencatat ke ilok_log.txt
 * siapa saja yang membuka tanpa aplikasi (bagus untuk uji coba).
 */

define('ILOK_SECRET', 'GANTI_DENGAN_KUNCI_RAHASIA_ANDA_123'); // harus sama dengan di aplikasi
define('ILOK_WINDOW', 600);                                    // detik, harus sama dengan di aplikasi
define('ILOK_ENFORCE', true);                                  // true = blokir, false = hanya catat
define('ILOK_ALLOW_PATHS', ['/admin', '/uploads', '/assets']); // awalan path yang BOLEH tanpa aplikasi
define('ILOK_ALLOW_IPS', []);                                  // IP guru/admin, mis. ['192.168.1.10']

/** Mengembalikan data perangkat bila UA valid, atau null bila tidak valid. */
function ilok_check()
{
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    if (!preg_match('/ILOK\/([\d.]+) t=(\d+) k=([a-f0-9]{16}) d=([a-f0-9]{8}) v=(\d+)/', $ua, $m)) {
        return null;
    }
    $nowWindow = (int) floor(time() / ILOK_WINDOW);
    if (abs($nowWindow - (int) $m[2]) > 1) {
        return null; // token kedaluwarsa / jam HP salah
    }
    $calc = substr(hash_hmac('sha256', $m[2] . '|' . $m[4], ILOK_SECRET), 0, 16);
    if (!hash_equals($calc, $m[3])) {
        return null; // kunci tidak cocok (UA palsu)
    }
    return array('version' => $m[1], 'device' => $m[4], 'violations' => (int) $m[5]);
}

function ilok_log($text)
{
    @file_put_contents(__DIR__ . '/ilok_log.txt', date('Y-m-d H:i:s') . ' ' . $text . "\n", FILE_APPEND | LOCK_EX);
}

(function () {
    $path = parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH);
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

    foreach (ILOK_ALLOW_PATHS as $p) {
        if (strpos($path, $p) === 0) return;
    }
    if (in_array($ip, ILOK_ALLOW_IPS, true)) return;

    $info = ilok_check();
    if ($info !== null) {
        $GLOBALS['ILOK_INFO'] = $info; // tersedia untuk kode lain: device id & jumlah pelanggaran
        if ($info['violations'] > 0) {
            ilok_log("PELANGGARAN ip=$ip device={$info['device']} v={$info['violations']} path=$path");
        }
        return;
    }

    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '-';
    ilok_log("TANPA-APLIKASI ip=$ip path=$path ua=" . substr($ua, 0, 120));
    if (!ILOK_ENFORCE) return;

    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1"><title>Akses Ditolak</title>'
        . '<style>body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;'
        . 'background:linear-gradient(#0A0F2C,#2A1B5E);font-family:sans-serif;color:#fff;text-align:center;padding:24px}'
        . '.c{max-width:420px}h1{font-size:22px}p{color:#B8C6FF;line-height:1.6}</style></head><body><div class="c">'
        . '<div style="font-size:56px">&#128274;</div><h1>Ujian hanya dapat dibuka lewat aplikasi IKHLAS LOCK (ILOK)</h1>'
        . '<p>Buka aplikasi ILOK di HP Anda. Pastikan tanggal &amp; jam HP diatur otomatis.</p></div></body></html>';
    exit;
})();
