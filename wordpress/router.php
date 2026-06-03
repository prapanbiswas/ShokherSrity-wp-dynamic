<?php
$request = $_SERVER['REQUEST_URI'];
$file = __DIR__ . parse_url($request, PHP_URL_PATH);

if (is_file($file)) {
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
