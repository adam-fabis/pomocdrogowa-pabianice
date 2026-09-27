# tools/ — narzędzia (nie wgrywać na serwer)

Wymagania: PHP 8.x, Python 3 + Pillow (z AVIF), Node (dla zrzutów: Playwright), curl, ssh/scp (staging).

| skrypt | co robi |
|---|---|
| `fetch_fonts.py` | pobiera Barlow + Barlow Condensed (latin, latin-ext) jako woff2 do `public_html/assets/fonts/` i generuje `fonts.css` (blok `@font-face`, wklejony na początek `main.css`) |
| `fetch_google_photos.py` | pobiera zdjęcia z wizytówki Google (ID z designu) do `zrodla/google/NN-<slug>.jpg`; slugi SEO w liście `PHOTOS` (bez zdjęć starej białej lawety) |
| `prepare_client_photos.py` | kopiuje zdjęcia od klienta (`poprawki i materiały/`, poza gitem) do `zrodla/klient/NN-<slug>.jpg`, rozmywa tablice klientów |
| `build_images.py` | **źródło prawdy ról zdjęć** (`ROLES`: idx → miejsca na stronie; `GALLERY_ORDER`: kolejność w galerii). Generuje `public_html/assets/img/<slug>.jpg` + AVIF/WebP, `thumbs/`, `hero/`, `variants.json`, `roles.json` (rola → slug, czytane przez `pd_slug()`), `manifest.json` |
| `alts.json` | idx → alt zdjęcia (galeria) |
| `build_gallery.py` | przepisuje kafelki między `<!-- GALLERY:START -->` i `<!-- GALLERY:END -->` w `galeria.php`; `BIG`/`VISIBLE` = duże kafelki i liczba widocznych |
| `router.php` | podgląd lokalny: `php -S 127.0.0.1:8080 -t public_html tools/router.php` (emuluje nginx `try_files` → `index.php`) |
| `check.sh` | smoke test: `tools/check.sh [scaffold\|images\|core\|partials\|home\|oferta\|galeria\|kontakt\|all]` |
| `shots.mjs` | zrzuty 390/1280 px (wymaga `playwright` w katalogu uruchomienia, np. `.superpowers/pw`) |
| `deploy_staging.sh` | deploy na Mikrus (`DEPLOY.md`) |

## Zmiana zdjęć

1. Nowe źródła do `zrodla/klient/` jako `NN-<slug>.jpg` (dopisz do `PHOTOS` w `prepare_client_photos.py` i uruchom); NN unikalne w obu katalogach.
2. Przypisz role w `ROLES` i pozycję w `GALLERY_ORDER` (`build_images.py`), popraw `alts.json`, `BIG`/`VISIBLE` (`build_gallery.py`)
   i liczby zdjęć w `check.sh` (sekcje `images`, `galeria`).
   Brakujące zdjęcie (rola bez pliku): usuń rolę z `ROLES`, dopisz do `$PD_PENDING` w `partials/config.php` (zaślepka
   „Zdjęcie wkrótce”) i zmniejsz liczbę ról w `check.sh`; gdy zdjęcie przyjdzie — odwrotnie.
3. `python3 tools/build_images.py && python3 tools/build_gallery.py`
4. `tools/check.sh all`

Zdjęcia z wizytówki Google mają max 1080 px (portrety 810 px); zdjęcia od klienta ~2048 px. Zdjęcia ze starą białą lawetą
(klient nie ma jej od ~2024) są w `zrodla/archiwum-biala-laweta/` i nie trafiają na stronę. `zrodla/` jest poza gitem.
