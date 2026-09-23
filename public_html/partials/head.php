<?php
if (!defined('PD_APP')) { http_response_code(403); exit; }
/**
 * <head> + otwarcie <body>. Wymaga (ustawione przez stronę przed include config.php):
 *   $page      home|oferta|galeria|kontakt|404
 *   $title     tytuł (<= 60 znaków)
 *   $desc      meta description
 *   $path      '/', '/oferta/', ...  (canonical = $BASE . path)
 *   $ogImage   'assets/img/hero/<slug>.jpg'
 * Opcjonalnie: $preloadHero (bool, domyślnie true), $faqLd (tablica par [pytanie, odpowiedź] -> FAQPage).
 */
$heroSlug = pathinfo($ogImage, PATHINFO_FILENAME);
$heroV = $PD_VARIANTS['hero/' . $heroSlug];
$heroSrcset = implode(', ', array_map(fn($w) => "/assets/img/hero/{$heroSlug}-{$w}.avif {$w}w", $heroV['widths']));
$areaServed = array_map(fn($n) => ['@type' => 'City', 'name' => $n],
    ['Pabianice', 'Łódź', 'Konstantynów Łódzki', 'Ksawerów', 'Rzgów', 'Dobroń', 'Łask', 'Zduńska Wola', 'Lutomiersk']);
$areaServed[] = ['@type' => 'AdministrativeArea', 'name' => 'województwo łódzkie'];
$hours = ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'], 'opens' => '00:00', 'closes' => '23:59'];
$heroImages = array_values(array_map(fn($k) => $BASE . 'assets/img/hero/' . basename($k) . '.jpg',
    array_filter(array_keys($PD_VARIANTS), fn($k) => str_starts_with($k, 'hero/'))));
$ld = [];
$ld[] = [
    '@context' => 'https://schema.org', '@type' => 'AutomotiveBusiness', '@id' => $BASE . '#firma',
    'name' => $SITE_NAME, 'url' => $BASE, 'telephone' => '+48517574330',
    'image' => $heroImages,
    'logo' => $BASE . 'assets/img/logo.png',
    'description' => 'Całodobowa pomoc drogowa w Pabianicach i okolicach: holowanie i laweta, naprawa na miejscu, awaryjne odpalanie, wymiana koła, dowóz paliwa, pomoc po kolizji, transport pojazdów, auto zastępcze z OC sprawcy.',
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => $ADDR1, 'postalCode' => '95-200', 'addressLocality' => 'Pabianice', 'addressRegion' => 'łódzkie', 'addressCountry' => 'PL'],
    'geo' => ['@type' => 'GeoCoordinates', 'latitude' => $HQ[0], 'longitude' => $HQ[1]],
    'hasMap' => $GMAPS_PLACE, 'openingHoursSpecification' => $hours,
    'contactPoint' => ['@type' => 'ContactPoint', 'telephone' => '+48517574330', 'contactType' => 'customer service', 'availableLanguage' => 'pl', 'areaServed' => 'PL'],
    'areaServed' => $areaServed, 'priceRange' => '$$', 'sameAs' => [$FB, $GMAPS_PLACE],
];
$ld[] = [
    '@context' => 'https://schema.org', '@type' => 'Service', '@id' => $BASE . '#obszar-dzialania',
    'name' => 'Całodobowa pomoc drogowa', 'url' => $BASE . 'oferta/',
    'description' => 'Pomoc drogowa 24/7: holowanie i laweta, naprawa na miejscu, awaryjne odpalanie, wymiana koła i serwis opon, dowóz paliwa, pomoc po kolizji i wypadku, transport pojazdów, auto zastępcze z OC sprawcy — Pabianice, Łódź i okolice, trasy S8, S14, A1.',
    'provider' => ['@id' => $BASE . '#firma'], 'areaServed' => $areaServed,
    'serviceType' => ['Pomoc drogowa', 'Holowanie', 'Laweta', 'Autoholowanie', 'Naprawa na miejscu', 'Awaryjne odpalanie samochodu', 'Wymiana koła', 'Dowóz paliwa', 'Pomoc po kolizji', 'Transport pojazdów', 'Auto zastępcze z OC sprawcy'],
    'hoursAvailable' => $hours,
    'availableChannel' => ['@type' => 'ServiceChannel', 'servicePhone' => ['@type' => 'ContactPoint', 'telephone' => '+48517574330', 'contactType' => 'Pomoc drogowa']],
];
if ($page !== 'home' && $page !== '404') {
    $bn = ['oferta' => 'Oferta', 'galeria' => 'Galeria', 'kontakt' => 'Kontakt'];
    $ld[] = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Strona główna', 'item' => $BASE],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $bn[$page] ?? 'Strona', 'item' => $canonical]]];
}
if (!empty($faqLd)) {
    $ld[] = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn($f) => [
        '@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqLd)];
}
$e = fn($s) => htmlspecialchars($s, ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<script>document.documentElement.classList.add('js')</script>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $e($title); ?></title>
<?php if (!$IS_PROD): ?>
<meta name="robots" content="noindex, nofollow">
<?php endif; ?>
<meta name="description" content="<?php echo $e($desc); ?>">
<link rel="canonical" href="<?php echo $e($canonical); ?>">
<?php if ($preloadHero): ?>
<link rel="preload" as="image" type="image/avif" fetchpriority="high" imagesrcset="<?php echo $e($heroSrcset); ?>" imagesizes="100vw">
<?php endif; ?>
<link rel="preload" href="/assets/fonts/barlow-condensed-800-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/barlow-400-latin.woff2" as="font" type="font/woff2" crossorigin>
<meta property="og:type" content="website">
<meta property="og:locale" content="pl_PL">
<meta property="og:site_name" content="<?php echo $e($SITE_NAME); ?>">
<meta property="og:title" content="<?php echo $e($title); ?>">
<meta property="og:description" content="<?php echo $e($desc); ?>">
<meta property="og:url" content="<?php echo $e($canonical); ?>">
<meta property="og:image" content="<?php echo $e($BASE . $ogImage); ?>">
<meta property="og:image:width" content="<?php echo $heroV['w']; ?>">
<meta property="og:image:height" content="<?php echo $heroV['h']; ?>">
<meta property="og:image:alt" content="<?php echo $e($title); ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo $e($title); ?>">
<meta name="twitter:description" content="<?php echo $e($desc); ?>">
<meta name="twitter:image" content="<?php echo $e($BASE . $ogImage); ?>">
<meta name="theme-color" content="#111111">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
<style><?php readfile(__DIR__ . '/../assets/css/main.css'); ?></style>
<?php foreach ($ld as $block): ?>
<script type="application/ld+json"><?php echo json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<?php endforeach; ?>
</head>
<body>
<div style="min-height:100vh;background:#1f1f1f;overflow-x:clip">
