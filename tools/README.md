# tools/ — narzędzia (nie wgrywać na serwer)

Wymagania: PHP 8.x, Python 3 + Pillow (z AVIF), Node (dla zrzutów: Playwright), curl, ssh/scp (staging).

| skrypt | co robi |
|---|---|
| `fetch_fonts.py` | pobiera Barlow + Barlow Condensed (latin, latin-ext) jako woff2 do `public_html/assets/fonts/` i generuje `fonts.css` (blok `@font-face`, wklejony na początek `main.css`) |
| `fetch_google_photos.py` | pobiera 14 zdjęć z wizytówki Google (ID z designu) do `zrodla/google/NN-<slug>.jpg`; slugi SEO w liście `PHOTOS` |
| `build_images.py` | **źródło prawdy ról zdjęć** (`ROLES`: idx → miejsca na stronie). Generuje `public_html/assets/img/<slug>.jpg` + AVIF/WebP, `thumbs/`, `hero/`, `variants.json`, `roles.json` (rola → slug, czytane przez `pd_slug()`), `manifest.json` |
| `alts.json` | idx → alt zdjęcia (galeria) |
| `build_gallery.py` | przepisuje kafelki między `<!-- GALLERY:START -->` i `<!-- GALLERY:END -->` w `galeria.php` |
| `router.php` | podgląd lokalny: `php -S 127.0.0.1:8080 -t public_html tools/router.php` (emuluje nginx `try_files` → `index.php`) |
| `check.sh` | smoke test: `tools/check.sh [scaffold\|images\|core\|partials\|home\|oferta\|galeria\|kontakt\|all]` |
| `shots.mjs` | zrzuty 390/1280 px (wymaga `playwright` w katalogu uruchomienia, np. `.superpowers/pw`) |
| `deploy_staging.sh` | deploy na Mikrus (`DEPLOY.md`) |

## Zmiana zdjęć

1. Nowe źródła do `zrodla/google/` jako `NN-<slug>.jpg` (lub dopisz do `PHOTOS` w `fetch_google_photos.py` i uruchom).
2. Przypisz role w `ROLES` (`build_images.py`), popraw `alts.json`.
3. `python3 tools/build_images.py && python3 tools/build_gallery.py`
4. `tools/check.sh all`

Zdjęcia z wizytówki Google mają max 1080 px (portrety 810 px) — hero na dużych ekranach jest miękkie. Gdy klient dostarczy
zdjęcia w wyższej rozdzielczości, podmień pliki w `zrodla/google/` (te same nazwy) i uruchom `build_images.py`.
