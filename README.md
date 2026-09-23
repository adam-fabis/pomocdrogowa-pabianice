# pomocdrogowa-pabianice

Strona www.pomocdrogowa-pabianice.pl (Pomoc Drogowa Łukasz Rogowski) — PHP bez frameworka, dark design z Claude Design.

- `public_html/` — kod strony (jedyne, co idzie na serwer)
- `design/` — źródła designu (`*.dc.html`) i logo
- `tools/` — skrypty zdjęć, fontów, galerii, podglądu, testów i deployu na staging (`tools/README.md`)
- `docs/superpowers/` — spec i plan implementacji
- `CONVENTIONS.md` — konwencje kodu, `DEPLOY.md` — staging i produkcja

Podgląd lokalny: `php -S 127.0.0.1:8080 -t public_html tools/router.php`
Testy: `tools/check.sh all`
Staging: `tools/deploy_staging.sh` → https://pomocdrogowa-pabianice.byst.re/ (noindex)
