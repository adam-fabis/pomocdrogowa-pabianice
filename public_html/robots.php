<?php
// robots.txt generowany wg hosta: produkcja indeksowana, każdy inny host (staging, podgląd) zablokowany.
define('PD_APP', true);
include __DIR__ . '/partials/config.php';
header('Content-Type: text/plain; charset=utf-8');
if ($IS_PROD) {
    echo "User-agent: *\nAllow: /\nDisallow: /partials/\n\nSitemap: {$BASE}sitemap.xml\n";
} else {
    echo "User-agent: *\nDisallow: /\n";
}
