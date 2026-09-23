<?php
// Router podglądu lokalnego: php -S 127.0.0.1:8080 -t public_html tools/router.php
// Statyczne pliki serwuje wbudowany serwer; wszystko inne idzie do index.php (jak nginx try_files).
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = $_SERVER['DOCUMENT_ROOT'];
if (preg_match('#^/partials/#', $uri)) { http_response_code(403); echo 'Forbidden'; return true; }
if ($uri !== '/' && is_file($root . $uri) && !preg_match('#\.php$#', $uri)) return false;
$_SERVER['SCRIPT_NAME'] = '/index.php';
ob_start('ob_gzhandler');
require $root . '/index.php';
return true;
