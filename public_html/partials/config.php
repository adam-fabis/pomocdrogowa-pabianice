<?php
if (!defined('PD_APP')) { http_response_code(403); exit; } // tylko przez include ze strony
/**
 * Stałe projektu + środowisko. Include jako pierwszy partial w każdej stronie.
 * Środowisko rozpoznawane po hoście: produkcja = $PROD_HOST, wszystko inne (staging, podgląd) = noindex.
 */
$PROD_HOST = 'www.pomocdrogowa-pabianice.pl';
$IS_PROD = (($_SERVER['HTTP_HOST'] ?? '') === $PROD_HOST) || getenv('PD_FORCE_PROD') === '1'; // PD_FORCE_PROD=1 tylko do lokalnego Lighthouse (php -S)
$BASE = 'https://' . $PROD_HOST . '/';
$SITE_NAME = 'Pomoc Drogowa Łukasz Rogowski';
$PHONE_HREF = 'tel:+48517574330';
$PHONE = '+48 517 574 330';
$PHONE_SHORT = '517 574 330';
$ADDR1 = 'ul. Stanisława Moniuszki 41';
$ADDR2 = '95-200 Pabianice';
$PLACE_ID = 'ChIJpfx1cpE3GkcRsXM2X8BREYA';
$GMAPS_PLACE = 'https://www.google.com/maps/place/?q=place_id:' . $PLACE_ID;
$GREVIEWS = 'https://search.google.com/local/reviews?placeid=' . $PLACE_ID;
$FB = 'https://www.facebook.com/profile.php?id=61556520203279';
$HQ = [51.6607527, 19.3441975];
$path = $path ?? '/';
$canonical = $BASE . ltrim($path, '/');
$preloadHero = $preloadHero ?? true;
// nginx (staging) wykonuje /oferta.php bezpośrednio — przekieruj na ładny URL (na Apache robi to .htaccess; po rewrite REQUEST_URI = /oferta/)
$pdReq = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (isset($page) && in_array($page, ['home', 'oferta', 'galeria', 'kontakt'], true) && preg_match('#\.php$#', $pdReq) && !headers_sent()) {
    header('Location: ' . $path, true, 301); exit;
}
if (!$IS_PROD && !headers_sent()) { header('X-Robots-Tag: noindex, nofollow'); }
if (!headers_sent()) { header('X-LiteSpeed-Purge: *'); } // LiteSpeed (SEOhost): nie podawać starej wersji z cache

$PD_VARIANTS = json_decode(file_get_contents(__DIR__ . '/../assets/img/variants.json'), true);

/**
 * <picture> z AVIF/WebP/JPEG na podstawie variants.json.
 * $key = 'slug' | 'thumbs/slug' | 'hero/slug'. $eager = hero (fetchpriority=high, bez lazy).
 */
function pd_picture(string $key, string $alt, string $imgStyle, string $sizes = '100vw', bool $eager = false, string $extra = ''): string {
    global $PD_VARIANTS;
    $v = $PD_VARIANTS[$key] ?? null;
    if (!$v) { throw new RuntimeException("Brak wariantu obrazu: $key"); }
    $dir = dirname($key) === '.' ? '' : dirname($key) . '/';
    $slug = basename($key);
    $set = function (string $ext) use ($dir, $slug, $v): string {
        return implode(', ', array_map(fn($w) => "/assets/img/{$dir}{$slug}-{$w}.{$ext} {$w}w", $v['widths']));
    };
    $load = $eager ? 'fetchpriority="high"' : 'loading="lazy"';
    $altAttr = htmlspecialchars($alt, ENT_QUOTES);
    return '<picture style="display:contents">'
        . '<source type="image/avif" srcset="' . $set('avif') . '" sizes="' . $sizes . '">'
        . '<source type="image/webp" srcset="' . $set('webp') . '" sizes="' . $sizes . '">'
        . "<img src=\"/assets/img/{$dir}{$slug}.jpg\" alt=\"{$altAttr}\" width=\"{$v['w']}\" height=\"{$v['h']}\" {$load} decoding=\"async\" style=\"{$imgStyle}\"{$extra}>"
        . '</picture>';
}

/** Role, których zdjęcia klient jeszcze nie przysłał (nie ma ich w roles.json) — pd_role_picture() pokazuje zaślepkę. */
$PD_PENDING = ['home-svc-7', 'oferta-transport']; // Transport pojazdów: czeka na zdjęcie T6 przy czerwonym budynku

/** Zdjęcie roli albo zaślepka „Zdjęcie wkrótce” w tym samym wymiarze ($imgStyle: aspect-ratio, ramka). */
function pd_role_picture(string $role, string $alt, string $imgStyle, string $sizes): string {
    global $PD_PENDING;
    if (!in_array($role, $PD_PENDING, true)) { return pd_picture(pd_slug($role), $alt, $imgStyle, $sizes); }
    return '<div role="img" aria-label="' . htmlspecialchars("$alt — zdjęcie wkrótce", ENT_QUOTES) . '" style="' . $imgStyle
        . ';display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;box-sizing:border-box;color:#f5c518;'
        . 'background:repeating-linear-gradient(135deg,#1d1d1d 0 18px,#242424 18px 36px)">'
        . '<svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square" aria-hidden="true">'
        . '<path d="M3 7h4l2-3h6l2 3h4v13H3z"/><circle cx="12" cy="13" r="4"/></svg>'
        . '<span style="font-size:13px;letter-spacing:3px;font-weight:700">ZDJĘCIE WKRÓTCE</span></div>';
}

/** Slug zdjęcia o danej roli (assets/img/roles.json, generowany przez tools/build_images.py). */
function pd_slug(string $role): string {
    static $roles = null;
    if ($roles === null) { $roles = json_decode(file_get_contents(__DIR__ . '/../assets/img/roles.json'), true); }
    if (!isset($roles[$role])) { throw new RuntimeException("Brak zdjęcia o roli: $role"); }
    return $roles[$role];
}
