# Spec: strona www.pomocdrogowa-pabianice.pl (Pomoc Drogowa Łukasz Rogowski)

Data: 2026-09-23. Status: zatwierdzony ustnie (podejście „klon architektury Tuszyna, nowy design”).

## 1. Cel

Statyczna strona firmowa w PHP (bez frameworka, bez WordPressa) dla firmy
Pomoc Drogowa Łukasz Rogowski, Pabianice. Cztery strony: Strona główna, Oferta,
Galeria, Kontakt. Wierne przeniesienie projektu z Claude Design (projekt
`3973b39a-1059-438e-9708-13af0dd730d3`). Optymalizacja pod SEO i szybkość
(cel: Lighthouse 100 na mobile w kategoriach Performance/SEO/Best Practices).
Hosting docelowy: SEOhost (Apache/LiteSpeed, FTPS). Staging: Mikrus (nginx +
PHP-FPM) pod `https://pomocdrogowa-pabianice.byst.re`.

Wzorzec architektury: projekt `/Users/adamfabis/Desktop/Prywatne/tuszyn`
(www.pomocdrogowa-tuszyn.pl). Przenosimy architekturę, procedury i narzędzia,
nie wygląd (Tuszyn jest jasny, Pabianice ciemne).

## 2. Dane firmy (źródło prawdy)

| pole | wartość |
|---|---|
| nazwa | Pomoc Drogowa Łukasz Rogowski |
| telefon | +48 517 574 330 (`tel:+48517574330`) |
| adres | ul. Stanisława Moniuszki 41, 95-200 Pabianice |
| godziny | całą dobę, 7 dni w tygodniu, również w święta |
| e-mail | brak (nie pokazujemy) |
| Google place_id | `ChIJpfx1cpE3GkcRsXM2X8BREYA` |
| opinie Google | 5.0, 82 opinie (link: `https://search.google.com/local/reviews?placeid=ChIJpfx1cpE3GkcRsXM2X8BREYA`) |
| wizytówka | `https://www.google.com/maps/place/?q=place_id:ChIJpfx1cpE3GkcRsXM2X8BREYA` |
| Facebook | `https://www.facebook.com/profile.php?id=61556520203279` |
| obszar | Pabianice, Łódź, Konstantynów Łódzki, Ksawerów, Rzgów, Dobroń, Łask, Zduńska Wola, Lutomiersk; trasy S8, S14, A1, DK71 |
| domena produkcyjna | `https://www.pomocdrogowa-pabianice.pl/` (apex 301 → www) |
| staging | `https://pomocdrogowa-pabianice.byst.re/` |
| repo | `https://github.com/adam-fabis/pomocdrogowa-pabianice` (branch `main`) |

Teksty wszystkich sekcji, FAQ (7 pytań), 3 opinie, 8 usług: verbatim z plików
designu (`design/*.dc.html`, dane w blokach `<script type="text/x-dc">`).

## 3. Struktura repozytorium

```
pabianice/
  public_html/                      <- docroot (jedyne, co idzie na serwer)
    index.php oferta.php galeria.php kontakt.php
    robots.php sitemap.php           <- generowane wg hosta (patrz §6)
    llms.txt
    .htaccess                        <- Apache/LiteSpeed (produkcja)
    partials/config.php head.php header.php cta.php footer.php
    assets/css/main.css              <- fonty, tokeny, RWD, komponenty (inline w head.php)
    assets/js/main.js home.js galeria.js kontakt.js
    assets/img/<slug>.jpg + -{480,800,1200,1600}.{avif,webp}
    assets/img/thumbs/<slug>.jpg + -{400,800}.{avif,webp}
    assets/img/hero/<slug>.jpg + -{960,1440,1920}.{avif,webp}
    assets/img/variants.json
    assets/img/logo.png (+ logo-*.webp/avif jeśli build je wygeneruje)
    assets/fonts/barlow-*.woff2 barlow-condensed-*.woff2
    assets/vendor/leaflet/leaflet.js leaflet.css (+ images/)
    assets/favicon.svg
  design/*.dc.html                   <- źródła designu (7 plików) + uploads/logo
  zrodla/google/<slug>.jpg           <- pobrane zdjęcia z wizytówki (poza public_html, w .gitignore)
  tools/fetch_google_photos.py build_images.py build_gallery.py router.php deploy_staging.sh
  tools/alts.json manifest.json
  .github/workflows/deploy.yml       <- FTPS na SEOhost (aktywny po dodaniu sekretów)
  docs/superpowers/specs/, docs/superpowers/plans/
  CONVENTIONS.md DEPLOY.md README.md .gitignore auto-deploy.md
```

`.gitignore`: `.DS_Store`, `*.zip`, `zrodla/`, `__pycache__/`, `*.pyc`.

## 4. Szkielet strony PHP i partiale

Każda strona:

```php
<?php
define('PD_APP', true);
$page = 'home|oferta|galeria|kontakt';
$title = '...'; $desc = '...';
$path = '/' | '/oferta/' | '/galeria/' | '/kontakt/';   // canonical = $BASE . ltrim($path,'/')
$ogImage = 'assets/img/hero/<slug>.jpg';
$pageJs = 'assets/js/<page>.js';   // brak dla oferty
include __DIR__ . '/partials/config.php';
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/header.php';
?>
  <!-- sekcje z designu: wszystko między nagłówkiem a sekcją CTA -->
<?php
include __DIR__ . '/partials/cta.php';
include __DIR__ . '/partials/footer.php';
```

- `config.php`: strażnik `PD_APP`; `$PROD_HOST = 'www.pomocdrogowa-pabianice.pl'`;
  `$IS_PROD = ($_SERVER['HTTP_HOST'] ?? '') === $PROD_HOST`; `$BASE = 'https://' . $PROD_HOST . '/'`
  (canonical i OG zawsze wskazują na produkcję, także na stagingu); stałe firmy
  (telefon, adres, place_id, Facebook) jako zmienne używane przez partiale i JSON-LD.
- `head.php`: `<head>` z meta, canonical, OG/Twitter, `<meta name="robots" content="noindex, nofollow">`
  gdy `!$IS_PROD`, preload hero (srcset AVIF z `$ogImage`), preload 2 fontów krytycznych
  (Barlow 400 latin, Barlow Condensed 800 latin), CSS inline (`readfile main.css`), JSON-LD (§7).
  Otwiera `<body>` i główny `<div>` (`background:#1f1f1f;font-family:'Barlow'...;color:#f2f2f2`).
- `header.php`: sticky `<header>` z `Naglowek.dc.html` (logo, nav 4 linki z aktywnym
  podkreśleniem, przycisk telefonu z pulsującą kropką, burger + menu mobilne).
  Otwiera `<main>`. Aktywna strona: link do kotwicy `#top`, pozostałe do `/`, `/oferta/`, ….
- `cta.php`: zamyka `</main>`, sekcja „Potrzebujesz pomocy?” (ta sama na wszystkich
  stronach w designie; strona kontakt jej nie ma, więc `kontakt.php` ustawia `$noCta = true`,
  a `cta.php` renderuje wtedy tylko `</main>`).
- `footer.php`: `<footer>` ze `Stopka.dc.html` (logo, kontakt, godziny, nawigacja, pasek
  © + „Projekt i wykonanie: pozycjonujewizytowke.pl”), pływający przycisk telefonu (`data-fab`:
  desktop prawy dół, mobile pełna szerokość dołu), `<script defer src=...?v=filemtime>`.

Reguły konwersji designu = jak `CONVENTIONS.md` z Tuszyna: inline style verbatim,
`sc-for` rozwinięte statycznie, `sc-if` w stanie początkowym, zero `{{ }}`, `sc-`,
`x-dc`, `helmet`, `support.js`, `fonts.googleapis`, `lh3.googleusercontent.com`.
Style responsywne z `renderVals()` (zależne od `state.w`) przenosimy do `main.css`
przez atrybuty `data-*` i media queries (progi z designu: 560, 700, 820, 900, 1000 px).
Stany interaktywne (hover/active z `style-hover`) → reguły CSS na klasach/atrybutach.

## 5. Strony

### 5.1 Strona główna (`index.php`, `Strona Glowna 1b v2.dc.html`)
Sekcje w kolejności: HERO (`#top`, badge, h1, lead, wielki numer, link „Zobacz zakres”,
pasek 4 statystyk: 24/7, 5.0 ★ 82 opinie, Na miejscu, Auto zastępcze) → pasek żółto-czarny →
O FIRMIE (`#o-firmie`) → OBSZAR DZIAŁANIA (`#obszar`, chipy 9 miejscowości + trasy + mapa Leaflet
+ pasek „Wybrana miejscowość”) → ZAKRES POMOCY (`#zakres`, 8 kart z obrazem, numerem, tytułem,
opisem) → AUTO ZASTĘPCZE (żółta sekcja) → OPINIE (`#opinie`, 3 karty) → FAQ (`#faq`, 7 pytań,
akordeon, indeks 0 otwarty) → CTA (partial) → stopka.
Meta: `Pomoc drogowa Pabianice 24/7 – laweta, holowanie | Ł. Rogowski` (≤60 znaków, do
doprecyzowania w planie), description z numerem telefonu.

### 5.2 Oferta (`oferta.php`, `Oferta.dc.html`)
HERO z breadcrumbem, h1 „Oferta pomocy drogowej”, przycisk tel → pasek → „PRZEJDŹ DO”
(8 linków kotwic) → 8 artykułów (`#holowanie #naprawa #akumulator #opony #paliwo #kolizja
#transport #oc`, naprzemiennie obraz lewo/prawo, `scroll-margin-top:100px`) → „Od telefonu do
rozwiązania” (3 kroki) → CTA → stopka. Bez własnego JS.

### 5.3 Galeria (`galeria.php`, `Galeria.dc.html`)
Nagłówek z breadcrumbem i linkiem do wizytówki → pasek → siatka 14 kafelków (4 kolumny
desktop, 2 mobile, kafelki 0 i 7 `span 2`), pierwsze 10 widoczne, pozostałe `hidden` +
przycisk „Zobacz więcej zdjęć (4)” → CTA → stopka → lightbox (prev/next/close, licznik
`n / 14`, Escape, strzałki, klik w tło). Kafelki generuje `build_gallery.py`.

### 5.4 Kontakt (`kontakt.php`, `Kontakt.dc.html`)
HERO z breadcrumbem, h1, wielki numer, „Dyżur teraz” → pasek → 4 karty (telefon, adres,
godziny, Znajdź nas: Google + Facebook) → „Co warto mieć pod ręką” (4 punkty + ostrzeżenie)
obok mapy HQ Leaflet + pasek „Wyznacz trasę” (link Google Maps dir) + chipy obszaru →
stopka (bez CTA, jak w designie).

## 6. Routing i środowiska

Jeden kod działa na Apache (produkcja) i nginx (staging):

- `.htaccess` (Apache): `/oferta/` → `oferta.php` (i analogicznie), `*.php` i wersje bez
  ukośnika → 301 na ładny URL, `robots.txt` → `robots.php`, `sitemap.xml` → `sitemap.php`,
  403 dla `partials/` i plików ukrytych, https+www 301 **tylko gdy**
  `HTTP_HOST` pasuje do `^(www\.)?pomocdrogowa-pabianice\.pl$`, HSTS, cache, kompresja,
  nagłówki bezpieczeństwa, `CacheLookup off` dla LiteSpeed, MIME avif/webp.
- `index.php` (nginx fallback `try_files $uri $uri/ /index.php?$args`): na początku
  dispatcher po `parse_url(REQUEST_URI, PHP_URL_PATH)`:
  `/oferta/`, `/galeria/`, `/kontakt/` → `require` odpowiedniego pliku i `return`;
  `/robots.txt` → `robots.php`; `/sitemap.xml` → `sitemap.php`; `/oferta`, `/oferta.php`,
  `/index.php` → 301 na ładny URL; inne nieznane ścieżki → 404 (`http_response_code(404)`
  + krótka strona 404 z nawigacją); `/` → strona główna.
- `robots.php`: `Content-Type: text/plain`. Produkcja: `Allow: /`, `Disallow: /partials/`,
  `Sitemap: https://www.pomocdrogowa-pabianice.pl/sitemap.xml`. Inne hosty: `Disallow: /`.
- `sitemap.php`: `Content-Type: application/xml`, 4 adresy z `$BASE`, `lastmod` = max
  `filemtime` plików stron.
- Staging: dodatkowo nagłówek `X-Robots-Tag: noindex, nofollow` wysyłany z `config.php`
  gdy `!$IS_PROD` (działa na obu serwerach, bez edycji vhosta).
- `tools/router.php`: emulacja dla `php -S 127.0.0.1:8080 -t public_html tools/router.php`
  (ładne URL-e, robots, sitemap, statyczne pliki, gzip).

## 7. SEO

- `$title` ≤ 60 znaków, unikalne `description` z telefonem, canonical na produkcję,
  OG (`og:image` = hero 1920×1440 JPEG), Twitter card, `lang="pl"`, `og:locale pl_PL`.
- JSON-LD w `head.php`: `AutomotiveBusiness` (`@id #firma`: name, url, telephone, address,
  geo (współrzędne Moniuszki 41 z geokodowania w planie), `hasMap`, `openingHoursSpecification`
  24/7, `areaServed` (9 miast + `województwo łódzkie`), `image` (4 hero), `sameAs` Facebook +
  wizytówka Google, `priceRange`), `Service` (`@id #obszar-dzialania`, provider → `#firma`,
  `serviceType` 8 usług + laweta/autoholowanie), `BreadcrumbList` na podstronach, `FAQPage`
  na stronie głównej (7 par z sekcji FAQ, teksty identyczne z HTML). Bez `aggregateRating`.
- Semantyka: `<header><nav>`, `<main>`, `<section id>` z `<h2>`, jeden `<h1>` na stronę,
  `<article>` dla usług w ofercie, `<footer>`, `aria-label` na przyciskach ikonowych,
  `aria-expanded` na burgerze i FAQ.
- `llms.txt` w stylu Tuszyna. `sitemap.xml` i `robots.txt` dynamiczne (§6).

## 8. Wydajność

- CSS inline w `<head>` (`main.css` ≈ 10–14 KB), bez zewnętrznych arkuszy.
- Fonty self-hosted: Barlow 400/500/600/700 i Barlow Condensed 600/700/800, subsety
  latin + latin-ext (pobrane z Google Fonts CSS API jako woff2, `unicode-range`),
  `font-display: swap`, preload dwóch plików krytycznych.
- Obrazy: `<picture style="display:contents">` z `<source avif>`, `<source webp>`, `<img>`
  JPEG + `width/height` + `loading="lazy" decoding="async"` (hero: `fetchpriority="high"`,
  bez lazy, preload srcset w head). Hero jako `<img>` absolutnie pozycjonowany, nie
  `background-image`. Kafelki galerii z `thumbs/`, pełny plik w `data-full`.
- JS: `defer`, `?v=filemtime`, vanilla IIFE, bez `DOMContentLoaded`. Leaflet wstrzykiwany
  leniwie przez `IntersectionObserver` na `[data-leaflet]` (`home.js`, `kontakt.js`).
- `.htaccess`: deflate, `Expires`/`Cache-Control` (JS/CSS immutable rok, obrazy 30 dni,
  fonty rok, PHP `no-cache, must-revalidate`).

## 9. Zdjęcia

- `tools/fetch_google_photos.py`: lista 14 ID z `Galeria.dc.html` → pobiera
  `https://lh3.googleusercontent.com/p/<id>=s0` (fallback `=s1600`) do `zrodla/google/<slug>.jpg`.
  Slugi SEO po polsku i alt-y (`tools/alts.json`) opisane ręcznie po obejrzeniu zdjęć,
  z frazą „pomoc drogowa Pabianice” / „laweta Pabianice”, bez duplikatów.
- `tools/build_images.py` (port z Tuszyna, źródło `zrodla/google/`): `ROLES` przypisuje
  zdjęcia do miejsc (home-hero, home-about, home-oc, cta-bg, sit-1..8 karty zakresu,
  oferta-hero, oferta-1..8, kontakt-hero); wszystkie 14 trafiają też do galerii (mało zdjęć,
  design tak robi). Generuje JPEG + AVIF/WebP, `thumbs/`, `hero/`, `variants.json`,
  `manifest.json`. `build_gallery.py` przepisuje kafelki `[data-shot]` w `galeria.php`.
- Logo: `design/uploads/unnamed 1.png` → `assets/img/logo.png` (56 px w nagłówku, 96 px
  w stopce; `width/height` w HTML), ewentualnie 2× WebP.
- Mapowanie ID → miejsce wg designu (np. `AF1QipMCuRG…` = home hero, `AF1QipMv1T…` =
  o firmie + kontakt hero, `AF1QipOws8…` = tło CTA, `AF1QipMit_…` = auto zastępcze,
  `AF1QipMzHp…` = oferta hero + holowanie) — pełna tabela w planie.

## 10. Mapy (Leaflet 1.9.4 lokalnie, OSM)

- Home `home.js`: mapa obszaru, centrum Pabianice, piny `divIcon` dla 9 miejscowości
  (współrzędne zaszyte w JS), pin bazy wyróżniony, klik na pin lub chip wybiera miasto,
  `panTo`, aktualizacja chipów (aktywny `#f5c518/#111`, nieaktywny transparent/`#eee`)
  i tekstu „Wybrana miejscowość: <b>…</b>”. Okrąg zasięgu ok. 25 km wokół Pabianic.
- Kontakt `kontakt.js`: `setView` na Moniuszki 41 (zoom 15), marker `pd-hq` z popupem,
  okrąg 600 m, `invalidateSize` po 250 ms.
- Kafelki `https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png`, attribution `© OpenStreetMap`.
  Ciemny wygląd: CSS `filter: grayscale(1) invert(.92) contrast(1.05) brightness(.9)` na
  `.leaflet-tile-pane` (weryfikacja wizualna w planie; jeśli nieczytelne, sam grayscale).

## 11. JS stron

- `main.js`: burger/menu mobilne (`.menu-open`, `aria-expanded`, Escape, zamykanie po kliku
  linku, blokada scrolla body), zamykanie przy `matchMedia('(min-width:900px)')`.
- `home.js`: mapa (§10) + akordeon FAQ (jeden otwarty, ponowny klik zamyka; animacja
  `grid-template-rows 0fr/1fr` jak w designie).
- `galeria.js`: „Zobacz więcej” (odkrywa ukryte kafelki, chowa przycisk) + lightbox
  (indeks z `data-shot`, `data-full`, `alt`, licznik, prev/next cyklicznie, Escape/strzałki,
  klik w tło, `body.lb-open` chowa pływający przycisk).
- `kontakt.js`: mapa HQ.

## 12. Deploy

### Staging (Mikrus, runbook `auto-deploy.md` zaadaptowany do statycznego PHP)
`tools/deploy_staging.sh` (idempotentny, uruchamiany na życzenie „deploy”):
1. `remove patecwariatec --backup` (zwalnia port 40461; jednorazowo, tylko gdy `exists`).
   Użytkownik ręcznie usuwa wpis `patecwariatec.byst.re` z panelu Mikrusa (API nie ma
   endpointu).
2. `create pomocdrogowa-pabianice <port z port-free> adamfabis96@gmail.com pomocdrogowa-pabianice.byst.re`
   tylko gdy slug nie istnieje (helper tworzy nieużywaną bazę — akceptowalne).
3. `tar czf` z `public_html/` (bez `.DS_Store`), `scp`, `ssh tar xzf -C /srv/sites/pomocdrogowa-pabianice`,
   `chown www-data`, uprawnienia 755/644. Bez `unpack-files` (wołałby `wp config set`) i bez `import-db`.
4. Weryfikacja: `curl -sI https://pomocdrogowa-pabianice.byst.re/` → 200 + `X-Robots-Tag: noindex`;
   `/oferta/` → 200; `/robots.txt` → `Disallow: /`.

### Produkcja (SEOhost)
`.github/workflows/deploy.yml` jak w Tuszynie (FTPS, `security: loose`, 3 próby, check 200
na `https://www.pomocdrogowa-pabianice.pl/`). Aktywny dopiero po dodaniu sekretów
`FTP_SERVER/FTP_USERNAME/FTP_PASSWORD` — użytkownik dostarczy dostęp FTP później.
`DEPLOY.md`: checklista (sekrety, DNS, SSL, weryfikacja robots/sitemap/JSON-LD, LiteSpeed
purge). Bez ręcznego przełączania noindex — decyduje host.

## 13. Definicja ukończenia

- [ ] `php -l` na wszystkich `.php` przechodzi.
- [ ] `grep -rE '\{\{|<sc-|x-dc|helmet|support\.js|fonts\.googleapis|lh3\.googleusercontent' public_html` → 0 trafień.
- [ ] Każdy `src`/`srcset`/`data-full` wskazuje na istniejący plik w `assets/`.
- [ ] Wszystkie sekcje designu obecne w tej samej kolejności, teksty PL verbatim, wizualnie
  zgodne z designem na 390 px, 820 px, 1280 px (zrzuty z podglądu lokalnego).
- [ ] `php -S` + `router.php`: `/`, `/oferta/`, `/galeria/`, `/kontakt/`, `/robots.txt`,
  `/sitemap.xml` → 200; `/oferta.php`, `/oferta` → 301; `/x` → 404.
- [ ] Lighthouse mobile (lokalnie, `--preset=desktop` i mobile) ≥ 95 Performance, 100 SEO,
  100 Best Practices, ≥ 95 Accessibility.
- [ ] Rich Results Test / walidator schema.org bez błędów dla 4 stron (JSON-LD wyciągnięty lokalnie).
- [ ] Staging odpowiada 200 z `noindex`; produkcyjny host w `config.php` daje `index`.
- [ ] Repo zainicjowane, `.gitignore`, commit, push na `origin main`.
- [ ] `CONVENTIONS.md`, `DEPLOY.md`, `README.md`, `tools/README.md` opisują ten projekt (nie Tuszyn).

## 14. Poza zakresem

Formularz kontaktowy, e-mail, blog, wielojęzyczność, cookie banner (brak cookies:
Leaflet/OSM nie ustawia, brak analityki), migracja starych adresów (brak starej strony
pod tą domeną — do potwierdzenia przy wdrożeniu), deploy produkcyjny (do czasu FTP).
