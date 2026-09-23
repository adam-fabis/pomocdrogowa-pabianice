# Pomoc Drogowa Pabianice — plan implementacji

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Zbudować 4-stronicową statyczną stronę PHP (dark design z Claude Design) dla Pomocy Drogowej Łukasz Rogowski, zoptymalizowaną pod SEO/Lighthouse, działającą identycznie na Apache (SEOhost) i nginx (staging Mikrus), i wgrać ją na staging `https://pomoc-pabianice.byst.re/`.

**Architecture:** Strony `public_html/*.php` składają się z partiali (`config/head/header/cta/footer`), sekcje przeniesione 1:1 z plików `design/*.dc.html` (inline style verbatim, RWD i hover w `main.css` przez atrybuty `data-*` i klasy). `index.php` zawiera dispatcher po `REQUEST_URI` (fallback dla nginx), `.htaccess` obsługuje Apache. Noindex/robots/sitemap decydowane po hoście (`config.php`). Obrazy z wizytówki Google przetwarzane skryptami Python do JPEG+AVIF/WebP.

**Tech Stack:** PHP 8.3 (bez frameworka), vanilla JS (IIFE), CSS inline, Leaflet 1.9.4 (lokalnie), Python 3 + Pillow (AVIF), bash + ssh/scp (staging), GitHub Actions FTPS (produkcja, później).

**Spec:** `docs/superpowers/specs/2026-09-23-pomocdrogowa-pabianice-design.md`

## Global Constraints

- Docroot = `public_html/`; na serwer idzie tylko ten katalog.
- Produkcyjny host: `www.pomocdrogowa-pabianice.pl`; `$BASE = 'https://www.pomocdrogowa-pabianice.pl/'`; staging `https://pomoc-pabianice.byst.re/`.
- Telefon: `+48 517 574 330`, `href="tel:+48517574330"`. Adres: `ul. Stanisława Moniuszki 41, 95-200 Pabianice`. Place ID `ChIJpfx1cpE3GkcRsXM2X8BREYA`. Facebook `https://www.facebook.com/profile.php?id=61556520203279`.
- Kolory designu: tło `#1f1f1f`, sekcje `#262626`/`#2a2a2a`/`#141414`/`#161616`/`#111`, akcent `#f5c518`, czerwień `#e8342a`, hover czerwieni `#ff4336`, zielona kropka `#3ad36b`, tekst `#f2f2f2`.
- Fonty: `'Barlow'` 400/500/600/700, `'Barlow Condensed'` 600/700/800, self-hosted woff2 (latin + latin-ext), `font-display: swap`.
- Inline style z designu przenosimy **verbatim**. Style zależne od szerokości okna i `style-hover` → `main.css`.
- Zero w wyniku: `{{`, `}}`, `<sc-`, `<x-dc`, `<helmet`, `hint-placeholder`, `support.js`, `fonts.googleapis`, `lh3.googleusercontent.com`, `dc-import`.
- Polskie znaki w UTF-8 bez encji. Strony PHP: czysty HTML poza preambułą `<?php ... ?>`.
- Każdy obraz: `<picture style="display:contents">` + `<source avif>` + `<source webp>` + `<img>` JPEG z `width`/`height`, `loading="lazy" decoding="async"`; hero: `fetchpriority="high"` bez lazy.
- Skrypty: `<script defer src="/assets/js/X.js?v=<filemtime>">`, bez `DOMContentLoaded`.
- `$title` ≤ 60 znaków. Jeden `<h1>` na stronę. `<section id>` z `<h2>`.
- Commity po każdym tasku, wiadomości po polsku lub angielsku, z linią `Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>`.
- Podgląd lokalny: `php -S 127.0.0.1:8080 -t public_html tools/router.php` (uruchamiaj w tle, zabijaj po testach).

## Review Focus

1. **Host inny niż produkcja i staging** (np. `127.0.0.1:8080`, tymczasowy adres SEOhost): strona ma działać, ale z `noindex` i `robots.txt: Disallow: /`; canonical zawsze na produkcję. Test w Task 3 (curl z nagłówkiem `Host:`).
2. **Ścieżki z `.php` i bez ukośnika oraz nieznane ścieżki na nginx**: `/oferta.php`, `/oferta` → 301 na `/oferta/`; `/cokolwiek` → 404 z nawigacją, nie strona główna z kodem 200 (soft 404 dla Google). Test w Task 3 (router emuluje nginx-owy fallback do `index.php`).
3. **Brak JS lub Leaflet nie załadowany** (blokada skryptów, wolna sieć): menu mobilne niedostępne, ale wszystkie linki, telefon i treść widoczne; kontener mapy ma wysokość i tło, brak błędów w konsoli. Test w Task 5 i 8 (`node`-owy lint `node --check` + ręczna weryfikacja z wyłączonym JS w podglądzie).
4. **Klawiatura i czytniki**: burger `aria-expanded`, FAQ przyciski `aria-expanded` + `aria-controls`, lightbox zamykany Escape, focus wraca na kafelek. Test w Task 4/5/7.
5. **Zdjęcie z Google niedostępne** (`=s0` zwraca 4xx): skrypt pobierania ma fallback `=s1600` i przerywa z czytelnym błędem, nie zostawia pustych plików. Test w Task 2.

---

## Mapa plików

| plik | odpowiedzialność |
|---|---|
| `design/*.dc.html`, `design/uploads/logo.png` | źródła designu (już zapisane przez planującego; Task 1 tylko weryfikuje) |
| `public_html/partials/config.php` | stałe firmy, `$IS_PROD`, `$BASE`, nagłówki noindex, strażnik `PD_APP` |
| `public_html/partials/head.php` | `<head>`, meta/OG, preload, CSS inline, JSON-LD, otwarcie `<body>` |
| `public_html/partials/header.php` | nagłówek sticky + menu mobilne, otwiera `<main>` |
| `public_html/partials/cta.php` | zamyka `</main>`, sekcja „Potrzebujesz pomocy?” (pomijana gdy `$noCta`) |
| `public_html/partials/footer.php` | stopka, pływający telefon, skrypty, zamknięcie dokumentu |
| `public_html/assets/css/main.css` | @font-face, tokeny, baza, animacje, hover, RWD (`data-*`), Leaflet, lightbox |
| `public_html/assets/js/main.js` | menu mobilne |
| `public_html/assets/js/home.js` | mapa obszaru + FAQ |
| `public_html/assets/js/galeria.js` | „zobacz więcej” + lightbox |
| `public_html/assets/js/kontakt.js` | mapa HQ |
| `public_html/index.php` | dispatcher URL + strona główna |
| `public_html/oferta.php`, `galeria.php`, `kontakt.php` | podstrony |
| `public_html/robots.php`, `sitemap.php` | robots/sitemap wg hosta |
| `public_html/404.php` | strona 404 (include z dispatchera) |
| `public_html/.htaccess` | Apache: ładne URL-e, 301, https+www, cache, nagłówki |
| `public_html/llms.txt` | opis dla LLM |
| `tools/fetch_google_photos.py` | pobiera 14 zdjęć z wizytówki do `zrodla/google/` |
| `tools/build_images.py` | JPEG + AVIF/WebP + thumbs/hero + `variants.json` + `manifest.json` |
| `tools/build_gallery.py` | generuje kafelki `[data-shot]` w `galeria.php` |
| `tools/fetch_fonts.py` | pobiera woff2 Barlow do `assets/fonts/` i generuje `tools/fonts.css` |
| `tools/router.php` | podgląd lokalny (emulacja Apache/nginx) |
| `tools/deploy_staging.sh` | deploy na Mikrus |
| `.github/workflows/deploy.yml` | FTPS produkcja (czeka na sekrety) |
| `CONVENTIONS.md`, `DEPLOY.md`, `README.md`, `tools/README.md` | dokumentacja |

---

### Task 1: Szkielet repo, fonty, Leaflet, logo, router

**Files:**
- Verify: `design/Strona Glowna 1b v2.dc.html`, `design/Oferta.dc.html`, `design/Galeria.dc.html`, `design/Kontakt.dc.html`, `design/Naglowek.dc.html`, `design/Stopka.dc.html`, `design/uploads/logo.png`
- Create: `tools/fetch_fonts.py`, `tools/router.php`, `public_html/assets/favicon.svg`, `public_html/assets/img/logo.png`
- Copy: `/Users/adamfabis/Desktop/Prywatne/tuszyn/public_html/assets/vendor/leaflet/` → `public_html/assets/vendor/leaflet/`

**Interfaces:**
- Produces: `public_html/assets/fonts/*.woff2` + `tools/fonts.css` (blok `@font-face` do wklejenia do `main.css` w Task 3), `tools/router.php` (używany we wszystkich testach `php -S`).

- [ ] **Step 1: Zweryfikuj źródła designu**

Run: `ls -la design design/uploads && grep -c 'x-dc' design/*.dc.html`
Expected: 6 plików `.dc.html` (każdy ≥1 trafienie `x-dc`) i `design/uploads/logo.png`. Jeśli brakuje: `ToolSearch select:DesignSync`, potem `DesignSync {method:"get_file", projectId:"3973b39a-1059-438e-9708-13af0dd730d3", path:"<nazwa>"}` i zapisz pole `content` do pliku; logo: `path:"uploads/unnamed 1.png"` (odpowiedź base64 → zdekoduj do `design/uploads/logo.png`).

- [ ] **Step 2: Skopiuj Leaflet i logo**

```bash
mkdir -p public_html/assets/vendor public_html/assets/img public_html/assets/fonts public_html/assets/js public_html/assets/css public_html/partials
cp -R /Users/adamfabis/Desktop/Prywatne/tuszyn/public_html/assets/vendor/leaflet public_html/assets/vendor/leaflet
cp "design/uploads/logo.png" public_html/assets/img/logo.png
python3 -c "from PIL import Image; im=Image.open('public_html/assets/img/logo.png'); print(im.size, im.mode)"
```
Expected: `leaflet.js`, `leaflet.css`, `images/` w vendor; logo PNG z alfą. Jeśli logo > 400 px szerokości: `python3 -c "from PIL import Image; im=Image.open('public_html/assets/img/logo.png'); im.thumbnail((384,384)); im.save('public_html/assets/img/logo.png', optimize=True)"` (stopka używa 96 px, 4× = 384).

- [ ] **Step 3: Napisz `tools/fetch_fonts.py`**

```python
#!/usr/bin/env python3
"""Pobiera Barlow + Barlow Condensed (latin, latin-ext) z Google Fonts jako woff2 do public_html/assets/fonts/
i generuje tools/fonts.css z blokiem @font-face (ścieżki absolutne /assets/fonts/...)."""
import re, os, urllib.request
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(ROOT, 'public_html/assets/fonts')
CSS_URL = ('https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800'
           '&family=Barlow:wght@400;500;600;700&display=swap')
UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36'
os.makedirs(OUT, exist_ok=True)
css = urllib.request.urlopen(urllib.request.Request(CSS_URL, headers={'User-Agent': UA})).read().decode()
blocks = re.findall(r'/\* (\w[\w-]*) \*/\s*@font-face \{(.*?)\}', css, re.S)
out = []
for subset, body in blocks:
    if subset not in ('latin', 'latin-ext'):
        continue
    fam = re.search(r"font-family: '([^']+)'", body).group(1)
    weight = re.search(r'font-weight: (\d+)', body).group(1)
    url = re.search(r'url\((https://[^)]+\.woff2)\)', body).group(1)
    urange = re.search(r'unicode-range: ([^;]+);', body).group(1)
    fname = f"{fam.lower().replace(' ', '-')}-{weight}-{subset}.woff2"
    path = os.path.join(OUT, fname)
    if not os.path.exists(path):
        urllib.request.urlretrieve(url, path)
    out.append(f"@font-face {{\n  font-family: '{fam}';\n  font-style: normal;\n  font-weight: {weight};\n  font-display: swap;\n  src: url('/assets/fonts/{fname}') format('woff2');\n  unicode-range: {urange};\n}}")
open(os.path.join(ROOT, 'tools/fonts.css'), 'w').write('\n'.join(out) + '\n')
print(len(out), 'font faces,', len(os.listdir(OUT)), 'files')
```

- [ ] **Step 4: Uruchom i sprawdź**

Run: `python3 tools/fetch_fonts.py && ls public_html/assets/fonts | wc -l && head -8 tools/fonts.css`
Expected: `14 font faces, 14 files` (7 wag × 2 subsety), pliki `barlow-400-latin.woff2` … `barlow-condensed-800-latin-ext.woff2`.

- [ ] **Step 5: Napisz `tools/router.php`** (emuluje `.htaccess` na Apache i `try_files` na nginx: nieznane ścieżki trafiają do `index.php`, który sam robi dispatch)

```php
<?php
// Router podglądu lokalnego: php -S 127.0.0.1:8080 -t public_html tools/router.php
// Statyczne pliki serwuje wbudowany serwer; wszystko inne idzie do index.php (jak nginx try_files).
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = $_SERVER['DOCUMENT_ROOT'];
if ($uri !== '/' && is_file($root . $uri) && !preg_match('#^/(partials|tools)/|\.php$#', $uri)) return false;
if (preg_match('#^/(partials)/#', $uri)) { http_response_code(403); echo 'Forbidden'; return true; }
$_SERVER['SCRIPT_NAME'] = '/index.php';
ob_start('ob_gzhandler');
require $root . '/index.php';
return true;
```

- [ ] **Step 6: Favicon** — `public_html/assets/favicon.svg`:

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" fill="#111"/><path d="M8 40h6l6-14h22l6 14h8v8H8z" fill="#f5c518"/><circle cx="20" cy="50" r="6" fill="#f5c518"/><circle cx="46" cy="50" r="6" fill="#f5c518"/></svg>
```

- [ ] **Step 7: Commit**

```bash
git add tools/fetch_fonts.py tools/fonts.css tools/router.php public_html/assets design
git commit -m "Scaffold: design sources, fonts, Leaflet, logo, local router"
```

---

### Task 2: Zdjęcia z wizytówki Google → `assets/img`

**Files:**
- Create: `tools/fetch_google_photos.py`, `tools/build_images.py`, `tools/alts.json`
- Output: `zrodla/google/*.jpg` (ignorowane przez git), `public_html/assets/img/**`, `tools/manifest.json`

**Interfaces:**
- Produces: `public_html/assets/img/variants.json` `{"<slug>": {"w","h","widths"}, "thumbs/<slug>": …, "hero/<slug>": …}`; slugi z tabeli `PHOTOS` poniżej (używane w Task 5–8); `tools/manifest.json` `{"items":[{"idx","slug","role","gallery_pos","w","h"}]}`.

Tabela zdjęć (kolejność = `ids` z `Galeria.dc.html`, `gallery_pos` = idx). Slugi są **wstępne** — po pobraniu obejrzyj każde zdjęcie (`Read` na pliku) i popraw slug/alt tak, by opisywał to, co widać (po polsku, z frazą „Pabianice”, bez dwóch identycznych slugów).

| idx | Google ID | rola (miejsce na stronie) | slug wstępny | alt wstępny |
|---|---|---|---|---|
| 1 | `AF1QipMCuRG4erkL39pLL-v0rNfz0yXzroni7Qpzx6v6` | home-hero | `laweta-z-samochodem-droga-ekspresowa-pomoc-drogowa-pabianice` | Laweta z samochodem na drodze ekspresowej — Pomoc Drogowa Pabianice |
| 2 | `AF1QipMv1TCeNhEn7CsgdXJ8HbEe-ug58zUUvaXUlpMC` | home-about, kontakt-hero | `zolta-laweta-pomoc-drogowa-lukasz-rogowski-pabianice` | Żółta laweta Pomocy Drogowej Łukasz Rogowski |
| 3 | `AF1QipMzHpitGAQvdsdOpMms7mvUbVlFEQAZWRbQFfzp` | oferta-hero, oferta-holowanie, home-svc-6 | `auto-na-lawecie-holowanie-pabianice` | Auto na lawecie — holowanie Pabianice |
| 4 | `AF1QipNxSBvqzk0MvD3v3z-K-M2_fPR08xxLUrvz5cv6` | oferta-transport, home-svc-7 | `transport-pojazdu-laweta-pabianice` | Transport pojazdu lawetą |
| 5 | `AF1QipN3rS421J-tXZyiCLpLVrnyjECo27osUiNnQ6lm` | oferta-oc, home-svc-8 | `auto-zastepcze-z-oc-sprawcy-pabianice` | Auto zastępcze z OC sprawcy |
| 6 | `AF1QipOhg9yQtUQb0wI4jMKIpNABYi1o758Xv0dhyVUz` | oferta-naprawa, home-svc-1 | `naprawa-na-miejscu-mobilny-serwis-pabianice` | Naprawa na miejscu — mobilny serwis |
| 7 | `AF1QipO3xxEQ2QUWqHNvawCpDGmIp-zbr9nG7JyX4HBX` | oferta-kolizja, home-svc-2 | `pomoc-po-kolizji-laweta-pabianice` | Pomoc po kolizji i wypadku |
| 8 | `AF1QipMit_frYn13qotrJ1NEQ5AIg388AubGXEJEkQ8t` | home-oc | `transport-samochodu-laweta-pabianice` | Transport samochodu lawetą |
| 9 | `AF1QipOws8D63tK6NA5h08hihNIxxIbEUF61ad3O0_n7` | cta-bg, galeria-hero (og) | `laweta-noca-pomoc-drogowa-24h-pabianice` | Laweta nocą — pomoc drogowa 24h |
| 10 | `AF1QipO6uXMIkJtD7I1DQ-P4ZBrFWaJNLWplPSPBNF18` | oferta-opony, home-svc-4 | `wymiana-kola-serwis-opon-pabianice` | Awaryjna wymiana koła |
| 11 | `AF1QipPNQ_Vl7A6VJy9VQYt0VJ4j0VskjFPRj8-YAYSF` | tylko galeria | `laweta-w-akcji-pabianice-1` | Laweta w akcji — Pabianice |
| 12 | `AF1QipPK5EdP7Uxt1uwfHafz7lGTDpIy4ZodLjEe_Bj7` | tylko galeria | `laweta-w-akcji-pabianice-2` | Laweta w akcji — okolice Pabianic |
| 13 | `AF1QipMB2_dWtK4utmrc26AdqSzd-Q9oYM0msVSYuR5k` | oferta-paliwo, home-svc-5 | `dowoz-paliwa-pomoc-drogowa-pabianice` | Dowóz paliwa |
| 14 | `AF1QipOHdjErDhgeuPOzcloXnD7w3A34Mc2y4CaZGlrk` | oferta-akumulator, home-svc-3 | `awaryjne-odpalanie-rozladowany-akumulator-pabianice` | Awaryjne odpalanie auta |

- [ ] **Step 1: Napisz `tools/fetch_google_photos.py`**

```python
#!/usr/bin/env python3
"""Pobiera zdjęcia z wizytówki Google (ID z designu) do zrodla/google/<slug>.jpg.
=s0 zwraca oryginał; fallback =s1600. Pusty/niepełny plik = błąd, skrypt się zatrzymuje."""
import os, sys, urllib.request, urllib.error
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(ROOT, 'zrodla/google')
PHOTOS = [  # (idx, google_id, slug) — slug musi być zgodny z build_images.py
    (1, 'AF1QipMCuRG4erkL39pLL-v0rNfz0yXzroni7Qpzx6v6', 'laweta-z-samochodem-droga-ekspresowa-pomoc-drogowa-pabianice'),
    (2, 'AF1QipMv1TCeNhEn7CsgdXJ8HbEe-ug58zUUvaXUlpMC', 'zolta-laweta-pomoc-drogowa-lukasz-rogowski-pabianice'),
    (3, 'AF1QipMzHpitGAQvdsdOpMms7mvUbVlFEQAZWRbQFfzp', 'auto-na-lawecie-holowanie-pabianice'),
    (4, 'AF1QipNxSBvqzk0MvD3v3z-K-M2_fPR08xxLUrvz5cv6', 'transport-pojazdu-laweta-pabianice'),
    (5, 'AF1QipN3rS421J-tXZyiCLpLVrnyjECo27osUiNnQ6lm', 'auto-zastepcze-z-oc-sprawcy-pabianice'),
    (6, 'AF1QipOhg9yQtUQb0wI4jMKIpNABYi1o758Xv0dhyVUz', 'naprawa-na-miejscu-mobilny-serwis-pabianice'),
    (7, 'AF1QipO3xxEQ2QUWqHNvawCpDGmIp-zbr9nG7JyX4HBX', 'pomoc-po-kolizji-laweta-pabianice'),
    (8, 'AF1QipMit_frYn13qotrJ1NEQ5AIg388AubGXEJEkQ8t', 'transport-samochodu-laweta-pabianice'),
    (9, 'AF1QipOws8D63tK6NA5h08hihNIxxIbEUF61ad3O0_n7', 'laweta-noca-pomoc-drogowa-24h-pabianice'),
    (10, 'AF1QipO6uXMIkJtD7I1DQ-P4ZBrFWaJNLWplPSPBNF18', 'wymiana-kola-serwis-opon-pabianice'),
    (11, 'AF1QipPNQ_Vl7A6VJy9VQYt0VJ4j0VskjFPRj8-YAYSF', 'laweta-w-akcji-pabianice-1'),
    (12, 'AF1QipPK5EdP7Uxt1uwfHafz7lGTDpIy4ZodLjEe_Bj7', 'laweta-w-akcji-pabianice-2'),
    (13, 'AF1QipMB2_dWtK4utmrc26AdqSzd-Q9oYM0msVSYuR5k', 'dowoz-paliwa-pomoc-drogowa-pabianice'),
    (14, 'AF1QipOHdjErDhgeuPOzcloXnD7w3A34Mc2y4CaZGlrk', 'awaryjne-odpalanie-rozladowany-akumulator-pabianice'),
]
os.makedirs(OUT, exist_ok=True)

def fetch(url):
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    with urllib.request.urlopen(req, timeout=60) as r:
        return r.read()

for idx, gid, slug in PHOTOS:
    path = os.path.join(OUT, f'{idx:02d}-{slug}.jpg')
    if os.path.exists(path) and os.path.getsize(path) > 10_000:
        print('ok  ', path); continue
    data = None
    for size in ('s0', 's1600'):
        try:
            data = fetch(f'https://lh3.googleusercontent.com/p/{gid}={size}')
            if len(data) > 10_000: break
        except urllib.error.HTTPError as e:
            print(f'warn {gid}={size}: HTTP {e.code}')
    if not data or len(data) <= 10_000:
        sys.exit(f'BŁĄD: nie pobrano {gid} ({slug})')
    with open(path, 'wb') as f: f.write(data)
    print('got ', path, len(data))
print('done', len(PHOTOS))
```

- [ ] **Step 2: Uruchom i obejrzyj zdjęcia**

Run: `python3 tools/fetch_google_photos.py && python3 -c "from PIL import Image; import glob; [print(p, Image.open(p).size) for p in sorted(glob.glob('zrodla/google/*.jpg'))]"`
Expected: 14 plików, każdy ≥ 1000 px szerokości. Obejrzyj każdy plik narzędziem `Read` i **popraw slugi i alt-y** w `PHOTOS` (tu i w `build_images.py`), jeśli treść zdjęcia nie pasuje do nazwy (np. zdjęcie 11/12 przedstawia coś konkretnego). Po zmianie slugów usuń stare pliki z `zrodla/google/` i uruchom ponownie.

- [ ] **Step 3: Napisz `tools/alts.json`** (klucz = idx jako string, wartość = alt; teksty z tabeli po ewentualnej korekcie)

```json
{
  "1": "Laweta z samochodem na drodze ekspresowej — Pomoc Drogowa Pabianice",
  "2": "Żółta laweta Pomocy Drogowej Łukasz Rogowski w Pabianicach",
  "3": "Auto na lawecie — holowanie w Pabianicach",
  "4": "Transport pojazdu lawetą — Pabianice i okolice",
  "5": "Auto zastępcze z OC sprawcy — Pomoc Drogowa Pabianice",
  "6": "Naprawa na miejscu — mobilny serwis Pabianice",
  "7": "Pomoc po kolizji — laweta Pabianice",
  "8": "Transport samochodu lawetą — Pomoc Drogowa Łukasz Rogowski",
  "9": "Laweta nocą — pomoc drogowa 24h Pabianice",
  "10": "Awaryjna wymiana koła na miejscu — Pabianice",
  "11": "Laweta w akcji — Pomoc Drogowa Pabianice",
  "12": "Laweta w akcji — okolice Pabianic",
  "13": "Dowóz paliwa — Pomoc Drogowa Pabianice",
  "14": "Awaryjne odpalanie auta z rozładowanym akumulatorem — Pabianice"
}
```

- [ ] **Step 4: Napisz `tools/build_images.py`** (port z Tuszyna: te same wymiary i jakości; źródło = `zrodla/google/`)

```python
#!/usr/bin/env python3
"""Generuje public_html/assets/img/<slug>.jpg (max 1600) + AVIF/WebP {480,800,1200,1600},
thumbs/<slug>.jpg (800) + {400,800}, hero/<slug>.jpg (1920) + {960,1440,1920}, variants.json, manifest.json.
Źródło: zrodla/google/NN-<slug>.jpg. Wszystkie zdjęcia trafiają do galerii (gallery_pos = idx)."""
import os, re, json, glob
from PIL import Image, ImageOps
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SP = os.path.join(ROOT, 'tools')
SRC = os.path.join(ROOT, 'zrodla/google')
OUT = os.path.join(ROOT, 'public_html/assets/img')
ROLES = {1: ['home-hero'], 2: ['home-about', 'kontakt-hero'], 3: ['oferta-hero', 'oferta-holowanie', 'home-svc-6'],
         4: ['oferta-transport', 'home-svc-7'], 5: ['oferta-oc', 'home-svc-8'], 6: ['oferta-naprawa', 'home-svc-1'],
         7: ['oferta-kolizja', 'home-svc-2'], 8: ['home-oc'], 9: ['cta-bg', 'galeria-hero'],
         10: ['oferta-opony', 'home-svc-4'], 13: ['oferta-paliwo', 'home-svc-5'], 14: ['oferta-akumulator', 'home-svc-3']}
FULL_WIDTHS = [480, 800, 1200, 1600]; THUMB_WIDTHS = [400, 800]; HERO_WIDTHS = [960, 1440, 1920]
AVIF_Q, AVIF_SPEED, WEBP_Q, WEBP_METHOD = 55, 6, 78, 6

files = sorted(glob.glob(SRC + '/*.jpg'))
assert len(files) == 14, f'oczekiwano 14 zdjęć w {SRC}, jest {len(files)}'
items = []
for p in files:
    m = re.match(r'(\d+)-(.+)\.jpg$', os.path.basename(p))
    idx, slug = int(m.group(1)), m.group(2)
    items.append({'idx': idx, 'src': p, 'slug': slug, 'roles': ROLES.get(idx, []), 'gallery_pos': idx})
assert len({it['slug'] for it in items}) == 14, 'zduplikowany slug'

class LazyImage:
    def __init__(self, src): self.src = src; self._im = None
    def get(self):
        if self._im is None: self._im = ImageOps.exif_transpose(Image.open(self.src)).convert('RGB')
        return self._im

def up_to_date(path, src_mtime): return os.path.exists(path) and os.path.getmtime(path) >= src_mtime
def fit(im, box):
    if im.width <= box[0] and im.height <= box[1]: return im
    return ImageOps.contain(im, box, Image.LANCZOS)
def save_jpg(lazy, path, box, q, src_mtime):
    if up_to_date(path, src_mtime):
        with Image.open(path) as ex: return ex.width, ex.height
    im2 = fit(lazy.get(), box); im2.save(path, 'JPEG', quality=q, optimize=True, progressive=True)
    return im2.width, im2.height
def widths_for(std, actual_w):
    ws = [w for w in std if w <= actual_w]
    if actual_w not in ws: ws.append(actual_w)
    return sorted(set(ws))
def save_nextgen(lazy, prefix, widths, src_mtime):
    for w in widths:
        ap, wp = f'{prefix}-{w}.avif', f'{prefix}-{w}.webp'
        na, nw = not up_to_date(ap, src_mtime), not up_to_date(wp, src_mtime)
        if na or nw:
            im2 = ImageOps.contain(lazy.get(), (w, w), Image.LANCZOS)
            if na: im2.save(ap, 'AVIF', quality=AVIF_Q, speed=AVIF_SPEED)
            if nw: im2.save(wp, 'WEBP', quality=WEBP_Q, method=WEBP_METHOD)

os.makedirs(OUT + '/thumbs', exist_ok=True); os.makedirs(OUT + '/hero', exist_ok=True)
variants = {}
for it in items:
    mt = os.path.getmtime(it['src']); lazy = LazyImage(it['src'])
    with Image.open(it['src']) as h: it['w'], it['h'] = h.size
    fw, fh = save_jpg(lazy, f"{OUT}/{it['slug']}.jpg", (1600, 1600), 82, mt)
    ws = widths_for(FULL_WIDTHS, fw); save_nextgen(lazy, f"{OUT}/{it['slug']}", ws, mt)
    variants[it['slug']] = {'w': fw, 'h': fh, 'widths': ws}
    tw, th = save_jpg(lazy, f"{OUT}/thumbs/{it['slug']}.jpg", (800, 800), 78, mt)
    tws = widths_for(THUMB_WIDTHS, tw); save_nextgen(lazy, f"{OUT}/thumbs/{it['slug']}", tws, mt)
    variants['thumbs/' + it['slug']] = {'w': tw, 'h': th, 'widths': tws}
    if any(r.endswith('-hero') for r in it['roles']):
        hw, hh = save_jpg(lazy, f"{OUT}/hero/{it['slug']}.jpg", (1920, 1920), 80, mt)
        hws = widths_for(HERO_WIDTHS, hw); save_nextgen(lazy, f"{OUT}/hero/{it['slug']}", hws, mt)
        variants['hero/' + it['slug']] = {'w': hw, 'h': hh, 'widths': hws}

VAR_RE = re.compile(r'^(.*)-(\d+)\.(?:avif|webp)$')
def clean_dir(d, keep):
    for f in sorted(os.listdir(d)):
        fp = os.path.join(d, f)
        if os.path.isdir(fp) or f.startswith('.') or f in ('variants.json', 'logo.png'): continue
        base = f[:-4] if f.endswith('.jpg') else (VAR_RE.match(f).group(1) if VAR_RE.match(f) else None)
        if base is not None and base not in keep: os.remove(fp); print('usunięto', fp)
clean_dir(OUT, {it['slug'] for it in items})
clean_dir(OUT + '/thumbs', {it['slug'] for it in items})
clean_dir(OUT + '/hero', {it['slug'] for it in items if any(r.endswith('-hero') for r in it['roles'])})
json.dump({'items': items}, open(SP + '/manifest.json', 'w'), ensure_ascii=False, indent=1)
json.dump(variants, open(OUT + '/variants.json', 'w'), ensure_ascii=False, indent=1, sort_keys=True)
print('items', len(items), 'variants', len(variants), 'hero', sum(1 for k in variants if k.startswith('hero/')))
```

- [ ] **Step 5: Uruchom build i sprawdź**

Run: `python3 tools/build_images.py && python3 -c "import json; v=json.load(open('public_html/assets/img/variants.json')); print(sorted(k for k in v if k.startswith('hero/')))" && du -sh public_html/assets/img && ls public_html/assets/img/hero`
Expected: `items 14 variants 32 hero 4` (hero: slugi idx 1, 2, 3, 9), katalog < 40 MB. Jeśli któreś źródło jest węższe niż 1920 px, jego lista `widths` w hero kończy się rzeczywistą szerokością — to OK, ale `head.php` (Task 3) buduje srcset z `variants.json`, więc nic nie trzeba poprawiać.

- [ ] **Step 6: Test fallbacku pobierania** (Review Focus 5)

Run: `python3 - <<'EOF'
import urllib.request, urllib.error
try:
    urllib.request.urlopen(urllib.request.Request('https://lh3.googleusercontent.com/p/AF1QipNIEISTNIEJE_000000000000000000000000=s0', headers={'User-Agent':'Mozilla/5.0'}))
except urllib.error.HTTPError as e: print('HTTP', e.code)
EOF`
Expected: `HTTP 4xx` — potwierdza, że nieistniejące ID daje wyjątek, który skrypt łapie i kończy `sys.exit` z komunikatem (nie tworzy pliku).

- [ ] **Step 7: Commit**

```bash
git add tools/fetch_google_photos.py tools/build_images.py tools/alts.json tools/manifest.json public_html/assets/img
git commit -m "Add Google photo pipeline and generated image variants"
```

---

### Task 3: config.php, head.php, main.css, robots/sitemap, dispatcher, .htaccess

**Files:**
- Create: `public_html/partials/config.php`, `public_html/partials/head.php`, `public_html/assets/css/main.css`, `public_html/robots.php`, `public_html/sitemap.php`, `public_html/404.php`, `public_html/index.php` (tymczasowo: dispatcher + placeholder treści, sekcje w Task 5), `public_html/.htaccess`

**Interfaces:**
- Produces (dla stron): zmienne wejściowe `$page, $title, $desc, $path, $ogImage, $pageJs, $preloadHero (bool, domyślnie true), $noCta, $faqLd (array par [q,a], tylko home)`; z `config.php`: `$PROD_HOST, $IS_PROD, $BASE, $PHONE_HREF='tel:+48517574330', $PHONE='+48 517 574 330', $PHONE_SHORT='517 574 330', $ADDR1='ul. Stanisława Moniuszki 41', $ADDR2='95-200 Pabianice', $GMAPS_PLACE, $GREVIEWS, $FB, $HQ=[51.6607527,19.3441975]`; funkcja `pd_picture($key, $alt, $imgStyle, $sizes='100vw', $eager=false, $extraImgAttr='')` w `config.php` zwraca HTML `<picture>` na podstawie `variants.json`.
- Klasy CSS (używane w Task 4–8): `.hov-brand`, `.hov-lift`, `.hov-big`, `.hov-red`, `.hov-white`, `.press`, `.chip`, `.chip.is-on`, `.faq-btn`, `.faq-btn.is-open`, `.faq-wrap`, `.faq-wrap.is-open`, `.svc-grid`, `.svc-row`, `.gal-grid`, `.gal-cell`, `.gal-cell.is-big`, `.lb`, `.lb.is-open`; atrybuty RWD: `[data-nav]`, `[data-navlinks]`, `[data-navphone]`, `[data-burger]`, `[data-menu]`, `[data-fab]`, `[data-bottomrow]`, `[data-leaflet]`.

- [ ] **Step 1: `public_html/partials/config.php`**

```php
<?php
if (!defined('PD_APP')) { http_response_code(403); exit; }
/** Stałe projektu + środowisko. Include jako pierwszy w każdej stronie. */
$PROD_HOST = 'www.pomocdrogowa-pabianice.pl';
$IS_PROD = ($_SERVER['HTTP_HOST'] ?? '') === $PROD_HOST;
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
if (!$IS_PROD && !headers_sent()) { header('X-Robots-Tag: noindex, nofollow'); }
if (!headers_sent()) { header('X-LiteSpeed-Purge: *'); }

$PD_VARIANTS = json_decode(file_get_contents(__DIR__ . '/../assets/img/variants.json'), true);

/** <picture> z AVIF/WebP/JPEG. $key = 'slug' | 'thumbs/slug' | 'hero/slug'. */
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
```

- [ ] **Step 2: `public_html/partials/head.php`**

```php
<?php
if (!defined('PD_APP')) { http_response_code(403); exit; }
/** Wymaga: $page, $title, $desc, $path, $ogImage (assets/img/hero/<slug>.jpg). Opcjonalnie: $preloadHero, $faqLd. */
$heroSlug = pathinfo($ogImage, PATHINFO_FILENAME);
$heroV = $PD_VARIANTS['hero/' . $heroSlug];
$heroSrcset = implode(', ', array_map(fn($w) => "/assets/img/hero/{$heroSlug}-{$w}.avif {$w}w", $heroV['widths']));
$areaServed = array_map(fn($n) => ['@type' => 'City', 'name' => $n],
    ['Pabianice', 'Łódź', 'Konstantynów Łódzki', 'Ksawerów', 'Rzgów', 'Dobroń', 'Łask', 'Zduńska Wola', 'Lutomiersk']);
$areaServed[] = ['@type' => 'AdministrativeArea', 'name' => 'województwo łódzkie'];
$hours = ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'], 'opens' => '00:00', 'closes' => '23:59'];
$ld = [];
$ld[] = [
    '@context' => 'https://schema.org', '@type' => 'AutomotiveBusiness', '@id' => $BASE . '#firma',
    'name' => $SITE_NAME, 'url' => $BASE, 'telephone' => '+48517574330',
    'image' => array_map(fn($k) => $BASE . 'assets/img/hero/' . basename($k) . '.jpg', array_filter(array_keys($PD_VARIANTS), fn($k) => str_starts_with($k, 'hero/'))),
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
if ($page !== 'home') {
    $bn = ['oferta' => 'Oferta', 'galeria' => 'Galeria', 'kontakt' => 'Kontakt'];
    $ld[] = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Strona główna', 'item' => $BASE],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $bn[$page], 'item' => $canonical]]];
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
```

- [ ] **Step 3: `public_html/assets/css/main.css`** — wklej zawartość `tools/fonts.css` na początek, potem:

```css
/* ── Tokeny ── */
:root { --bg:#1f1f1f; --bg2:#262626; --bg3:#2a2a2a; --deep:#141414; --nav:#161616; --ink:#111; --brand:#f5c518; --red:#e8342a; --red2:#ff4336; --ok:#3ad36b; --text:#f2f2f2; --navh:79px; --E:cubic-bezier(.2,.7,.2,1); }
/* ── Baza (z <helmet> designu) ── */
*{box-sizing:border-box}
html{scroll-behavior:smooth}
body{margin:0;background:#1f1f1f;font-family:'Barlow',system-ui,sans-serif;color:#f2f2f2;-webkit-text-size-adjust:100%}
a{color:#f5c518;text-decoration:none}
a:hover{color:#fff}
img{max-width:100%}
button{font:inherit;color:inherit}
a:focus-visible,button:focus-visible{outline:2px solid #f5c518;outline-offset:2px}
::selection{background:#f5c518;color:#111}
@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(58,211,107,.7)}70%{box-shadow:0 0 0 10px rgba(58,211,107,0)}100%{box-shadow:0 0 0 0 rgba(58,211,107,0)}}
.dot{width:10px;height:10px;border-radius:50%;background:#3ad36b;flex:none;display:inline-block;animation:pulse 1.8s infinite}
[hidden]{display:none!important}
/* ── Hover/active z atrybutów style-hover / style-active designu ── */
.hov-brand:hover{background:#f5c518!important;border-color:#f5c518!important;color:#111!important}
.hov-lift:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.35)}
.hov-lift-flat:hover{transform:translateY(-2px)}
.hov-big:hover{color:#f5c518!important;transform:translate(-3px,-3px);box-shadow:15px 15px 0 #f5c518}
.hov-red:hover{background:#ff4336!important;color:#fff!important;transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.35)}
.hov-yellow:hover{background:#ffd83a!important;color:#111!important;transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.35)}
.hov-white:hover{color:#fff!important}
.hov-dark:hover{background:#303030!important;color:#f2f2f2!important;transform:translateY(-2px)}
.hov-border:hover{border-color:#f5c518!important}
.hov-text:hover{color:#f5c518!important}
.press:active{transform:translateY(0) scale(.98)!important}
/* ── Nagłówek (Naglowek.dc.html: mob = <900px) ── */
[data-navlinks]{display:flex;gap:26px;font-size:15px;font-weight:600;white-space:nowrap}
[data-navlinks] a{color:#ddd;font-weight:600;padding-bottom:4px;border-bottom:2px solid transparent;transition:color .2s,border-color .2s}
[data-navlinks] a.is-on{color:#f5c518;font-weight:800;border-bottom-color:#f5c518}
[data-navphone]{background:#e8342a;color:#fff;font-weight:800;padding:12px 20px;font-size:18px;white-space:nowrap;display:flex;align-items:center;gap:10px;flex:none;transition:background-color .2s,transform .2s var(--E),box-shadow .2s}
[data-burger]{all:unset;cursor:pointer;position:relative;width:44px;height:44px;flex:none;display:none;border:2px solid #444;box-sizing:border-box}
[data-burger] span{position:absolute;left:9px;width:22px;height:2px;background:#f5c518;transition:transform .3s var(--E),opacity .2s}
[data-burger] span:nth-child(1){top:12px}[data-burger] span:nth-child(2){top:20px}[data-burger] span:nth-child(3){top:28px}
[data-nav].menu-open [data-burger] span:nth-child(1){transform:translateY(8px) rotate(45deg)}
[data-nav].menu-open [data-burger] span:nth-child(2){opacity:0}
[data-nav].menu-open [data-burger] span:nth-child(3){transform:translateY(-8px) rotate(-45deg)}
[data-menu]{position:absolute;left:0;right:0;top:100%;height:calc(100dvh - 79px);overflow-y:auto;background:#161616;border-top:1px solid #333;display:none;opacity:0;transform:translateY(-12px);visibility:hidden;transition:opacity .25s,transform .35s var(--E),visibility .35s}
[data-menu] a.m-link{font-family:'Barlow Condensed';font-weight:800;font-size:34px;color:#f2f2f2;padding:14px 0;border-bottom:1px solid #333;display:flex;justify-content:space-between;align-items:center}
[data-menu] a.m-link.is-on{color:#f5c518}
body.menu-open{overflow:hidden}
@media (max-width:899px){
  [data-navlinks],[data-navphone]{display:none}
  [data-burger]{display:block}
  [data-menu]{display:block}
  [data-nav].menu-open [data-menu]{opacity:1;transform:translateY(0);visibility:visible}
}
/* ── Stopka + pływający telefon (Stopka.dc.html) ── */
[data-bottomrow]{max-width:1200px;margin:0 auto;padding:18px 20px;display:flex;justify-content:flex-start;gap:8px 28px;flex-wrap:wrap;font-size:13px;color:#777}
[data-fab]{position:fixed;right:20px;bottom:20px;z-index:30;background:#e8342a;color:#fff;font-weight:800;font-size:20px;padding:16px 22px;box-shadow:0 10px 28px rgba(0,0,0,.55);display:flex;align-items:center;gap:12px;white-space:nowrap;border:2px solid #fff;transition:background-color .2s,transform .2s,box-shadow .2s}
[data-fab]:hover{background:#ff4336;color:#fff;transform:translateY(-3px);box-shadow:0 14px 32px rgba(0,0,0,.6)}
[data-fab] .fab-mob{display:none}
@media (max-width:899px){
  [data-bottomrow]{padding:18px 20px 84px}
  [data-fab]{left:0;right:0;bottom:0;font-size:18px;padding:16px 20px calc(16px + env(safe-area-inset-bottom));justify-content:center;border:0;border-top:2px solid #fff;box-shadow:0 -8px 24px rgba(0,0,0,.45)}
  [data-fab]:hover{transform:none}
  [data-fab] .fab-desk{display:none}[data-fab] .fab-mob{display:inline}
  body.menu-open [data-fab],body.lb-open [data-fab]{display:none}
}
/* ── Strona główna ── */
.chip{all:unset;cursor:pointer;padding:7px 12px;font-size:14px;font-weight:500;background:transparent;color:#eee;border:1px solid #666;transition:background-color .2s,color .2s,border-color .2s,transform .2s}
.chip.is-on{font-weight:800;background:#f5c518;color:#111;border-color:#f5c518;transform:translateY(-1px)}
.svc-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:3px}
@media (max-width:999px){.svc-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width:559px){.svc-grid{grid-template-columns:1fr}}
.faq-btn{all:unset;cursor:pointer;width:100%;padding:18px 0;display:flex;justify-content:space-between;align-items:center;gap:16px;font-weight:700;font-size:18px;color:#f2f2f2;transition:color .25s}
.faq-btn.is-open{color:#f5c518}
.faq-icon{position:relative;width:34px;height:34px;flex:none;background:transparent;border:2px solid #555;transition:background-color .25s,border-color .25s}
.faq-btn.is-open .faq-icon{background:#f5c518;border-color:#f5c518}
.faq-icon i{position:absolute;left:50%;top:50%;background:#f5c518;transition:transform .3s var(--E),background-color .25s}
.faq-icon .h{width:14px;height:2px;margin-left:-7px;margin-top:-1px}
.faq-icon .v{width:2px;height:14px;margin-left:-1px;margin-top:-7px}
.faq-btn.is-open .faq-icon i{background:#111}
.faq-btn.is-open .faq-icon .v{transform:scaleY(0)}
.faq-wrap{display:grid;grid-template-rows:0fr;transition:grid-template-rows .4s var(--E)}
.faq-wrap.is-open{grid-template-rows:1fr}
.faq-wrap p{margin:0;padding:0 48px 20px 0;font-size:16px;line-height:1.6;color:#bbb;opacity:0;transform:translateY(-8px);transition:opacity .3s 0s,transform .4s var(--E)}
.faq-wrap.is-open p{opacity:1;transform:translateY(0);transition:opacity .3s .1s,transform .4s var(--E)}
/* ── Oferta (mob = <820px) ── */
.svc-row{display:flex;flex-direction:row;gap:56px;align-items:center;scroll-margin-top:100px}
.svc-row.is-rev{flex-direction:row-reverse}
@media (max-width:819px){.svc-row,.svc-row.is-rev{flex-direction:column;gap:24px;align-items:stretch}}
/* ── Galeria (cols: <700 = 2, else 4) ── */
.gal-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));grid-auto-rows:minmax(0,1fr);grid-auto-flow:dense;gap:6px}
.gal-cell{all:unset;cursor:zoom-in;display:block;overflow:hidden;background:#2a2a2a;aspect-ratio:1;position:relative}
.gal-cell.is-big{grid-column:span 2;grid-row:span 2}
.gal-cell img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .5s var(--E),opacity .3s}
.gal-cell:hover img{transform:scale(1.05);opacity:.85}
@media (max-width:699px){.gal-grid{grid-template-columns:repeat(2,minmax(0,1fr));grid-auto-rows:auto}}
.lb{position:fixed;inset:0;z-index:100;background:rgba(10,10,10,.94);display:flex;align-items:center;justify-content:center;padding:72px 84px;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s}
.lb.is-open{opacity:1;visibility:visible}
.lb img{max-width:100%;max-height:100%;object-fit:contain;border:3px solid #f5c518;transform:scale(.96);transition:transform .3s var(--E)}
.lb.is-open img{transform:scale(1)}
@media (max-width:699px){.lb{padding:64px 12px}}
body.lb-open{overflow:hidden}
/* ── Mapy Leaflet (ciemny wygląd) ── */
[data-leaflet]{background:#2a2a2a}
[data-leaflet] .leaflet-tile-pane{filter:grayscale(1) invert(.92) contrast(1.05) brightness(.9)}
[data-leaflet] .leaflet-container{background:#2a2a2a;font-family:'Barlow',system-ui,sans-serif}
[data-leaflet] .leaflet-control-attribution{font-size:9px;background:rgba(17,17,17,.85);color:#aaa}
[data-leaflet] .leaflet-control-attribution a{color:#f5c518}
[data-leaflet] .leaflet-bar{border:2px solid #f5c518;box-shadow:none}
[data-leaflet] .leaflet-bar a{border-radius:0;background:#111;color:#f5c518;border-bottom-color:#333}
.pd-pin{background:none;border:none}
.pd-pin i{display:block;width:100%;height:100%;border-radius:50%;background:#111;border:2px solid #f5c518;box-sizing:border-box;cursor:pointer}
.pd-pin.is-hub i{background:#f5c518;box-shadow:0 0 0 3px rgba(245,197,24,.25)}
.pd-pin.is-active i{background:#f5c518;box-shadow:0 0 0 5px rgba(58,211,107,.45)}
.pd-label{background:none;border:none;font-family:'Barlow',system-ui,sans-serif;font-size:11px;font-weight:800;color:#f2f2f2;white-space:nowrap;text-shadow:0 0 3px #111,0 0 3px #111,0 0 3px #111}
.pd-hq{background:none;border:none}
.pd-hq i{display:block;width:100%;height:100%;background:#f5c518;border:3px solid #111;box-sizing:border-box;box-shadow:0 0 0 6px rgba(245,197,24,.35)}
.leaflet-popup-content-wrapper,.leaflet-popup-tip{background:#111;color:#f2f2f2;border-radius:0}
```

- [ ] **Step 4: `public_html/robots.php`, `sitemap.php`, `404.php`**

```php
<?php // robots.php
define('PD_APP', true); include __DIR__ . '/partials/config.php';
header('Content-Type: text/plain; charset=utf-8');
if ($IS_PROD) { echo "User-agent: *\nAllow: /\nDisallow: /partials/\n\nSitemap: {$BASE}sitemap.xml\n"; }
else { echo "User-agent: *\nDisallow: /\n"; }
```

```php
<?php // sitemap.php
define('PD_APP', true); include __DIR__ . '/partials/config.php';
header('Content-Type: application/xml; charset=utf-8');
$pages = ['' => ['index.php', '1.0'], 'oferta/' => ['oferta.php', '0.8'], 'galeria/' => ['galeria.php', '0.6'], 'kontakt/' => ['kontakt.php', '0.8']];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($pages as $p => [$file, $prio]) {
    $mod = date('Y-m-d', max(filemtime(__DIR__ . '/' . $file), filemtime(__DIR__ . '/partials/head.php')));
    echo "  <url><loc>{$BASE}{$p}</loc><lastmod>{$mod}</lastmod><priority>{$prio}</priority></url>\n";
}
echo "</urlset>\n";
```

```php
<?php // 404.php — include z dispatchera (index.php), PD_APP już zdefiniowane
http_response_code(404);
$page = '404'; $title = 'Nie znaleziono strony | Pomoc Drogowa Pabianice';
$desc = 'Strona nie istnieje. Zadzwoń: +48 517 574 330 — pomoc drogowa Pabianice 24/7.';
$path = '/'; $ogImage = 'assets/img/hero/' . basename(array_values(array_filter(array_keys(json_decode(file_get_contents(__DIR__ . '/assets/img/variants.json'), true)), fn($k) => str_starts_with($k, 'hero/')))[0]) . '.jpg';
$preloadHero = false; $noCta = true;
include __DIR__ . '/partials/config.php';
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/header.php';
?>
  <section style="max-width:1200px;margin:0 auto;padding:clamp(64px,12vw,120px) 20px;display:flex;flex-direction:column;gap:18px;align-items:flex-start">
    <div style="font-size:13px;letter-spacing:3px;font-weight:700;color:#f5c518">BŁĄD 404</div>
    <h1 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(44px,7vw,80px);line-height:.95">Nie ma takiej strony</h1>
    <p style="margin:0;max-width:560px;font-size:18px;line-height:1.55;color:#ddd">Adres jest błędny albo strona została przeniesiona. Potrzebujesz pomocy na drodze? Zadzwoń — odbieramy całą dobę.</p>
    <a href="tel:+48517574330" class="hov-yellow press" style="background:#f5c518;color:#111;font-weight:800;font-size:20px;padding:16px 26px;transition:background-color .2s,transform .2s var(--E),box-shadow .2s">Zadzwoń: +48 517 574 330</a>
    <a href="/" style="text-decoration:underline">← Strona główna</a>
  </section>
<?php include __DIR__ . '/partials/cta.php'; include __DIR__ . '/partials/footer.php';
```
W `head.php` linia z `$bn[$page]` musi tolerować `'404'`: użyj `$bn[$page] ?? 'Błąd 404'`. W `header.php`/`footer.php` (Task 4) aktywność linku porównuje `$page === $key`, więc `'404'` nie podświetla nic — OK.

- [ ] **Step 5: `public_html/index.php` — dispatcher (na górze pliku; sekcje strony głównej dopisze Task 5)**

```php
<?php
define('PD_APP', true);
// Dispatcher: na nginx (staging) wszystkie nieistniejące ścieżki trafiają tu przez try_files;
// na Apache .htaccess mapuje ładne URL-e bezpośrednio, ale ten kod jest nieszkodliwy.
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
$title = 'Pomoc drogowa Pabianice 24/7 – laweta, holowanie | Ł. Rogowski';
$desc = 'Całodobowa pomoc drogowa Pabianice i okolice: holowanie, laweta, naprawa na miejscu, odpalanie auta, wymiana koła, dowóz paliwa, auto zastępcze z OC sprawcy. Tel. +48 517 574 330.';
$path = '/';
$ogImage = 'assets/img/hero/laweta-z-samochodem-droga-ekspresowa-pomoc-drogowa-pabianice.jpg';
$pageJs = 'assets/js/home.js';
include __DIR__ . '/partials/config.php';
include __DIR__ . '/partials/head.php';
// TODO-TASK5: header, sekcje, cta, footer
echo '<main><h1>Strona główna — w budowie (Task 5)</h1></main></div></body></html>';
```
(`$ogImage` = slug hero idx 1 po korekcie z Task 2 — sprawdź w `variants.json`. `$title` ma 60 znaków; policz: `python3 -c "print(len('Pomoc drogowa Pabianice 24/7 – laweta, holowanie | Ł. Rogowski'))"`. Jeśli > 60, użyj `Pomoc drogowa Pabianice 24/7 – laweta i holowanie`.)

- [ ] **Step 6: `public_html/.htaccess`**

```apache
# Pomoc Drogowa Pabianice
AddDefaultCharset utf-8
DirectoryIndex index.php
AddType image/avif .avif
AddType image/webp .webp
AddType font/woff2 .woff2

<IfModule LiteSpeed>
  CacheLookup off
</IfModule>

<IfModule mod_rewrite.c>
  RewriteEngine On

  # https + www — tylko dla domeny produkcyjnej (na innych hostach bez zmian)
  RewriteCond %{HTTP_HOST} ^(www\.)?pomocdrogowa-pabianice\.pl$ [NC]
  RewriteCond %{HTTPS} !=on [OR]
  RewriteCond %{HTTP_HOST} !^www\. [NC]
  RewriteRule ^(.*)$ https://www.pomocdrogowa-pabianice.pl/$1 [R=301,L]

  # partiale tylko przez include; pliki ukryte 403 (poza .well-known)
  RewriteRule ^partials/ - [F,L]
  RewriteRule (^|/)\.(?!well-known(/|$)) - [F,L]

  # robots / sitemap generowane w PHP
  RewriteRule ^robots\.txt$ robots.php [L]
  RewriteRule ^sitemap\.xml$ sitemap.php [L]

  # stare adresy .php i wersje bez ukośnika -> 301 na ładne URL-e
  RewriteCond %{THE_REQUEST} \s/index\.php[?\s]
  RewriteRule ^index\.php$ / [R=301,L]
  RewriteCond %{THE_REQUEST} \s/(oferta|galeria|kontakt)\.php[?\s]
  RewriteRule ^(oferta|galeria|kontakt)\.php$ /$1/ [R=301,L]
  RewriteRule ^(oferta|galeria|kontakt)$ /$1/ [R=301,L]

  # ładne URL-e -> pliki PHP
  RewriteRule ^(oferta|galeria|kontakt)/$ $1.php [L]

  # wszystko inne, co nie jest plikiem/katalogiem -> index.php (dispatcher zwróci 404)
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^ index.php [L]
</IfModule>

<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/css application/javascript image/svg+xml application/json text/xml application/xml
</IfModule>

<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/jpeg "access plus 30 days"
  ExpiresByType image/png "access plus 30 days"
  ExpiresByType image/svg+xml "access plus 30 days"
  ExpiresByType image/avif "access plus 30 days"
  ExpiresByType image/webp "access plus 30 days"
  ExpiresByType font/woff2 "access plus 365 days"
  <FilesMatch "\.(js|css)$">
    ExpiresDefault "access plus 1 year"
  </FilesMatch>
</IfModule>

<IfModule mod_headers.c>
  Header set X-Content-Type-Options "nosniff"
  Header set Referrer-Policy "strict-origin-when-cross-origin"
  Header set X-Frame-Options "SAMEORIGIN"
  <FilesMatch "\.(js|css)$">
    Header set Cache-Control "public, max-age=31536000, immutable"
  </FilesMatch>
  <FilesMatch "\.php$">
    Header set Cache-Control "no-cache, must-revalidate"
  </FilesMatch>
  # HSTS tylko na produkcji (Apache 2.4 expr)
  Header always set Strict-Transport-Security "max-age=31536000" "expr=%{HTTP_HOST} =~ /pomocdrogowa-pabianice\.pl$/"
</IfModule>
```

- [ ] **Step 7: Tymczasowe stuby partiali, żeby 404.php działał w tym tasku**: utwórz `public_html/partials/header.php`, `cta.php`, `footer.php` z minimalną treścią (Task 4 je nadpisze):

```php
<?php if (!defined('PD_APP')) { http_response_code(403); exit; } ?>
<header data-nav><nav><a href="/">Strona główna</a></nav></header><main>
```
```php
<?php if (!defined('PD_APP')) { http_response_code(403); exit; } ?>
</main>
```
```php
<?php if (!defined('PD_APP')) { http_response_code(403); exit; } ?>
<footer></footer></div></body></html>
```

- [ ] **Step 8: Test lint + routing + noindex** (Review Focus 1, 2)

```bash
for f in public_html/*.php public_html/partials/*.php; do php -l "$f" | grep -v 'No syntax'; done
php -S 127.0.0.1:8080 -t public_html tools/router.php >/tmp/php.log 2>&1 &
sleep 1
for u in / /oferta/ /galeria/ /kontakt/ /robots.txt /sitemap.xml /oferta /oferta.php /index.php /nie-ma /partials/config.php; do printf '%-22s ' "$u"; curl -s -o /dev/null -w '%{http_code} %{redirect_url}\n' "http://127.0.0.1:8080$u"; done
curl -s http://127.0.0.1:8080/robots.txt; curl -sI http://127.0.0.1:8080/ | grep -i x-robots
curl -s -H 'Host: www.pomocdrogowa-pabianice.pl' http://127.0.0.1:8080/robots.txt
curl -s -H 'Host: www.pomocdrogowa-pabianice.pl' http://127.0.0.1:8080/ | grep -c 'noindex'
curl -s http://127.0.0.1:8080/ | grep -o '<link rel="canonical" href="[^"]*"'
kill %1
```
Expected: `/` 200, `/oferta/` `/galeria/` `/kontakt/` → 500 lub „Task 6” (pliki jeszcze nie istnieją — dopuszczalne: jeśli `require` pada, utwórz puste `oferta.php`/`galeria.php`/`kontakt.php` z `<?php define('PD_APP', true); echo 'stub';` — Task 6–8 je nadpiszą), `/robots.txt` 200 (`Disallow: /`), `/sitemap.xml` 200, `/oferta` i `/oferta.php` 301 → `/oferta/`, `/index.php` 301 → `/`, `/nie-ma` 404, `/partials/config.php` 403; nagłówek `X-Robots-Tag: noindex, nofollow` obecny; z hostem produkcyjnym robots ma `Allow: /` i strona ma `0` wystąpień `noindex`; canonical = `https://www.pomocdrogowa-pabianice.pl/` niezależnie od hosta.

- [ ] **Step 9: Commit**

```bash
git add public_html
git commit -m "Add config/head partials, main.css, robots/sitemap, URL dispatcher, .htaccess"
```

---

### Task 4: header.php, cta.php, footer.php, main.js

**Files:**
- Modify (nadpisz stuby): `public_html/partials/header.php`, `cta.php`, `footer.php`
- Create: `public_html/assets/js/main.js`
- Source: `design/Naglowek.dc.html`, `design/Stopka.dc.html`, sekcja CTA z `design/Oferta.dc.html` (przedostatnia `<section>`)

**Interfaces:**
- Consumes: `$page`, stałe z `config.php`, `pd_picture()`, klasy CSS z Task 3.
- Produces: `header.php` otwiera `<main>`; `cta.php` zamyka `</main>` i renderuje CTA chyba że `$noCta === true`; `footer.php` ładuje `main.js` i `$pageJs`.

- [ ] **Step 1: `header.php`** (z `Naglowek.dc.html`; `dot` → `<span class="dot"></span>`; linki: aktywna strona → `#top`)

```php
<?php
if (!defined('PD_APP')) { http_response_code(403); exit; }
$navItems = ['home' => ['Strona główna', '/'], 'oferta' => ['Oferta', '/oferta/'], 'galeria' => ['Galeria', '/galeria/'], 'kontakt' => ['Kontakt', '/kontakt/']];
$navHref = fn(string $k): string => $page === $k ? '#top' : $navItems[$k][1];
?>
<header data-nav style="position:sticky;top:0;z-index:40;display:block;background:#161616;border-bottom:3px solid #f5c518;font-family:'Barlow',system-ui,sans-serif;color:#f2f2f2">
  <div style="max-width:1200px;margin:0 auto;padding:10px 20px;display:flex;align-items:center;justify-content:space-between;gap:16px">
    <a href="<?php echo $page === 'home' ? '#top' : '/'; ?>" style="display:flex;align-items:center;gap:10px;flex:none"><img src="/assets/img/logo.png" alt="Logo Pomoc Drogowa Łukasz Rogowski" width="56" height="56" style="width:56px;height:56px;object-fit:contain"><div style="font-family:'Barlow Condensed';font-weight:800;font-size:22px;line-height:.95;color:#f5c518">POMOC DROGOWA<br><span style="font-family:'Barlow';font-weight:600;font-size:11px;letter-spacing:3px;color:#e8342a">ŁUKASZ ROGOWSKI</span></div></a>
    <nav data-navlinks aria-label="Nawigacja główna">
      <?php foreach ($navItems as $k => [$label]): ?>
      <a href="<?php echo $navHref($k); ?>"<?php if ($page === $k) echo ' class="is-on" aria-current="page"'; ?>><?php echo $label; ?></a>
      <?php endforeach; ?>
    </nav>
    <a href="<?php echo $PHONE_HREF; ?>" data-navphone class="hov-red press"><span class="dot"></span><?php echo $PHONE; ?></a>
    <button data-burger aria-label="Menu" aria-expanded="false" aria-controls="mobile-menu"><span></span><span></span><span></span></button>
  </div>
  <div data-menu id="mobile-menu">
    <nav style="display:flex;flex-direction:column;padding:8px 20px" aria-label="Menu mobilne">
      <?php foreach ($navItems as $k => [$label]): ?>
      <a href="<?php echo $navHref($k); ?>" data-menuclose class="m-link<?php if ($page === $k) echo ' is-on'; ?>"><?php echo $label; ?><span style="color:#f5c518;font-size:24px">→</span></a>
      <?php endforeach; ?>
    </nav>
    <div style="padding:20px;display:flex;flex-direction:column;gap:10px">
      <div style="font-size:12px;letter-spacing:3px;font-weight:700;color:#999">CZYNNE 24/7 · PABIANICE I OKOLICE</div>
      <a href="<?php echo $PHONE_HREF; ?>" data-menuclose style="background:#e8342a;color:#fff;font-family:'Barlow Condensed';font-weight:800;font-size:34px;padding:14px 20px;display:flex;align-items:center;justify-content:center;gap:12px"><span class="dot"></span><?php echo $PHONE_SHORT; ?></a>
    </div>
  </div>
</header>
<main>
```

- [ ] **Step 2: `cta.php`** (sekcja „Potrzebujesz pomocy?”; tło = `pd_picture('laweta-noca-pomoc-drogowa-24h-pabianice', ...)` — slug roli `cta-bg` po korekcie z Task 2)

```php
<?php
if (!defined('PD_APP')) { http_response_code(403); exit; }
$noCta = $noCta ?? false;
?>
</main>
<?php if (!$noCta): ?>
  <section aria-labelledby="cta-h" style="position:relative;overflow:hidden;background:#141414">
    <?php echo pd_picture('laweta-noca-pomoc-drogowa-24h-pabianice', '', 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.25', '100vw'); ?>
    <div style="position:relative;max-width:1200px;margin:0 auto;padding:clamp(56px,10vw,88px) 20px;display:flex;flex-direction:column;align-items:center;text-align:center;gap:16px">
      <div style="font-size:13px;letter-spacing:3px;font-weight:700;color:#f5c518">DOSTĘPNI 24 GODZINY NA DOBĘ</div>
      <h2 id="cta-h" style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(44px,6vw,72px);line-height:1">Potrzebujesz pomocy?</h2>
      <p style="margin:0;font-size:18px;color:#ccc;max-width:560px">Zadzwoń o każdej porze dnia i nocy — dojedziemy tam, gdzie nas potrzebujesz.</p>
      <a href="<?php echo $PHONE_HREF; ?>" class="hov-big press" style="font-family:'Barlow Condensed';font-weight:800;font-size:clamp(48px,8vw,88px);line-height:1;color:#fff;background:#111;padding:4px 28px 8px;border:5px solid #f5c518;margin-top:10px;transition:background-color .2s,color .2s,border-color .2s,transform .2s cubic-bezier(.2,.7,.2,1),box-shadow .2s"><?php echo $PHONE; ?></a>
    </div>
  </section>
<?php endif; ?>
```

- [ ] **Step 3: `footer.php`** (ze `Stopka.dc.html`; `bottomRow` → `data-bottomrow`; fab → `data-fab` z dwoma wariantami tekstu)

```php
<?php
if (!defined('PD_APP')) { http_response_code(403); exit; }
$footItems = ['home' => ['Strona główna', '/'], 'oferta' => ['Oferta', '/oferta/'], 'galeria' => ['Galeria', '/galeria/'], 'kontakt' => ['Kontakt', '/kontakt/']];
?>
<footer style="background:#111;border-top:6px solid #f5c518;font-family:'Barlow',system-ui,sans-serif;color:#f2f2f2">
  <div style="max-width:1200px;margin:0 auto;padding:56px 20px 48px;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:40px">
    <div style="display:flex;flex-direction:column;gap:12px"><img src="/assets/img/logo.png" alt="Logo Pomoc Drogowa Łukasz Rogowski" width="96" height="96" loading="lazy" style="width:96px;height:96px;object-fit:contain"><div style="font-family:'Barlow Condensed';font-weight:800;font-size:22px;color:#f5c518;line-height:1">POMOC DROGOWA<br><span style="font-family:'Barlow';font-weight:600;font-size:12px;letter-spacing:3px;color:#e8342a">ŁUKASZ ROGOWSKI</span></div><div style="font-size:14px;color:#999;line-height:1.5">Całodobowa pomoc drogowa dla Pabianic i okolic.</div></div>
    <div style="display:flex;flex-direction:column;gap:10px;font-size:15px"><div style="font-size:12px;letter-spacing:2px;font-weight:700;color:#999">KONTAKT</div><a href="<?php echo $PHONE_HREF; ?>" style="font-weight:800;font-size:22px"><?php echo $PHONE; ?></a><div style="color:#ccc;line-height:1.5"><?php echo $ADDR1; ?><br><?php echo $ADDR2; ?></div><a href="<?php echo $GMAPS_PLACE; ?>" target="_blank" rel="noopener" style="text-decoration:underline">Wizytówka Google →</a></div>
    <div style="display:flex;flex-direction:column;gap:10px;font-size:15px"><div style="font-size:12px;letter-spacing:2px;font-weight:700;color:#999">GODZINY</div><div style="color:#f5c518;font-weight:800;font-size:22px">Czynne 24/7</div><div style="color:#ccc;line-height:1.5">Poniedziałek – Niedziela<br>całą dobę, również w święta</div></div>
    <div style="display:flex;flex-direction:column;gap:10px;font-size:15px"><div style="font-size:12px;letter-spacing:2px;font-weight:700;color:#999">NAWIGACJA</div>
      <?php foreach ($footItems as $k => [$label, $href]): ?>
      <a href="<?php echo $page === $k ? '#top' : $href; ?>" class="hov-white" style="color:#ccc;transition:color .2s"><?php echo $label; ?></a>
      <?php endforeach; ?>
      <a href="<?php echo $FB; ?>" target="_blank" rel="noopener" class="hov-white" style="color:#ccc;transition:color .2s">Facebook</a></div>
  </div>
  <div style="border-top:1px solid #2a2a2a"><div data-bottomrow><span>© <?php echo date('Y'); ?> Pomoc Drogowa Łukasz Rogowski · Pabianice</span><span>Projekt i wykonanie: <a href="https://pozycjonujewizytowke.pl/" target="_blank" rel="noopener" class="hov-text" style="color:#aaa;text-decoration:underline;transition:color .2s">pozycjonujewizytowke.pl</a></span></div></div>
  <a href="<?php echo $PHONE_HREF; ?>" data-fab class="press" aria-label="Zadzwoń 24h: <?php echo $PHONE; ?>"><span class="dot"></span><span class="fab-desk">24H · <?php echo $PHONE_SHORT; ?></span><span class="fab-mob">ZADZWOŃ 24H · <?php echo $PHONE_SHORT; ?></span></a>
</footer>
</div>
<?php $mainJs = __DIR__ . '/../assets/js/main.js'; ?>
<script src="/assets/js/main.js?v=<?php echo filemtime($mainJs); ?>" defer></script>
<?php if (!empty($pageJs)): ?>
<script src="/<?php echo $pageJs; ?>?v=<?php echo filemtime(__DIR__ . '/../' . $pageJs); ?>" defer></script>
<?php endif; ?>
</body>
</html>
```

- [ ] **Step 4: `main.js`**

```js
/* Pomoc Drogowa Pabianice — wspólny skrypt: menu mobilne */
(function () {
  var nav = document.querySelector('[data-nav]');
  if (!nav) return;
  var burger = nav.querySelector('[data-burger]');
  function setMenu(open) {
    nav.classList.toggle('menu-open', open);
    document.body.classList.toggle('menu-open', open);
    if (burger) burger.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  document.addEventListener('click', function (e) {
    var t = e.target;
    if (!t || !t.closest) return;
    if (t.closest('[data-burger]')) { setMenu(!nav.classList.contains('menu-open')); return; }
    if (t.closest('[data-menuclose]')) setMenu(false);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape' || !nav.classList.contains('menu-open')) return;
    setMenu(false);
    if (burger) burger.focus();
  });
  var mq = window.matchMedia('(min-width:900px)');
  var onMq = function (ev) { if (ev.matches) setMenu(false); };
  if (mq.addEventListener) mq.addEventListener('change', onMq); else mq.addListener(onMq);
})();
```

- [ ] **Step 5: Test**

```bash
for f in public_html/partials/*.php; do php -l "$f" | grep -v 'No syntax'; done; node --check public_html/assets/js/main.js
php -S 127.0.0.1:8080 -t public_html tools/router.php >/tmp/php.log 2>&1 & sleep 1
curl -s http://127.0.0.1:8080/nie-ma > /tmp/404.html; kill %1
grep -c 'data-nav\|data-fab\|data-menu' /tmp/404.html; grep -cE '\{\{|<sc-|dc-import|lh3\.google' /tmp/404.html
grep -o '<script src="[^"]*"' /tmp/404.html; grep -c '<main>' /tmp/404.html; grep -c 'Potrzebujesz pomocy' /tmp/404.html
```
Expected: lint OK; 3 trafienia data-*; 0 śladów DC; `main.js?v=<liczba>`; `<main>` 1; CTA 0 (404 ma `$noCta`). Otwórz `http://127.0.0.1:8080/nie-ma` w przeglądarce (lub zrób zrzut przez Playwright, jeśli `npx playwright` dostępne) przy 390 px: burger widoczny, po kliknięciu menu rozwinięte, Escape zamyka, pasek telefonu na dole.

- [ ] **Step 6: Commit** — `git add public_html && git commit -m "Add header, CTA, footer partials and mobile menu"`

---

### Task 5: Strona główna (`index.php`) + `home.js`

**Files:**
- Modify: `public_html/index.php` (zastąp placeholder sekcjami)
- Create: `public_html/assets/js/home.js`
- Source: `design/Strona Glowna 1b v2.dc.html` — wszystko między `<dc-import name="Naglowek"…>` a ostatnią `<section>` CTA (CTA i stopka są w partialach).

**Interfaces:**
- Consumes: `pd_picture()`, klasy `.chip`, `.svc-grid`, `.faq-*`, `[data-leaflet]`.
- Produces: `home.js` czyta `[data-town]` chipy (atrybut = nazwa), `[data-town-label]`, `[data-leaflet]`, `[data-faq]` przyciski z `data-faq="<i>"` + `aria-controls="faq-a<i>"`, panele `[data-faq-wrap="<i>"]`.

Dane (z `renderVals()` designu):
- `svcData` (8 kart, `n` = 01–08, obraz = rola `home-svc-N` z Task 2): 1 Awaria samochodu / „Usterka na drodze, parkingu lub posesji — naprawiamy na miejscu albo holujemy do warsztatu.”; 2 Kolizja lub wypadek / „Zabezpieczamy i usuwamy pojazd, pomagamy w formalnościach.”; 3 Rozładowany akumulator / „Auto nie odpala? Przyjedziemy z urządzeniem rozruchowym i uruchomimy silnik na miejscu.”; 4 Przebita opona / „Awaryjna wymiana koła i serwis opon na miejscu — bez holowania do wulkanizacji.”; 5 Brak paliwa / „Zabrakło paliwa w trasie? Przywieziemy tyle, żebyś spokojnie dojechał do stacji.”; 6 Holowanie i laweta / „Bezpieczny transport lawetą do warsztatu lub domu.”; 7 Transport pojazdów / „Przewóz aut osobowych i dostawczych, także na dalsze trasy.”; 8 Auto zastępcze z OC / „Nie jesteś sprawcą? Auto zastępcze bezpłatnie, rozliczamy się z ubezpieczycielem.”
- `reviews` (3): Jacek A, Jakub G, Mateusz G — teksty verbatim z designu, inicjał = pierwsza litera.
- `faqData` (7 par) verbatim z designu; indeks 0 otwarty.
- `townList`: Pabianice, Łódź, Konstantynów Łódzki, Ksawerów, Rzgów, Dobroń, Łask, Zduńska Wola, Lutomiersk. Aktywne: Pabianice.

- [ ] **Step 1: Przepisz `index.php`** — zachowaj dispatcher i preambułę z Task 3, dodaj przed `include head.php`:

```php
$faqLd = [
  ['Czy pomoc drogowa działa całodobowo?', 'Tak. Dojeżdżamy 24 godziny na dobę, 7 dni w tygodniu — również w weekendy i święta. Wystarczy zadzwonić: +48 517 574 330.'],
  ['Jaki obszar obsługujecie?', 'Pabianice i okolice: Łódź, Konstantynów Łódzki, Ksawerów, Rzgów, Dobroń, Łask, Zduńska Wola i inne. Pomagamy też na trasach S8, S14 i A1.'],
  ['Jak szybko dojedziecie?', 'Czas dojazdu zależy od miejsca zdarzenia — podajemy go od razu w rozmowie. W Pabianicach i najbliższej okolicy zwykle to kilkadziesiąt minut.'],
  ['Ile kosztuje auto zastępcze?', 'Jeśli sprawcą kolizji był ktoś inny, auto zastępcze jest dla Ciebie bezpłatne — rozliczamy się bezpośrednio z ubezpieczycielem sprawcy.'],
  ['Czy da się naprawić auto na miejscu?', 'Często tak — wymiana koła, odpalenie auta czy dowóz paliwa odbywają się na miejscu. Gdy naprawa nie jest możliwa, holujemy lawetą.'],
  ['Auto po awarii jedzie — czy potrzebuję lawety?', 'Nie zawsze, ale jazda z usterką może ją pogłębić. Zadzwoń — ocenimy, czy wystarczy pomoc na miejscu.'],
  ['Co zrobić po kolizji?', 'Zadbaj o bezpieczeństwo, włącz światła awaryjne, ustaw trójkąt i zadzwoń do nas. Zajmiemy się pojazdem i pomożemy w formalnościach.'],
];
$towns = ['Pabianice', 'Łódź', 'Konstantynów Łódzki', 'Ksawerów', 'Rzgów', 'Dobroń', 'Łask', 'Zduńska Wola', 'Lutomiersk'];
$services = [ // [tytuł, opis, slug obrazu (rola home-svc-N z tools/manifest.json)]
  ['Awaria samochodu', 'Usterka na drodze, parkingu lub posesji — naprawiamy na miejscu albo holujemy do warsztatu.', 'naprawa-na-miejscu-mobilny-serwis-pabianice'],
  ['Kolizja lub wypadek', 'Zabezpieczamy i usuwamy pojazd, pomagamy w formalnościach.', 'pomoc-po-kolizji-laweta-pabianice'],
  ['Rozładowany akumulator', 'Auto nie odpala? Przyjedziemy z urządzeniem rozruchowym i uruchomimy silnik na miejscu.', 'awaryjne-odpalanie-rozladowany-akumulator-pabianice'],
  ['Przebita opona', 'Awaryjna wymiana koła i serwis opon na miejscu — bez holowania do wulkanizacji.', 'wymiana-kola-serwis-opon-pabianice'],
  ['Brak paliwa', 'Zabrakło paliwa w trasie? Przywieziemy tyle, żebyś spokojnie dojechał do stacji.', 'dowoz-paliwa-pomoc-drogowa-pabianice'],
  ['Holowanie i laweta', 'Bezpieczny transport lawetą do warsztatu lub domu.', 'auto-na-lawecie-holowanie-pabianice'],
  ['Transport pojazdów', 'Przewóz aut osobowych i dostawczych, także na dalsze trasy.', 'transport-pojazdu-laweta-pabianice'],
  ['Auto zastępcze z OC', 'Nie jesteś sprawcą? Auto zastępcze bezpłatnie, rozliczamy się z ubezpieczycielem.', 'auto-zastepcze-z-oc-sprawcy-pabianice'],
];
$reviews = [
  ['Jacek A', 'Przyjazd na miejsce zdarzenia ekspresowy, szybko i sprawnie. Bez nerwów, w dobrej atmosferze. Po zdarzeniu pomoc w załatwieniu koniecznych formalności. Jednym zdaniem — właściwy człowiek na właściwym miejscu. Uczciwy i bardzo pomocny.'],
  ['Jakub G', 'Profesjonalne podejście, szybki dojazd na miejsce i uprzejma obsługa sprawiły, że stresująca sytuacja stała się o wiele łatwiejsza do zniesienia. Wszystko załatwione sprawnie i bez zbędnych komplikacji.'],
  ['Mateusz G', 'W trudnym momencie mogłem liczyć na szybką i skuteczną pomoc. Laweta przyjechała szybko, a cała usługa przebiegła bez zarzutu. Miło spotkać ludzi, którzy naprawdę znają się na swojej pracy.'],
];
```
Po `include header.php` wstaw sekcje z designu **w tej kolejności**, stosując reguły:
1. HERO: `<section id="top" …>` verbatim; obraz → `pd_picture('hero/<slug home-hero>', 'Laweta z samochodem na drodze ekspresowej', 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.5', '100vw', true)`; `{{ googleReviews }}` → `$GREVIEWS`; przyciski tel z `class="hov-big press"` (wielki numer) — `style-hover`/`style-active` usuń.
2. Pasek `<div style="height:16px;background:repeating-linear-gradient(...)"></div>` verbatim.
3. `#o-firmie` verbatim; obraz → `pd_picture('<slug home-about>', 'Żółta laweta Pomocy Drogowej Łukasz Rogowski', 'width:calc(100% - 12px);aspect-ratio:4/3;object-fit:cover;display:block;border:3px solid #333;box-shadow:12px 12px 0 #f5c518', '(max-width:760px) 100vw, 50vw')`.
4. `#obszar`: chipy → `<?php foreach ($towns as $i => $t): ?><button type="button" class="chip<?php if ($i===0) echo ' is-on'; ?>" data-town="<?php echo $t; ?>" aria-pressed="<?php echo $i===0?'true':'false'; ?>"><?php echo $t; ?></button><?php endforeach; ?>`; iframe → `<div data-leaflet data-map-area style="width:100%;height:100%" role="region" aria-label="Mapa obszaru działania"></div>` wewnątrz kontenera `height:420px;border:3px solid #333;overflow:hidden;background:#2a2a2a`; `{{ town }}` → `<b data-town-label style="color:#f5c518">Pabianice</b>`.
5. `#zakres`: link `Oferta.dc.html` → `/oferta/` z `class="hov-brand hov-lift-flat"`; `<div style="{{ svcGrid }}">` → `<div class="svc-grid">`; pętla `foreach ($services as $i => [$t,$d,$slug])`, `n` = `sprintf('%02d',$i+1)`, obraz `pd_picture($slug, $t, 'width:100%;aspect-ratio:16/10;object-fit:cover;display:block', '(max-width:559px) 100vw, (max-width:999px) 50vw, 25vw')`; tytuł karty jako `<h3>` (z tym samym inline style co `div` w designie + `margin:0`).
6. Sekcja żółta „Auto zastępcze z OC sprawcy” verbatim; obraz `pd_picture('<slug home-oc>', 'Transport samochodu lawetą', 'width:100%;aspect-ratio:4/3;object-fit:cover;display:block;border:4px solid #111', '(max-width:760px) 100vw, 50vw')`; przycisk `class="hov-white hov-lift press"` (design: hover `color:#fff` + lift).
7. `#opinie`: link `$GREVIEWS` z `class="hov-brand hov-lift press"`; pętla `$reviews`, inicjał `mb_substr($a,0,1)`; cytat w `<blockquote style="margin:0">` → wewnątrz `<p style="…">„…”</p>`.
8. `#faq`: pętla `foreach ($faqLd as $i => [$q,$a])`: `<div style="border-top:1px solid #444"><button type="button" class="faq-btn<?php if($i===0) echo ' is-open'; ?>" data-faq="<?php echo $i; ?>" aria-expanded="<?php echo $i===0?'true':'false'; ?>" aria-controls="faq-a<?php echo $i; ?>"><?php echo $q; ?><span class="faq-icon" aria-hidden="true"><i class="h"></i><i class="v"></i></span></button><div class="faq-wrap<?php if($i===0) echo ' is-open'; ?>" data-faq-wrap="<?php echo $i; ?>" id="faq-a<?php echo $i; ?>"><div style="overflow:hidden;min-height:0"><p><?php echo $a; ?></p></div></div></div>`; potem `<div style="border-top:1px solid #444"></div>`.
9. Zakończ: `<?php include __DIR__ . '/partials/cta.php'; include __DIR__ . '/partials/footer.php';`.
Każda `<section>` z `id` dostaje dodatkowo `scroll-margin-top:var(--navh)` w `style`.

- [ ] **Step 2: `home.js`**

```js
/* Pomoc Drogowa Pabianice — strona główna: mapa obszaru (Leaflet, leniwie) + FAQ */
(function () {
  var TOWNS = [
    { name: 'Pabianice', lat: 51.6639859, lon: 19.3535024, hub: true },
    { name: 'Łódź', lat: 51.7728245, lon: 19.478486 },
    { name: 'Konstantynów Łódzki', lat: 51.7572134, lon: 19.3082412 },
    { name: 'Ksawerów', lat: 51.6986, lon: 19.3828 },
    { name: 'Rzgów', lat: 51.6627785, lon: 19.4908887 },
    { name: 'Dobroń', lat: 51.63382, lon: 19.24516 },
    { name: 'Łask', lat: 51.5929947, lon: 19.1334784 },
    { name: 'Zduńska Wola', lat: 51.5949116, lon: 18.9501087 },
    { name: 'Lutomiersk', lat: 51.7550121, lon: 19.2120994 }
  ];
  var map = null, markers = {}, current = 'Pabianice';
  var label = document.querySelector('[data-town-label]');

  function setChips() {
    document.querySelectorAll('[data-town]').forEach(function (b) {
      var on = b.getAttribute('data-town') === current;
      b.classList.toggle('is-on', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
    if (label) label.textContent = current;
    Object.keys(markers).forEach(function (n) {
      var el = markers[n].getElement && markers[n].getElement();
      if (el) el.classList.toggle('is-active', n === current);
    });
  }
  function selectTown(name) {
    var t = TOWNS.filter(function (x) { return x.name === name; })[0];
    if (!t) return;
    current = name; setChips();
    if (map) map.panTo([t.lat, t.lon], { animate: true });
  }
  function renderMap(el) {
    if (map || !window.L) return;
    map = L.map(el, { scrollWheelZoom: false, zoomControl: true, attributionControl: true });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(map);
    var hub = TOWNS[0];
    L.circle([hub.lat, hub.lon], { radius: 25000, color: '#f5c518', weight: 2, dashArray: '6 6', fill: true, fillColor: '#f5c518', fillOpacity: .05 }).addTo(map);
    var bounds = [];
    TOWNS.forEach(function (t) {
      var m = L.marker([t.lat, t.lon], { icon: L.divIcon({ className: 'pd-pin' + (t.hub ? ' is-hub' : ''), html: '<i></i>', iconSize: [t.hub ? 22 : 16, t.hub ? 22 : 16] }), title: t.name, keyboard: true }).addTo(map);
      m.bindTooltip(t.name, { permanent: true, direction: 'top', offset: [0, -6], className: 'pd-label' });
      m.on('click', function () { selectTown(t.name); });
      markers[t.name] = m; bounds.push([t.lat, t.lon]);
    });
    map.fitBounds(bounds, { padding: [30, 30] });
    setChips();
    setTimeout(function () { try { map.invalidateSize(); } catch (e) {} }, 250);
  }
  function loadLeaflet(el) {
    if (window.L) { renderMap(el); return; }
    if (document.querySelector('script[data-leaflet-js]')) return;
    var link = document.createElement('link'); link.rel = 'stylesheet'; link.href = '/assets/vendor/leaflet/leaflet.css'; link.setAttribute('data-leaflet-css', ''); document.head.appendChild(link);
    var s = document.createElement('script'); s.src = '/assets/vendor/leaflet/leaflet.js'; s.setAttribute('data-leaflet-js', ''); s.onload = function () { renderMap(el); }; document.head.appendChild(s);
  }
  var el = document.querySelector('[data-leaflet]');
  if (el) {
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) { entries.forEach(function (en) { if (en.isIntersecting) { io.disconnect(); loadLeaflet(el); } }); }, { rootMargin: '600px 0px' });
      io.observe(el);
    } else loadLeaflet(el);
  }
  document.addEventListener('click', function (e) {
    var t = e.target; if (!t || !t.closest) return;
    var chip = t.closest('[data-town]');
    if (chip) { selectTown(chip.getAttribute('data-town')); return; }
    var fb = t.closest('[data-faq]');
    if (fb) {
      var i = fb.getAttribute('data-faq'), wasOpen = fb.classList.contains('is-open');
      document.querySelectorAll('[data-faq]').forEach(function (b) { b.classList.remove('is-open'); b.setAttribute('aria-expanded', 'false'); });
      document.querySelectorAll('[data-faq-wrap]').forEach(function (w) { w.classList.remove('is-open'); });
      if (!wasOpen) {
        fb.classList.add('is-open'); fb.setAttribute('aria-expanded', 'true');
        var w = document.querySelector('[data-faq-wrap="' + i + '"]'); if (w) w.classList.add('is-open');
      }
    }
  });
})();
```

- [ ] **Step 3: Test**

```bash
php -l public_html/index.php; node --check public_html/assets/js/home.js
php -S 127.0.0.1:8080 -t public_html tools/router.php >/tmp/php.log 2>&1 & sleep 1
curl -s http://127.0.0.1:8080/ > /tmp/home.html; kill %1
grep -cE '\{\{|<sc-|dc-import|lh3\.google|hint-placeholder|style-hover' /tmp/home.html   # 0
grep -o 'id="[a-z-]*"' /tmp/home.html | tr '\n' ' '     # top o-firmie obszar zakres opinie faq faq-a0..6 cta-h mobile-menu
grep -c '<h1' /tmp/home.html; grep -c '<h2' /tmp/home.html; grep -c 'data-faq=' /tmp/home.html; grep -c 'data-town=' /tmp/home.html; grep -c 'svc-grid' /tmp/home.html
grep -o 'src="/assets/img/[^"]*"' /tmp/home.html | sed 's/src="//;s/"//' | while read p; do [ -f "public_html$p" ] || echo "BRAK $p"; done
grep -o '"@type":"[A-Za-z]*"' /tmp/home.html | sort | uniq -c
python3 - <<'EOF'
import re,json,html
s=open('/tmp/home.html',encoding='utf-8').read()
for b in re.findall(r'<script type="application/ld\+json">(.*?)</script>',s,re.S): json.loads(b)
print('JSON-LD ok', len(s)//1024, 'KB')
EOF
```
Expected: 0 śladów DC; h1 = 1; h2 ≥ 6; data-faq = 7; data-town = 9; brak `BRAK`; typy: AutomotiveBusiness, Service, FAQPage, Question ×7, Answer ×7 (bez BreadcrumbList); JSON-LD parsuje się. Wizualnie (Playwright lub przeglądarka, 390/820/1280 px): hero z wielkim numerem, mapa ładuje się po przewinięciu, klik chipa „Łódź” przesuwa mapę i zmienia etykietę, FAQ otwiera jedno pytanie naraz, z wyłączonym JS wszystkie teksty i linki widoczne (Review Focus 3).

- [ ] **Step 4: Commit** — `git add public_html && git commit -m "Add home page sections, area map and FAQ"`

---

### Task 6: Oferta (`oferta.php`)

**Files:**
- Create/overwrite: `public_html/oferta.php`
- Source: `design/Oferta.dc.html`

**Interfaces:** Consumes `pd_picture()`, `.svc-row`, `.hov-*`. Bez JS strony (`$pageJs` nieustawione).

Dane `services` (id, tytuł, opis, 3 punkty, slug obrazu = rola `oferta-<id>` z manifestu):
1. `holowanie` Holowanie i laweta — „Niesprawne auto przewozimy lawetą do warsztatu, domu lub na wskazany parking. Pojazd jest zabezpieczony na całą drogę.” — Samochody osobowe i dostawcze / Auta po awarii, kolizji i wypadku / Trasy lokalne i dalsze
2. `naprawa` Naprawa na miejscu — „Mobilny serwis przyjeżdża do Ciebie. Wiele usterek usuwamy od ręki — bez holowania i czekania na warsztat.” — Diagnoza usterki na miejscu / Drobne naprawy mechaniczne i elektryczne / Holowanie, gdy naprawa na miejscu nie jest możliwa
3. `akumulator` Awaryjne odpalanie — „Rozładowany akumulator to najczęstszy powód, dla którego auto nie odpala — szczególnie zimą. Przyjedziemy i uruchomimy silnik.” — Rozruch z profesjonalnego urządzenia / Sprawdzenie akumulatora i ładowania / Parking, garaż, pobocze — dojedziemy
4. `opony` Wymiana koła i serwis opon — „Przebita opona w trasie? Założymy koło zapasowe lub dojazdowe na miejscu, żebyś mógł bezpiecznie ruszyć dalej.” — Awaryjna wymiana koła / Pomoc przy uszkodzonej feldze lub śrubach / Transport do wulkanizacji, jeśli potrzeba
5. `paliwo` Dowóz paliwa — „Zabrakło paliwa na trasie lub w mieście? Przywieziemy tyle, żebyś spokojnie dojechał do najbliższej stacji.” — Benzyna i olej napędowy / Dojazd na drogi lokalne i ekspresowe / O każdej porze dnia i nocy
6. `kolizja` Pomoc po kolizji i wypadku — „Zabezpieczamy miejsce zdarzenia, usuwamy uszkodzony pojazd i pomagamy przejść przez formalności.” — Usunięcie i transport uszkodzonego auta / Pomoc w formalnościach po zdarzeniu / Auto zastępcze z OC sprawcy
7. `transport` Transport pojazdów — „Przewozimy nie tylko auta po awarii. Kupiłeś samochód w innym mieście albo chcesz przewieźć quada? Zajmiemy się tym.” — Samochody kupione lub sprzedane / Quady i inne pojazdy / Trasy po całej Polsce
8. `oc` Auto zastępcze z OC sprawcy — „Jeśli kolizję spowodował ktoś inny, masz prawo do auta zastępczego na czas naprawy. Załatwimy to za Ciebie.” — Bezpłatnie dla poszkodowanego / Rozliczenie bezpośrednio z ubezpieczycielem / Pomoc w zgłoszeniu szkody

- [ ] **Step 1: Napisz `oferta.php`**

Preambuła:
```php
<?php
define('PD_APP', true);
$page = 'oferta';
$title = 'Oferta: laweta, holowanie, naprawa 24/7 | Pomoc Drogowa Pabianice';
$desc = 'Holowanie i laweta, naprawa na miejscu, awaryjne odpalanie, wymiana koła, dowóz paliwa, pomoc po kolizji, transport pojazdów i auto zastępcze z OC sprawcy. Pabianice, Łódź i okolice, 24/7.';
$path = '/oferta/';
$ogImage = 'assets/img/hero/<slug oferta-hero>.jpg';
$services = [ /* 8 wpisów: ['holowanie', 'Holowanie i laweta', 'opis', ['p1','p2','p3'], 'slug'], … */ ];
include __DIR__ . '/partials/config.php';
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/header.php';
?>
```
(`$title` musi mieć ≤ 60 znaków — powyższy ma 63; użyj `Oferta: laweta, holowanie, naprawa | Pomoc Drogowa Pabianice` = 58.)
Sekcje: (1) HERO `<section id="top" …>` verbatim, obraz → `pd_picture('hero/<slug oferta-hero>', 'Auto na lawecie', 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.4', '100vw', true)`, breadcrumb link → `/`, przycisk tel `class="hov-yellow press"`; (2) pasek; (3) „PRZEJDŹ DO:” pętla `<a href="#<?php echo $id; ?>" class="hov-brand" style="border:1px solid #555;color:#eee;padding:7px 12px;font-size:14px;font-weight:600;transition:background-color .2s,color .2s,border-color .2s"><?php echo $n; ?> <?php echo $t; ?></a>`; (4) kontener artykułów: `foreach ($services as $i => [$id,$t,$d,$b,$slug])` → `<article id="<?php echo $id; ?>" class="svc-row<?php if ($i % 2) echo ' is-rev'; ?>">` (row = `is-rev` dla nieparzystych, jak `i % 2 ? 'row-reverse' : 'row'`), obraz `pd_picture($slug, $t, 'width:100%;aspect-ratio:4/3;object-fit:cover;display:block;border:3px solid #333', '(max-width:819px) 100vw, 50vw')`, numer `sprintf('%02d',$i+1)`, `<h2>` verbatim style, lista `<ul>` z 3 `<li>`, przycisk tel `class="hov-brand hov-lift-flat press"`; (5) „Od telefonu do rozwiązania” verbatim (tytuły kroków jako `<h3 style="margin:0;…">`); (6) `include cta.php; include footer.php`.

- [ ] **Step 2: Test**

```bash
php -l public_html/oferta.php
php -S 127.0.0.1:8080 -t public_html tools/router.php >/tmp/php.log 2>&1 & sleep 1
curl -s -o /tmp/oferta.html -w '%{http_code}\n' http://127.0.0.1:8080/oferta/; kill %1
grep -cE '\{\{|<sc-|dc-import|lh3\.google|style-hover' /tmp/oferta.html; grep -c '<article id=' /tmp/oferta.html; grep -c 'is-rev' /tmp/oferta.html; grep -c '<h1' /tmp/oferta.html
grep -o 'src="/assets/img/[^"]*"' /tmp/oferta.html | sed 's/src="//;s/"//' | while read p; do [ -f "public_html$p" ] || echo "BRAK $p"; done
grep -o '"@type":"BreadcrumbList"' /tmp/oferta.html | wc -l; python3 -c "import re;s=open('/tmp/oferta.html').read();print(len(re.search(r'<title>(.*?)</title>',s).group(1)))"
```
Expected: 200; 0 śladów; 8 artykułów; 4 `is-rev`; 1 h1; brak BRAK; 1 BreadcrumbList; długość tytułu ≤ 60. Wizualnie: na 390 px artykuły w kolumnie (obraz nad tekstem), na 1280 naprzemiennie.

- [ ] **Step 3: Commit** — `git add public_html/oferta.php && git commit -m "Add offer page"`

---

### Task 7: Galeria (`galeria.php`, `build_gallery.py`, `galeria.js`)

**Files:**
- Create: `public_html/galeria.php`, `tools/build_gallery.py`, `public_html/assets/js/galeria.js`
- Source: `design/Galeria.dc.html`

**Interfaces:** `build_gallery.py` podmienia blok między `<!-- GALLERY:START -->` i `<!-- GALLERY:END -->` w `galeria.php`. Kafelki: `<button type="button" class="gal-cell[ is-big]" data-shot="<n>" data-full="/assets/img/<slug>.jpg" aria-label="Powiększ zdjęcie: <alt>"[ hidden]>` (`hidden` dla n ≥ 10); `is-big` dla n ∈ {0, 7}. Przycisk „więcej”: `[data-more]`. Lightbox: `.lb[data-lightbox]` z `[data-lbimg]`, `[data-lbpos]`, `[data-lbprev]`, `[data-lbnext]`, `[data-lbclose]`.

- [ ] **Step 1: `galeria.php`**

```php
<?php
define('PD_APP', true);
$page = 'galeria';
$title = 'Galeria: laweta w akcji | Pomoc Drogowa Pabianice';
$desc = 'Zdjęcia z realizacji: holowanie, transport pojazdów i pomoc na trasie w Pabianicach i okolicy. Pomoc Drogowa Łukasz Rogowski — zadzwoń 24/7: +48 517 574 330.';
$path = '/galeria/';
$ogImage = 'assets/img/hero/<slug galeria-hero (idx 9)>.jpg';
$preloadHero = false;
$pageJs = 'assets/js/galeria.js';
include __DIR__ . '/partials/config.php';
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/header.php';
?>
  <section id="top" style="background:#141414;border-bottom:1px solid #333;scroll-margin-top:var(--navh)">
    … nagłówek verbatim z designu (breadcrumb → "/", link do $GMAPS_PLACE z class="hov-brand hov-lift-flat") …
  </section>
  <div style="height:16px;background:repeating-linear-gradient(135deg,#f5c518 0 22px,#111 22px 44px)"></div>
  <section id="zdjecia" aria-label="Zdjęcia z realizacji" style="max-width:1200px;margin:0 auto;padding:clamp(32px,6vw,56px) 20px clamp(52px,9vw,80px);scroll-margin-top:var(--navh)">
    <div class="gal-grid">
<!-- GALLERY:START -->
<!-- GALLERY:END -->
    </div>
    <div style="display:flex;justify-content:center;margin-top:32px" data-more-wrap>
      <button type="button" data-more class="hov-brand hov-lift-flat press" style="all:unset;cursor:pointer;border:2px solid #f5c518;color:#f5c518;font-weight:800;font-size:17px;padding:14px 28px;transition:background-color .2s,color .2s,transform .2s cubic-bezier(.2,.7,.2,1)">Zobacz więcej zdjęć (<span data-more-count>4</span>)</button>
    </div>
  </section>
<?php include __DIR__ . '/partials/cta.php'; ?>
<?php include __DIR__ . '/partials/footer.php'; ?>
```
**Uwaga:** lightbox musi być wewnątrz głównego `<div>` strony, a `footer.php` go zamyka — dlatego wstaw lightbox **przed** `include cta.php`, zaraz po sekcji zdjęć:
```html
  <div class="lb" data-lightbox role="dialog" aria-modal="true" aria-label="Podgląd zdjęcia" tabindex="-1">
    <img data-lbimg src="" alt="" style="display:none">
    <button type="button" data-lbprev aria-label="Poprzednie" class="hov-brand" style="all:unset;cursor:pointer;position:absolute;left:16px;top:50%;margin-top:-28px;width:56px;height:56px;background:#111;border:2px solid #f5c518;color:#f5c518;font-size:30px;display:flex;align-items:center;justify-content:center;transition:background-color .2s,color .2s">‹</button>
    <button type="button" data-lbnext aria-label="Następne" class="hov-brand" style="all:unset;cursor:pointer;position:absolute;right:16px;top:50%;margin-top:-28px;width:56px;height:56px;background:#111;border:2px solid #f5c518;color:#f5c518;font-size:30px;display:flex;align-items:center;justify-content:center;transition:background-color .2s,color .2s">›</button>
    <button type="button" data-lbclose aria-label="Zamknij" class="hov-border" style="all:unset;cursor:pointer;position:absolute;right:16px;top:16px;width:48px;height:48px;background:#111;border:2px solid #555;color:#fff;font-size:26px;display:flex;align-items:center;justify-content:center;transition:border-color .2s">×</button>
    <div data-lbpos style="position:absolute;left:50%;bottom:20px;transform:translateX(-50%);background:#111;color:#ccc;font-size:14px;font-weight:700;padding:6px 12px"></div>
  </div>
```

- [ ] **Step 2: `tools/build_gallery.py`**

```python
#!/usr/bin/env python3
"""Przepisuje kafelki galerii w public_html/galeria.php z manifest.json + alts.json + variants.json."""
import json, os, html, re
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SP = os.path.join(ROOT, 'tools')
m = json.load(open(SP + '/manifest.json'))
alts = json.load(open(SP + '/alts.json'))
variants = json.load(open(ROOT + '/public_html/assets/img/variants.json'))
gal = sorted(m['items'], key=lambda x: x['gallery_pos'])
BIG = {0, 7}; VISIBLE = 10
SIZES_BIG = '(max-width:699px) 100vw, 50vw'; SIZES = '(max-width:699px) 50vw, 25vw'
def srcset(slug, widths, ext): return ', '.join(f'/assets/img/thumbs/{slug}-{w}.{ext} {w}w' for w in widths)
tiles = []
for n, it in enumerate(gal):
    slug, alt = it['slug'], alts[str(it['idx'])]
    v = variants['thumbs/' + slug]; sizes = SIZES_BIG if n in BIG else SIZES
    hidden = ' hidden' if n >= VISIBLE else ''; big = ' is-big' if n in BIG else ''
    lazy = '' if n < 4 else ' loading="lazy"'
    tiles.append(f'''      <button type="button" class="gal-cell{big}" data-shot="{n}" data-full="/assets/img/{slug}.jpg" aria-label="Powiększ zdjęcie: {html.escape(alt, quote=True)}"{hidden}>
        <picture style="display:contents"><source type="image/avif" srcset="{srcset(slug, v['widths'], 'avif')}" sizes="{sizes}"><source type="image/webp" srcset="{srcset(slug, v['widths'], 'webp')}" sizes="{sizes}"><img src="/assets/img/thumbs/{slug}.jpg" alt="{html.escape(alt, quote=True)}" width="{v['w']}" height="{v['h']}"{lazy} decoding="async"></picture>
      </button>
''')
p = ROOT + '/public_html/galeria.php'
s = open(p, encoding='utf-8').read()
s = re.sub(r'(<!-- GALLERY:START -->\n).*?(<!-- GALLERY:END -->)', lambda mm: mm.group(1) + ''.join(tiles) + mm.group(2), s, flags=re.S)
s = re.sub(r'<span data-more-count>\d+</span>', f'<span data-more-count>{max(0, len(gal) - VISIBLE)}</span>', s)
open(p, 'w', encoding='utf-8').write(s)
print('tiles', len(tiles), 'hidden', max(0, len(gal) - VISIBLE))
```

- [ ] **Step 3: `galeria.js`**

```js
/* Pomoc Drogowa Pabianice — galeria: "zobacz więcej" + lightbox */
(function () {
  var shots = Array.prototype.slice.call(document.querySelectorAll('[data-shot]'));
  var total = shots.length;
  var lb = document.querySelector('[data-lightbox]');
  if (!lb || !total) return;
  var img = lb.querySelector('[data-lbimg]'), pos = lb.querySelector('[data-lbpos]');
  var open = -1, trigger = null;
  function render() {
    var on = open >= 0;
    lb.classList.toggle('is-open', on);
    document.body.classList.toggle('lb-open', on);
    if (on) {
      var tile = shots[open].querySelector('img');
      img.setAttribute('src', shots[open].getAttribute('data-full'));
      img.setAttribute('alt', tile ? tile.getAttribute('alt') : 'Powiększone zdjęcie');
      img.style.display = 'block';
      pos.textContent = (open + 1) + ' / ' + total;
      lb.focus();
    } else {
      img.style.display = 'none'; img.setAttribute('src', ''); img.setAttribute('alt', ''); pos.textContent = '';
      if (trigger) { trigger.focus(); trigger = null; }
    }
  }
  function step(d) { open = (open + d + total) % total; render(); }
  document.addEventListener('click', function (e) {
    var t = e.target; if (!t || !t.closest) return;
    if (t.closest('[data-more]')) {
      shots.forEach(function (s) { s.removeAttribute('hidden'); });
      var w = document.querySelector('[data-more-wrap]'); if (w) w.setAttribute('hidden', '');
      return;
    }
    if (t.closest('[data-lbclose]')) { open = -1; render(); return; }
    if (t.closest('[data-lbprev]')) { step(-1); return; }
    if (t.closest('[data-lbnext]')) { step(1); return; }
    var shot = t.closest('[data-shot]');
    if (shot) { trigger = shot; open = Number(shot.getAttribute('data-shot')); render(); return; }
    if (t === lb) { open = -1; render(); }
  });
  document.addEventListener('keydown', function (e) {
    if (open < 0) return;
    if (e.key === 'Escape') { open = -1; render(); }
    else if (e.key === 'ArrowLeft') step(-1);
    else if (e.key === 'ArrowRight') step(1);
  });
})();
```

- [ ] **Step 4: Uruchom builder i test**

```bash
python3 tools/build_gallery.py && php -l public_html/galeria.php && node --check public_html/assets/js/galeria.js
php -S 127.0.0.1:8080 -t public_html tools/router.php >/tmp/php.log 2>&1 & sleep 1
curl -s -o /tmp/gal.html -w '%{http_code}\n' http://127.0.0.1:8080/galeria/; kill %1
grep -c 'data-shot=' /tmp/gal.html; grep -c ' hidden>' /tmp/gal.html; grep -c 'is-big' /tmp/gal.html; grep -cE '\{\{|<sc-|dc-import|lh3\.google' /tmp/gal.html
grep -o 'data-full="[^"]*"\|src="/assets/img/[^"]*"' /tmp/gal.html | sed 's/.*="//;s/"//' | while read p; do [ -f "public_html$p" ] || echo "BRAK $p"; done
grep -c 'rel="preload" as="image"' /tmp/gal.html
```
Expected: 200; 14 kafelków; 4 `hidden`; 2 `is-big`; 0 śladów; brak BRAK; 0 preloadów hero (`$preloadHero=false`). Wizualnie: siatka 4 kolumny z dwoma dużymi kaflami, klik otwiera lightbox z licznikiem `1 / 14`, strzałki, Escape wraca fokus na kafelek (Review Focus 4), „Zobacz więcej (4)” odsłania resztę.

- [ ] **Step 5: Commit** — `git add public_html tools/build_gallery.py && git commit -m "Add gallery page with lightbox"`

---

### Task 8: Kontakt (`kontakt.php`, `kontakt.js`)

**Files:**
- Create: `public_html/kontakt.php`, `public_html/assets/js/kontakt.js`
- Source: `design/Kontakt.dc.html`

- [ ] **Step 1: `kontakt.php`**

Preambuła: `$page='kontakt'; $title='Kontakt – telefon 24/7: 517 574 330 | Pomoc Drogowa Pabianice'` (59 znaków); `$desc='Zadzwoń: +48 517 574 330, całą dobę. Pomoc Drogowa Łukasz Rogowski, ul. Stanisława Moniuszki 41, 95-200 Pabianice. Dojazd w Pabianicach, Łodzi i okolicy.'`; `$path='/kontakt/'`; `$ogImage='assets/img/hero/<slug kontakt-hero (idx 2)>.jpg'`; `$pageJs='assets/js/kontakt.js'`; `$noCta = true`.
Sekcje verbatim: HERO (`id="top"`, obraz `pd_picture('hero/<slug>', 'Laweta Pomocy Drogowej', 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.35', '100vw', true)`, `{{ dot }}` → `<span class="dot"></span>`, numer `class="hov-big press"`); pasek; 4 karty (telefon `class="hov-dark"`; linki Google `$GMAPS_PLACE`, Facebook `$FB`); sekcja dwukolumnowa: lista 4 punktów + ostrzeżenie verbatim, po prawej mapa: iframe → `<div data-leaflet style="width:100%;height:100%" role="region" aria-label="Mapa dojazdu"></div>` w kontenerze `height:clamp(320px,45vw,480px);border:3px solid #333;overflow:hidden;background:#2a2a2a`, pasek „Wyznacz trasę →” z linkiem `https://www.google.com/maps/dir/?api=1&destination=Stanis%C5%82awa%20Moniuszki%2041%2C%20Pabianice`, chipy obszaru verbatim (`<span>`). Nagłówek „Co warto mieć pod ręką” jako `<h2>`. Zakończenie: `include cta.php` (wyrenderuje tylko `</main>`), `include footer.php`.

- [ ] **Step 2: `kontakt.js`**

```js
/* Pomoc Drogowa Pabianice — kontakt: mapa siedziby (Leaflet, leniwie) */
(function () {
  var HQ = [51.6607527, 19.3441975], map = null;
  function renderMap(el) {
    if (map || !window.L) return;
    map = L.map(el, { scrollWheelZoom: false }).setView(HQ, 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(map);
    L.circle(HQ, { radius: 600, color: '#f5c518', weight: 2, dashArray: '5 6', fill: false, opacity: .9 }).addTo(map);
    L.marker(HQ, { icon: L.divIcon({ className: 'pd-hq', html: '<i></i>', iconSize: [22, 22], iconAnchor: [11, 11] }), title: 'Pomoc Drogowa Łukasz Rogowski — Moniuszki 41' })
      .addTo(map).bindPopup('<b>Pomoc Drogowa Łukasz Rogowski</b><br>ul. Stanisława Moniuszki 41<br>95-200 Pabianice');
    setTimeout(function () { try { map.invalidateSize(); } catch (e) {} }, 250);
  }
  function loadLeaflet(el) {
    if (window.L) { renderMap(el); return; }
    if (document.querySelector('script[data-leaflet-js]')) return;
    var link = document.createElement('link'); link.rel = 'stylesheet'; link.href = '/assets/vendor/leaflet/leaflet.css'; link.setAttribute('data-leaflet-css', ''); document.head.appendChild(link);
    var s = document.createElement('script'); s.src = '/assets/vendor/leaflet/leaflet.js'; s.setAttribute('data-leaflet-js', ''); s.onload = function () { renderMap(el); }; document.head.appendChild(s);
  }
  var el = document.querySelector('[data-leaflet]');
  if (!el) return;
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) { entries.forEach(function (en) { if (en.isIntersecting) { io.disconnect(); loadLeaflet(el); } }); }, { rootMargin: '600px 0px' });
    io.observe(el);
  } else loadLeaflet(el);
})();
```

- [ ] **Step 3: Test**

```bash
php -l public_html/kontakt.php; node --check public_html/assets/js/kontakt.js
php -S 127.0.0.1:8080 -t public_html tools/router.php >/tmp/php.log 2>&1 & sleep 1
curl -s -o /tmp/k.html -w '%{http_code}\n' http://127.0.0.1:8080/kontakt/; kill %1
grep -cE '\{\{|<sc-|dc-import|lh3\.google|maps.google.com/maps\?q' /tmp/k.html; grep -c 'Potrzebujesz pomocy' /tmp/k.html; grep -c 'data-leaflet' /tmp/k.html; grep -c '<h1' /tmp/k.html; grep -c '</main>' /tmp/k.html
grep -o 'src="/assets/img/[^"]*"' /tmp/k.html | sed 's/src="//;s/"//' | while read p; do [ -f "public_html$p" ] || echo "BRAK $p"; done
```
Expected: 200; 0 śladów (w tym brak iframe Google); 0 CTA; 1 data-leaflet; 1 h1; 1 `</main>`; brak BRAK. Wizualnie: mapa z żółtym markerem na Moniuszki 41, popup po kliknięciu, z wyłączonym JS ciemny kontener mapy bez błędów (Review Focus 3).

- [ ] **Step 4: Commit** — `git add public_html && git commit -m "Add contact page with HQ map"`

---

### Task 9: Dokumentacja, llms.txt, workflow produkcyjny

**Files:**
- Create: `public_html/llms.txt`, `.github/workflows/deploy.yml`, `CONVENTIONS.md`, `DEPLOY.md`, `README.md`, `tools/README.md`
- Delete: `tools/fonts.css` (już wklejony do `main.css`) — usuń i dopisz do `.gitignore`? Nie: zostaw w repo jako artefakt generatora (mały) — decyzja: **zostaw**.

- [ ] **Step 1: `public_html/llms.txt`**

```
# Pomoc Drogowa Łukasz Rogowski – Pabianice

> Całodobowa pomoc drogowa w Pabianicach, Łodzi i okolicach: holowanie i laweta, naprawa na miejscu, awaryjne odpalanie, wymiana koła, dowóz paliwa, pomoc po kolizji, transport pojazdów, bezpłatne auto zastępcze z OC sprawcy. Telefon 24/7: +48 517 574 330.

- Siedziba: ul. Stanisława Moniuszki 41, 95-200 Pabianice
- Godziny: całą dobę, 7 dni w tygodniu, również w święta
- Obszar: Pabianice, Łódź, Konstantynów Łódzki, Ksawerów, Rzgów, Dobroń, Łask, Zduńska Wola, Lutomiersk; trasy S8, S14, A1, DK71
- Opinie Google: 5.0 (82 opinie)

## Strony

- [Strona główna](https://www.pomocdrogowa-pabianice.pl/): o firmie, obszar działania, zakres pomocy, opinie, FAQ
- [Oferta](https://www.pomocdrogowa-pabianice.pl/oferta/): 8 usług ze szczegółami
- [Galeria](https://www.pomocdrogowa-pabianice.pl/galeria/): zdjęcia z realizacji
- [Kontakt](https://www.pomocdrogowa-pabianice.pl/kontakt/): telefon, adres, mapa dojazdu
```

- [ ] **Step 2: `.github/workflows/deploy.yml`** — skopiuj `/Users/adamfabis/Desktop/Prywatne/tuszyn/.github/workflows/deploy.yml` i zamień wszystkie `pomocdrogowa-tuszyn.pl` na `pomocdrogowa-pabianice.pl` oraz komentarz o certyfikacie zostaw. Dodaj na górze komentarz: `# Aktywny po dodaniu sekretów FTP_SERVER / FTP_USERNAME / FTP_PASSWORD w GitHub → Settings → Environments → production.`

Run: `grep -c pabianice .github/workflows/deploy.yml; grep -c tuszyn .github/workflows/deploy.yml` → `≥2`, `0`.

- [ ] **Step 3: `CONVENTIONS.md`** — napisz w oparciu o ten plan: struktura (Mapa plików), szkielet strony (Task 3 Interfaces), reguły konwersji designu (Global Constraints), klasy CSS i atrybuty `data-*` (Task 3), `pd_picture()`, pipeline obrazów (Task 2), tabela metadanych per strona (tytuł/description/ogImage z Task 5–8), definicja ukończenia (§13 specu). Krótko, po polsku, ≤ 200 linii.

- [ ] **Step 4: `DEPLOY.md`**

Treść: (1) Staging: `tools/deploy_staging.sh` (Task 11) — co robi, jak powtórzyć, że subdomenę `patecwariatec.byst.re` trzeba usunąć ręcznie w `https://mikr.us/panel/?a=domain`; (2) Produkcja SEOhost: dodać sekrety FTP w GitHub, DNS na SEOhost, SSL, pierwszy push na `main` wgrywa `public_html/`; (3) noindex automatyczny — produkcja rozpoznawana po hoście `www.pomocdrogowa-pabianice.pl`, nic nie przełączać; (4) weryfikacja po wdrożeniu: `curl -I https://www.pomocdrogowa-pabianice.pl/` → 200 bez `X-Robots-Tag`, `view-source` bez `noindex`, `/robots.txt` z `Allow: /`, `/sitemap.xml`, Rich Results Test, `http://` i apex → 301 na `https://www`, LiteSpeed purge; (5) Search Console: zgłosić sitemapę.

- [ ] **Step 5: `README.md`** (zastąp jednolinijkowy z GitHub-owej instrukcji) i `tools/README.md` (tabela skryptów: `fetch_fonts.py`, `fetch_google_photos.py`, `build_images.py`, `build_gallery.py`, `router.php`, `deploy_staging.sh`; kolejność uruchamiania przy zmianie zdjęć).

- [ ] **Step 6: Commit** — `git add . && git commit -m "Add docs, llms.txt and production deploy workflow"`

---

### Task 10: Weryfikacja końcowa (DoD) + Lighthouse

**Files:** brak nowych (poprawki w istniejących, jeśli testy coś wykażą).

- [ ] **Step 1: Pełny skan śladów i linków**

```bash
grep -rnE '\{\{|\}\}|<sc-|<x-dc|<helmet|hint-placeholder|support\.js|fonts\.googleapis|lh3\.googleusercontent|dc-import|_ds_bundle|unpkg' public_html && echo 'ŚLADY!' || echo 'czysto'
for f in public_html/*.php public_html/partials/*.php; do php -l "$f" | grep -v 'No syntax'; done
for j in public_html/assets/js/*.js; do node --check "$j"; done
php -S 127.0.0.1:8080 -t public_html tools/router.php >/tmp/php.log 2>&1 & sleep 1
for p in "" oferta/ galeria/ kontakt/; do curl -s "http://127.0.0.1:8080/$p" > "/tmp/p_${p%/}.html"; done
cat /tmp/p_*.html | grep -o '\(src\|href\|data-full\)="/assets/[^"?]*' | sed 's/.*="//' | sort -u | while read p; do [ -f "public_html$p" ] || echo "BRAK $p"; done
cat /tmp/p_*.html | grep -o 'srcset="[^"]*"' | tr ',' '\n' | grep -o '/assets/[^ ]*' | sort -u | while read p; do [ -f "public_html$p" ] || echo "BRAK $p"; done
for f in /tmp/p_*.html; do printf '%s h1=%s title=%s\n' "$f" "$(grep -c '<h1' $f)" "$(python3 -c "import re,sys;print(len(re.search(r'<title>(.*?)</title>',open(sys.argv[1]).read()).group(1)))" $f)"; done
```
Expected: `czysto`, brak `BRAK`, każdy plik h1=1, title ≤ 60. Serwer zostaw uruchomiony do Step 2.

- [ ] **Step 2: Lighthouse** (jeśli `npx --yes lighthouse --version` działa i Chrome jest zainstalowany)

```bash
for p in "" oferta/ galeria/ kontakt/; do npx --yes lighthouse "http://127.0.0.1:8080/$p" --only-categories=performance,accessibility,best-practices,seo --form-factor=mobile --screenEmulation.mobile --quiet --chrome-flags="--headless=new" --output=json --output-path="/tmp/lh_${p%/}.json" >/dev/null 2>&1; python3 -c "import json,sys;d=json.load(open(sys.argv[1]));print(sys.argv[1],{k:round(v['score']*100) for k,v in d['categories'].items()})" "/tmp/lh_${p%/}.json"; done
kill %1
```
Expected: Performance ≥ 95, SEO 100, Best Practices 100, Accessibility ≥ 95 na każdej stronie. Uwaga: lokalny `php -S` nie kompresuje statycznych plików ani nie ustawia cache — jeśli jedyne uwagi Lighthouse dotyczą `uses-text-compression`/`uses-long-cache-ttl` dla `.js/.css`, to jest artefakt środowiska (Apache/nginx to obsługują). Wszystkie inne uwagi popraw (np. kontrast, brak `alt`, CLS przez brak `width/height`). Jeśli Lighthouse niedostępny — zapisz to jawnie w raporcie tasku i przejdź dalej.

- [ ] **Step 3: Zrzuty ekranu** (jeśli dostępny Playwright: `npx --yes playwright screenshot --viewport-size=390,844 --full-page http://127.0.0.1:8080/ /tmp/home-390.png` itd. dla 4 stron × 390/1280) — obejrzyj narzędziem `Read` i porównaj z designem (struktura sekcji, kolory, wielki numer w hero, kafelki, stopka, pasek telefonu na dole na mobile). Popraw rozjazdy w `main.css`.

- [ ] **Step 4: Commit poprawek** — `git add -A && git commit -m "Verification fixes"` (tylko jeśli coś zmieniono).

---

### Task 11: Deploy na staging Mikrus

**Files:**
- Create: `tools/deploy_staging.sh`

**Interfaces:** Consumes runbook `auto-deploy.md` (SSH: `-i ~/.ssh/wp-deploy-mikrus -p 10198 root@tadek198.mikrus.xyz`, helper `/srv/wp-deploy/server-helper.sh`). Slug `pomocdrogowa-pabianice`, subdomena `pomoc-pabianice.byst.re`.

- [ ] **Step 1: `tools/deploy_staging.sh`**

```bash
#!/usr/bin/env bash
# Deploy statycznego PHP na staging Mikrus (runbook: auto-deploy.md, zaadaptowany: bez WordPressa, bez bazy).
set -euo pipefail
SLUG=pomocdrogowa-pabianice
SUBDOMAIN=pomoc-pabianice.byst.re
FREE_SLUG=${FREE_SLUG:-patecwariatec}   # staging do usunięcia, żeby zwolnić port (tylko gdy brak wolnych portów)
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SSH="ssh -i $HOME/.ssh/wp-deploy-mikrus -p 10198 -o StrictHostKeyChecking=accept-new root@tadek198.mikrus.xyz"
SCP="scp -i $HOME/.ssh/wp-deploy-mikrus -P 10198 -o StrictHostKeyChecking=accept-new"
H=/srv/wp-deploy/server-helper.sh
EMAIL=$(git -C "$ROOT" config user.email || echo "$USER@$(hostname)")

[ -f "$HOME/.ssh/wp-deploy-mikrus" ] || { echo "Brak klucza ~/.ssh/wp-deploy-mikrus (patrz auto-deploy.md)"; exit 1; }
$SSH "test -f $H" || { echo "Serwer wymaga bootstrapu (auto-deploy.md)"; exit 1; }

if ! $SSH "$H exists $SLUG"; then
  if ! PORT=$($SSH "$H port-free" 2>/dev/null); then
    echo "Brak wolnych portów — usuwam staging '$FREE_SLUG' (z backupem)"
    $SSH "$H remove $FREE_SLUG --backup" || true
    echo "UWAGA: wpis subdomeny '$FREE_SLUG' usuń ręcznie: https://mikr.us/panel/?a=domain"
    PORT=$($SSH "$H port-free")
  fi
  echo "Tworzę $SLUG na porcie $PORT ($SUBDOMAIN)"
  ENTRY=$($SSH "$H create $SLUG $PORT '$EMAIL' '$SUBDOMAIN'")
  echo "$ENTRY"
fi

TMP=$(mktemp -d)
tar czf "$TMP/files.tar.gz" --exclude='.DS_Store' -C "$ROOT/public_html" .
$SSH "mkdir -p /tmp/wp-deploy-incoming/$SLUG"
$SCP "$TMP/files.tar.gz" "root@tadek198.mikrus.xyz:/tmp/wp-deploy-incoming/$SLUG/"
$SSH "set -e; D=/srv/sites/$SLUG; mkdir -p \$D; find \$D -mindepth 1 -maxdepth 1 ! -name '.ftp-deploy-sync-state.json' -exec rm -rf {} +; tar xzf /tmp/wp-deploy-incoming/$SLUG/files.tar.gz -C \$D; chown -R www-data:www-data \$D; find \$D -type d -exec chmod 755 {} +; find \$D -type f -exec chmod 644 {} +; rm -rf /tmp/wp-deploy-incoming/$SLUG"
rm -rf "$TMP"

URL="https://$SUBDOMAIN"
sleep 5
for u in / /oferta/ /galeria/ /kontakt/ /robots.txt /sitemap.xml /nie-ma; do
  printf '%-14s %s\n' "$u" "$(curl -s -o /dev/null -w '%{http_code}' "$URL$u")"
done
curl -sI "$URL/" | grep -i 'x-robots-tag' || echo "UWAGA: brak X-Robots-Tag"
echo "✓ Staging: $URL"
```

- [ ] **Step 2: Uruchom**

Run: `chmod +x tools/deploy_staging.sh && tools/deploy_staging.sh`
Expected: usunięcie `patecwariatec` z `backup_path: /srv/wp-deploy/backups/patecwariatec-<ts>.tar.gz`, `create` zwraca JSON z `"url": "https://pomoc-pabianice.byst.re"`, kody: `/` 200, `/oferta/` 200, `/galeria/` 200, `/kontakt/` 200, `/robots.txt` 200, `/sitemap.xml` 200, `/nie-ma` 404; nagłówek `x-robots-tag: noindex, nofollow`. Jeśli `create` zgłosi `warning: subdomena … już istnieje` — kontynuuj. Jeśli strona zwraca 502: `$SSH "systemctl status php8.3-fpm; nginx -t; tail -20 /var/log/nginx/pomocdrogowa-pabianice.error.log"`.

Dodatkowo: `curl -s https://pomoc-pabianice.byst.re/robots.txt` → `Disallow: /`; `curl -s https://pomoc-pabianice.byst.re/ | grep -c noindex` → 1; `curl -sI https://pomoc-pabianice.byst.re/oferta.php | head -1` → 301 (nginx `location ~ \.php$` wykona `oferta.php` bezpośrednio z kodem 200 — to akceptowalne na stagingu; zanotuj w DEPLOY.md, że 301 z `.php` działa tylko na Apache).

- [ ] **Step 3: Commit + push**

```bash
git add tools/deploy_staging.sh DEPLOY.md
git commit -m "Add staging deploy script for Mikrus"
git push -u origin main
```
Expected: push OK (repo `adam-fabis/pomocdrogowa-pabianice`). Jeśli remote ma już commit `first commit` z README, wykonaj `git pull --rebase origin main` (konflikt w README.md rozwiąż na rzecz lokalnej wersji) i push ponownie.

---

## Self-review (wykonany przez planującego)

- **Spec coverage:** §3 struktura → Task 1/3/9; §4 partiale → Task 3/4; §5 strony → Task 5–8; §6 routing/noindex → Task 3 (+ test hostów); §7 SEO (JSON-LD, FAQPage, breadcrumb) → Task 3/5; §8 wydajność (fonty, picture, defer, lazy Leaflet, htaccess) → Task 1/3/4; §9 zdjęcia → Task 2/7; §10 mapy → Task 5/8; §11 JS → Task 4/5/7/8; §12 deploy → Task 9/11; §13 DoD → Task 10/11.
- **Placeholder scan:** `TODO-TASK5` w Task 3 jest jawnie zastępowany w Task 5; „…verbatim z designu” odnosi się do konkretnego pliku i konkretnej sekcji z podanymi regułami; slugi oznaczone `<slug …>` są rozwiązywane przez `tools/manifest.json` (rola) — wykonawca ma podane role.
- **Type consistency:** `pd_picture($key,$alt,$imgStyle,$sizes,$eager,$extra)` używane identycznie w Task 4–8; atrybuty `data-town`, `data-town-label`, `data-faq`, `data-faq-wrap`, `data-shot`, `data-full`, `data-more`, `data-lightbox`, `data-leaflet`, `data-nav`, `data-burger`, `data-menu`, `data-menuclose`, `data-fab`, `data-bottomrow` spójne między PHP, CSS i JS; klasy `.is-on`, `.is-open`, `.is-big`, `.is-rev`, `.menu-open`, `.lb-open` spójne.
- **Review Focus:** 1 → Task 3 Step 8; 2 → Task 3 Step 8; 3 → Task 5 Step 3, Task 8 Step 3; 4 → Task 4 Step 5, Task 5 (aria w FAQ), Task 7 Step 4; 5 → Task 2 Step 6.
