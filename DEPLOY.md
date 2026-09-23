# DEPLOY.md — staging (Mikrus) i produkcja (SEOhost)

Środowisko jest rozpoznawane **po hoście** (`partials/config.php`): tylko `www.pomocdrogowa-pabianice.pl` jest
indeksowane. Staging, podgląd lokalny i tymczasowy adres hostingu dostają `noindex` (meta + `X-Robots-Tag`) i
`robots.txt: Disallow: /`. Niczego nie trzeba przełączać ręcznie przed wdrożeniem.

## Staging — https://pomoc-pabianice.byst.re/

Subdomena jest krótsza niż planowana `pomocdrogowa-pabianice.byst.re`: API Mikrusa odrzuca etykiety dłuższe niż ~20 znaków
(`Niepoprawna nazwa domeny`). Slug stagingu na serwerze to nadal `pomocdrogowa-pabianice`.

Serwer: Mikrus `tadek198.mikrus.xyz` (runbook `auto-deploy.md`, zaadaptowany do statycznego PHP: bez WordPressa i bazy).
Klucz SSH `~/.ssh/wp-deploy-mikrus` (sekcja „Setup dla nowego użytkownika” w `auto-deploy.md`).

```bash
tools/deploy_staging.sh
```

Skrypt: sprawdza helper na serwerze → jeśli slug `pomocdrogowa-pabianice` nie istnieje, bierze wolny port
(gdy brak — usuwa staging `patecwariatec` z backupem `/srv/wp-deploy/backups/`) i tworzy stronę z subdomeną
`pomoc-pabianice.byst.re` → pakuje `public_html/` (tar), wgrywa scp, rozpakowuje do `/srv/sites/pomocdrogowa-pabianice`
(uprawnienia `www-data`, 755/644) → sprawdza kody HTTP i nagłówek `x-robots-tag`.

Kolejne deploye = ponowne uruchomienie (nadpisuje pliki). Wpis subdomeny `patecwariatec.byst.re` trzeba usunąć ręcznie
w https://mikr.us/panel/?a=domain (API Mikrusa nie usuwa subdomen).

Na nginx `.htaccess` nie działa — routing robi dispatcher w `index.php`. Różnica vs produkcja: `/oferta.php` na stagingu
zwraca 200 (nginx wykonuje PHP bezpośrednio), na Apache 301 → `/oferta/`.

## Produkcja — https://www.pomocdrogowa-pabianice.pl/

1. **DNS/hosting SEOhost**: domena wskazuje na konto, SSL aktywny (Let's Encrypt w panelu), docroot = `public_html` domeny.
2. **Sekrety GitHub**: repo → Settings → Environments → `production` → `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`
   (konto FTP zamknięte w docroot domeny). Workflow `.github/workflows/deploy.yml` wgrywa `public_html/` przez FTPS przy każdym
   pushu na `main` (3 próby — SEOhost zrywa połączenia; `security: loose` bo certyfikat FTP SEOhost bywa wygasły).
   Pierwsze uruchomienie wgrywa wszystko (~12 MB), kolejne tylko zmiany (`.ftp-deploy-sync-state.json` na serwerze).
3. **Po pierwszym wdrożeniu**:
   - `curl -I https://www.pomocdrogowa-pabianice.pl/` → `200`, **bez** `X-Robots-Tag`; `view-source:` bez `noindex`.
   - `https://www.pomocdrogowa-pabianice.pl/robots.txt` → `Allow: /` + `Sitemap:`; `/sitemap.xml` → 4 adresy.
   - `http://pomocdrogowa-pabianice.pl/` i `http://www…` → 301 na `https://www…` (reguła w `.htaccess`).
   - `/oferta`, `/oferta.php` → 301 na `/oferta/`; `/nie-ma` → 404.
   - Rich Results Test: `AutomotiveBusiness`, `Service`, `BreadcrumbList`, `FAQPage` bez błędów.
   - LiteSpeed: `.htaccess` ma `CacheLookup off`, `config.php` wysyła `X-LiteSpeed-Purge: *`; w razie starej wersji — panel →
     LiteSpeed Web Cache Manager → Flush All.
4. **Search Console**: dodać domenę (weryfikacja przez DNS lub meta tag do dodania w `head.php`), zgłosić
   `https://www.pomocdrogowa-pabianice.pl/sitemap.xml`.
5. Jeśli pod domeną była wcześniej inna strona: dodać przekierowania 301/410 starych adresów w `.htaccess` (wzór: projekt Tuszyn).

## Co jest na serwerze

Tylko zawartość `public_html/`. Nie wgrywać: `tools/`, `design/`, `zrodla/`, `docs/`, `.superpowers/`, `auto-deploy.md`.
