<?php
$request = $_SERVER['REQUEST_URI'];
// Decode %20 and other percent-encoded chars so paths with spaces resolve correctly
$path = rawurldecode(parse_url($request, PHP_URL_PATH));
$file = __DIR__ . $path;

// ── Security headers (every response) ────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (is_file($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    $types = [
        'webp'  => 'image/webp',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'png'   => 'image/png',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'mp4'   => 'video/mp4',
        'webm'  => 'video/webm',
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'woff2' => 'font/woff2',
        'woff'  => 'font/woff',
        'ttf'   => 'font/ttf',
        'ico'   => 'image/x-icon',
        'json'  => 'application/json',
        'xml'   => 'application/xml',
    ];
    if (isset($types[$ext])) header('Content-Type: ' . $types[$ext]);

    // ── Cache-Control by asset type ───────────────────────────
    $immutable = ['webp', 'jpg', 'jpeg', 'png', 'gif', 'ico', 'woff2', 'woff', 'ttf'];
    $long_live  = ['css', 'js', 'svg'];
    $videos     = ['mp4', 'webm'];

    if (in_array($ext, $immutable)) {
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Vary: Accept-Encoding, Accept');
    } elseif (in_array($ext, $long_live)) {
        header('Cache-Control: public, max-age=604800');
        header('Vary: Accept-Encoding');
    } elseif (in_array($ext, $videos)) {
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Accept-Ranges: bytes');
    } else {
        header('Cache-Control: no-cache, must-revalidate');
    }

    // ── On-the-fly WebP upgrade ───────────────────────────────
    // If browser supports WebP and a .webp copy exists, serve it transparently
    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($accept, 'image/webp') !== false) {
            $webp_file = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file);
            if (file_exists($webp_file)) {
                header('Content-Type: image/webp');
                header('Vary: Accept');
                header('Cache-Control: public, max-age=31536000, immutable');
                readfile($webp_file);
                return true;
            }
        }
    }

    return false;
}

if (is_dir($file)) {
    $index = rtrim($file, '/') . '/index.php';
    if (is_file($index)) {
        $_SERVER['SCRIPT_FILENAME'] = $index;
        require $index;
        return true;
    }
}

$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';
require __DIR__ . '/index.php';
