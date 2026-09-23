# Konwencje — Pomoc Drogowa Pabianice (design → statyczny PHP)

Statyczne strony PHP z partialami, zero frameworków, vanilla JS. Design: `design/*.dc.html` (Claude Design,
projekt `3973b39a-1059-438e-9708-13af0dd730d3`). Wzorzec architektury: projekt Tuszyn.

## Struktura

```
public_html/
  index.php  oferta.php  galeria.php  kontakt.php  404.php   <- strony
  robots.php  sitemap.php                                     <- generowane wg hosta
  partials/config.php head.php header.php cta.php footer.php <- wspólne
  assets/css/main.css      <- fonty (@font-face), tokeny, baza, hover, RWD (data-*), FAQ, galeria, lightbox, Leaflet
  assets/js/main.js        <- menu mobilne
  assets/js/home.js galeria.js kontakt.js                     <- JS stron (oferta bez JS)
  assets/img/<slug>.jpg + -{480,800,1200,1600}.{avif,webp}, thumbs/, hero/, variants.json, roles.json, logo.png
  assets/fonts/*.woff2, assets/vendor/leaflet/, assets/favicon.svg
  .htaccess  llms.txt
```

## Szkielet strony

```php
<?php
if (!defined('PD_APP')) { define('PD_APP', true); }   // index.php: define() bez warunku (dispatcher require'uje podstrony)
$page = 'oferta'; $title = '... (<= 60 znaków)'; $desc = '...'; $path = '/oferta/';
$pageJs = 'assets/js/oferta.js';   // opcjonalnie
$preloadHero = false;              // opcjonalnie (galeria: brak hero na stronie)
$noCta = true;                     // opcjonalnie (kontakt, 404)
$faqLd = [[pytanie, odpowiedź], …]; // tylko home -> FAQPage
include __DIR__ . '/partials/config.php';
$ogImage = 'assets/img/hero/' . pd_slug('oferta-hero') . '.jpg';   // po config.php (pd_slug)
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/header.php';
?>
  <!-- sekcje: wszystko z designu między nagłówkiem a sekcją CTA -->
<?php include __DIR__ . '/partials/cta.php'; include __DIR__ . '/partials/footer.php';
```

- `config.php`: stałe (`$PHONE`, `$PHONE_HREF`, `$PHONE_SHORT`, `$ADDR1/2`, `$GMAPS_PLACE`, `$GREVIEWS`, `$FB`, `$HQ`),
  `$IS_PROD` (host === `www.pomocdrogowa-pabianice.pl`), `$BASE`, `$canonical`, nagłówek `X-Robots-Tag` gdy nie produkcja,
  `pd_picture($key, $alt, $imgStyle, $sizes='100vw', $eager=false, $extra='')`, `pd_slug($rola)`.
- `head.php`: inline `<script>` dodaje klasę `js` na `<html>` (bez JS: FAQ rozwinięte, wszystkie kafelki galerii widoczne, przycisk „więcej” ukryty); meta, canonical (zawsze produkcja), `noindex` gdy `!$IS_PROD`, preload hero (AVIF srcset) + 2 fontów,
  CSS inline (`readfile main.css`), JSON-LD `AutomotiveBusiness`, `Service`, `BreadcrumbList` (podstrony), `FAQPage` (home).
  Otwiera `<body>` i główny `<div>`.
- `header.php` otwiera `<main>`; `cta.php` zamyka `</main>` i renderuje CTA (chyba że `$noCta`); `footer.php` zamyka dokument
  i ładuje `main.js` + `$pageJs` z `?v=filemtime`.

## Reguły konwersji designu

1. Zakres: sekcje między `<dc-import name="Naglowek">` a sekcją CTA. Nagłówek, CTA, stopka, pływający telefon = partiale.
2. Inline style verbatim. Style zależne od `state.w` i `style-hover`/`style-active` → `main.css`
   (klasy `.hov-brand .hov-lift .hov-lift-flat .hov-big .hov-red .hov-yellow .hov-white .hov-dark .hov-border .hov-text .press`;
   RWD przez `data-*`: `[data-nav] [data-navlinks] [data-navphone] [data-burger] [data-menu] [data-fab] [data-bottomrow] [data-leaflet]`;
   komponenty: `.chip.is-on`, `.svc-grid`, `.svc-row.is-rev`, `.faq-btn.is-open`, `.faq-wrap.is-open`, `.gal-grid`, `.gal-cell.is-big`, `.lb.is-open`).
3. `sc-for` → pętle PHP po tablicach w preambule; `sc-if` → stan początkowy; `{{ dot }}` → `<span class="dot"></span>`.
4. Obrazy: `pd_picture(pd_slug('<rola>'), alt, styleInline, sizes)`; hero: `pd_picture('hero/' . pd_slug('<page>-hero'), …, '100vw', true)`.
   Role: `tools/build_images.py` (`ROLES`) → `assets/img/roles.json`. Nigdy `lh3.googleusercontent.com`.
5. `main.css` ma `picture>img{height:auto}` — atrybuty `width/height` (CLS) nie blokują `aspect-ratio`.
6. Kotwice: `<section id="top">` na każdej stronie (aktywny link w nav → `#top`), `scroll-margin-top:var(--navh)` na sekcjach z `id`.
7. Semantyka: jeden `<h1>`, sekcje `<section>` z `<h2>`, karty `<h3>`, `<article id>` dla usług w ofercie, `<nav aria-label="Okruszki">`,
   `aria-expanded`/`aria-controls` (burger, FAQ), `aria-pressed` (chipy), `role="dialog"` (lightbox).
8. Zero w wyniku: `{{`, `<sc-`, `<x-dc`, `<helmet`, `hint-placeholder`, `support.js`, `fonts.googleapis`, `lh3.googleusercontent`, `dc-import`.
9. Polskie znaki w UTF-8 bez encji. Statyczny HTML poza preambułą.

## Routing i środowiska

- Apache (produkcja): `.htaccess` — `/oferta/` → `oferta.php`, `.php`/bez ukośnika → 301, `robots.txt`/`sitemap.xml` → PHP,
  https+www tylko dla hosta `pomocdrogowa-pabianice.pl`, HSTS warunkowy, cache, nagłówki.
- nginx (staging): `try_files` → `index.php`, który ma dispatcher po `REQUEST_URI` (`/oferta/`, `/robots.txt`, `/sitemap.xml`,
  301 dla `/oferta`; reszta → `404.php`). `/oferta.php` wykonany bezpośrednio przez nginx dostaje 301 z `config.php`.
- Noindex automatyczny: każdy host ≠ produkcja dostaje meta `noindex`, nagłówek `X-Robots-Tag`, `robots.txt: Disallow: /`.

## JS

Vanilla IIFE, `defer`, bez `DOMContentLoaded`. `main.js` menu (`.menu-open` na `[data-nav]` i `body`). `home.js` Leaflet leniwie
(IntersectionObserver na `[data-leaflet]`), chipy `[data-town]`, FAQ `[data-faq]`/`[data-faq-wrap]`. `galeria.js` `[data-more]`,
lightbox `[data-lightbox] [data-lbimg] [data-lbpos] [data-lbprev] [data-lbnext] [data-lbclose]`, kafelki `[data-shot][data-full]`.
`kontakt.js` mapa HQ.

## Metadane

| strona | title | ogImage (rola) |
|---|---|---|
| home | Pomoc drogowa Pabianice 24/7 – laweta i holowanie | home-hero |
| oferta | Oferta: laweta, holowanie, naprawa \| Pomoc Drogowa Pabianice | oferta-hero |
| galeria | Galeria: laweta w akcji \| Pomoc Drogowa Pabianice | galeria-hero (bez preload) |
| kontakt | Kontakt 24/7: 517 574 330 \| Pomoc Drogowa Pabianice | kontakt-hero |

## Definicja ukończenia

`tools/check.sh all` → PASS (lint, routing, noindex wg hosta, ślady DC, istnienie obrazów, JSON-LD, h1/title, brak PHP Warning),
zrzuty 390/1280 zgodne z designem, brak błędów konsoli, staging odpowiada 200 z `noindex`.
