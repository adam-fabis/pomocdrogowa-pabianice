<?php
// robots.txt generowany wg hosta: produkcja indeksowana, każdy inny host (staging, podgląd) zablokowany.
if (!defined('PD_APP')) { define('PD_APP', true); } // bezpośrednio (Apache) lub przez dispatcher w index.php (nginx)
include __DIR__ . '/partials/config.php';
header('Content-Type: text/plain; charset=utf-8');
if ($IS_PROD) {
    echo "User-agent: *\nAllow: /\nDisallow: /partials/\n\nSitemap: {$BASE}sitemap.xml\n";
} else {
    echo "User-agent: *\nDisallow: /\n";
}
