<?php
$request = $_SERVER['REQUEST_URI'];
// Decode %20 and other percent-encoded chars so paths with spaces (e.g. "Bride Photoshoot") resolve correctly
$path = rawurldecode(parse_url($request, PHP_URL_PATH));
$file = __DIR__ . $path;

if (is_file($file)) {
    // Serve static files with correct Content-Type
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $types = ['webp'=>'image/webp','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','svg'=>'image/svg+xml','mp4'=>'video/mp4','css'=>'text/css','js'=>'application/javascript','woff2'=>'font/woff2','woff'=>'font/woff','ttf'=>'font/ttf','ico'=>'image/x-icon'];
    if (isset($types[$ext])) header('Content-Type: ' . $types[$ext]);
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
