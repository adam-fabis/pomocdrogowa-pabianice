<?php
define('PD_APP', true); // strażnik: partials/*.php działają tylko przez include
// Dispatcher: na nginx (staging) wszystkie nieistniejące ścieżki trafiają tu przez try_files;
// na Apache .htaccess mapuje ładne URL-e bezpośrednio, więc ten blok widzi tylko "/".
$reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if ($reqPath !== '/' && $reqPath !== '/index.php') {
    if (preg_match('#^/(oferta|galeria|kontakt)/$#', $reqPath, $m)) { require __DIR__ . "/{$m[1]}.php"; return; }
    if ($reqPath === '/robots.txt') { require __DIR__ . '/robots.php'; return; }
    if ($reqPath === '/sitemap.xml') { require __DIR__ . '/sitemap.php'; return; }
    if (preg_match('#^/(oferta|galeria|kontakt)(\.php)?$#', $reqPath, $m)) { header("Location: /{$m[1]}/", true, 301); return; }
    require __DIR__ . '/404.php'; return;
}
if ($reqPath === '/index.php') { header('Location: /', true, 301); return; }

$page = 'home';
$title = 'Pomoc drogowa Pabianice 24/7 – laweta i holowanie';
$desc = 'Całodobowa pomoc drogowa Pabianice i okolice: holowanie, laweta, naprawa na miejscu, odpalanie auta, wymiana koła, dowóz paliwa, auto zastępcze z OC sprawcy. Tel. +48 517 574 330.';
$path = '/';
$pageJs = 'assets/js/home.js';
include __DIR__ . '/partials/config.php';
$ogImage = 'assets/img/hero/' . pd_slug('home-hero') . '.jpg';
include __DIR__ . '/partials/head.php';
// TODO-TASK5: header, sekcje, cta, footer
echo '<main><h1>Strona główna — w budowie (Task 5)</h1></main></div></body></html>';
