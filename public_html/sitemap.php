<?php
// sitemap.xml generowany dynamicznie: adresy produkcyjne, lastmod z mtime plików stron.
define('PD_APP', true);
include __DIR__ . '/partials/config.php';
header('Content-Type: application/xml; charset=utf-8');
$pages = ['' => ['index.php', '1.0'], 'oferta/' => ['oferta.php', '0.8'], 'galeria/' => ['galeria.php', '0.6'], 'kontakt/' => ['kontakt.php', '0.8']];
$headMtime = filemtime(__DIR__ . '/partials/head.php');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($pages as $p => [$file, $prio]) {
    $mod = date('Y-m-d', max(file_exists(__DIR__ . '/' . $file) ? filemtime(__DIR__ . '/' . $file) : 0, $headMtime));
    echo "  <url><loc>{$BASE}{$p}</loc><lastmod>{$mod}</lastmod><priority>{$prio}</priority></url>\n";
}
echo "</urlset>\n";
