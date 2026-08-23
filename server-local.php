<?php

// Router untuk PHP built-in server di lingkungan LOKAL.
// Project ini memakai struktur deploy flat (front controller index.php di
// root, tanpa public/index.php) agar cocok dengan shared hosting, sehingga
// `php artisan serve` / server.php bawaan tidak bisa dipakai.
//
// Cara pakai:
//   php -S 127.0.0.1:8000 server-local.php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__.str_replace('/', DIRECTORY_SEPARATOR, $path);

if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__.'/index.php';
