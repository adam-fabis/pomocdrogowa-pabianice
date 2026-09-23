<?php
// Router podglądu lokalnego: php -S 127.0.0.1:8080 -t public_html tools/router.php
// Emuluje nginx ze stagingu: istniejący plik *.php wykonuje bezpośrednio (location ~ \.php$),
// statyczne pliki serwuje wbudowany serwer, wszystko inne idzie do index.php (try_files -> /index.php).
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = $_SERVER['DOCUMENT_ROOT'];
if (preg_match('#^/partials/#', $uri)) { http_response_code(403); echo 'Forbidden'; return true; }
if ($uri !== '/' && is_file($root . $uri)) {
    if (!preg_match('#\.php$#', $uri)) return false; // statyczny plik: wbudowany serwer
    ob_start('ob_gzhandler'); $_SERVER['SCRIPT_NAME'] = $uri; require $root . $uri; return true;
}
ob_start('ob_gzhandler');
$_SERVER['SCRIPT_NAME'] = '/index.php';
require $root . '/index.php';
return true;
