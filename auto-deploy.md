# auto-deploy.md — instrukcja dla Claude'a do wrzucania WordPressa na staging

**Dla użytkownika:** wrzuć ten plik do katalogu z projektem WordPress. Otwórz Claude Code w tym katalogu i napisz **"deploy"** (lub "wrzuć na staging", "wgraj"). Claude przeczyta tę instrukcję i poprowadzi Cię przez resztę.

**Dla Claude'a (jeśli czytasz to jako runbook):** Ten plik jest twoim runbookiem. Czytaj sekcję "Instrukcje dla Claude" poniżej i wykonuj kroki dokładnie jak opisano. Pytaj usera tylko o rzeczy które są wprost zaznaczone jako "Pytanie do usera" — całą resztę wykonuj autonomicznie.

---

## Setup dla nowego użytkownika w zespole

**To zrobisz raz w życiu, zajmuje ~1 minutę.**

1. Pobierz plik `wp-deploy-mikrus` (klucz prywatny SSH) z **menedżera haseł zespołu** (1Password / Bitwarden — pozycja "wp-deploy / Mikrus tadek198 SSH key")
2. Wrzuć go do `~/.ssh/wp-deploy-mikrus` (jeśli przez Findera — Cmd+Shift+G w Finderze → `~/.ssh`)
3. Otwórz terminal i ustaw uprawnienia: `chmod 600 ~/.ssh/wp-deploy-mikrus`

Gotowe. Możesz teraz wrzucać `auto-deploy.md` do dowolnego projektu WordPress i mówić Claude "deploy".

## Dane serwera (Mikrus testowy)

```
Host SSH:      tadek198.mikrus.xyz
Port SSH:      10198
User:          root
Auth:          klucz SSH ~/.ssh/wp-deploy-mikrus
Serwer fiz.:   srv66  (subdomeny: srv66-PORT.wykr.es)
IPv6:          2a01:4f9:4a:1a51::198
Panel:         https://mikr.us/panel
```

**Dla Claude'a — wzorce wywołań SSH/SCP:**

```bash
# Wzorzec wywołania SSH:
ssh -i ~/.ssh/wp-deploy-mikrus -p 10198 -o StrictHostKeyChecking=accept-new \
  root@tadek198.mikrus.xyz "<komenda>"

# Wzorzec SCP:
scp -i ~/.ssh/wp-deploy-mikrus -P 10198 -o StrictHostKeyChecking=accept-new \
  <local-path> root@tadek198.mikrus.xyz:<remote-path>
```

W przykładach niżej dla czytelności piszę skróconą formę `ssh ...` / `scp ...`, ale zawsze dodajesz pełne flagi: `-i ~/.ssh/wp-deploy-mikrus -p 10198 -o StrictHostKeyChecking=accept-new`.

**Jeśli klucz nie istnieje** (`ls ~/.ssh/wp-deploy-mikrus` zwraca błąd) — zatrzymaj się i powiedz userowi: "Brakuje klucza SSH. Wykonaj sekcję 'Setup dla nowego użytkownika w zespole' z `auto-deploy.md`, potem powiedz mi 'gotowe'."

---

## Instrukcje dla Claude — co robić gdy user napisze "deploy"

### Krok 0: Sprawdź czy serwer jest gotowy (pierwsza komenda kiedykolwiek)

```bash
ssh -i ~/.ssh/wp-deploy-mikrus -p 10198 root@tadek198.mikrus.xyz \
  "test -f /srv/wp-deploy/server-helper.sh && echo READY || echo NEEDS_BOOTSTRAP"
```

- Jeśli `READY` → przejdź do Kroku 1
- Jeśli `NEEDS_BOOTSTRAP` → wykonaj sekcję **"Bootstrap serwera (jednorazowo)"** poniżej, potem wróć do Kroku 1

### Krok 1: Wykryj kontekst projektu WP

1. Sprawdź czy w bieżącym katalogu jest `wp-config.php`. Jeśli nie — szukaj w podkatalogach (1 poziom). Jeśli wciąż nie ma → **Pytanie do usera:** "Nie widzę WordPressa w tym katalogu. Wskaż ścieżkę do projektu."
2. Z `wp-config.php` wyciągnij:
   - `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST` (zwykle `localhost`)
   - `table_prefix`
3. Wykryj **lokalny URL** strony — sprawdź w tej kolejności:
   - `define('WP_HOME', ...)` w `wp-config.php`
   - `define('WP_SITEURL', ...)` w `wp-config.php`
   - Jeśli żaden nie jest zahardkodowany — uruchom lokalnie `wp option get siteurl --path=<wp-root>` (wymaga WP-CLI)
   - Ostateczność — **Pytanie do usera:** "Jaki jest lokalny URL tej strony? (np. http://klient-akme.local)"
4. Wykryj typ instancji lokalnej (do socketu MySQL):
   - LocalWP: ścieżka `lightning-services` w wp-config wskazuje LocalWP
   - MAMP: jeśli wp-config jest w `/Applications/MAMP/htdocs/`
   - Inne (Docker, standalone) → **Pytanie do usera:** "Skąd ma być eksport bazy? (LocalWP / MAMP / inne)"

### Krok 2: Zaproponuj slug

Sugestia = nazwa katalogu projektu, znormalizowana (małe litery, myślniki zamiast spacji/podkreśleń, regex `^[a-z0-9-]+$`, 3-40 znaków).

**Pytanie do usera:** "Jaki slug ma mieć staging? Sugestia: `<sugestia>`. (Wciśnij Enter żeby zaakceptować lub podaj inny)"

### Krok 2.5: Wybór subdomeny

**Pytanie do usera:** "Jaki URL ma mieć staging?
1. **Auto** — `https://srv66-<PORT>.wykr.es` (najszybsze, ale brzydki link)
2. **`<slug>.byst.re`** — ładny, dla klienta
3. **`<slug>.bieda.it`** — alternatywa
4. **`<slug>.mikr.us`** — alternatywa
5. **Inna** — podaj subdomenę jaką chcesz (np. `klient-akme.byst.re`)

Wybierz numer (1-5) [domyślnie: 2]:"

Zapisz wybór jako zmienną:
- Opcja 1 → `SUBDOMAIN=""` (helper użyje domyślnej `srv66-PORT.wykr.es`)
- Opcja 2 → `SUBDOMAIN="<slug>.byst.re"`
- Opcja 3 → `SUBDOMAIN="<slug>.bieda.it"`
- Opcja 4 → `SUBDOMAIN="<slug>.mikr.us"`
- Opcja 5 → pytanie do usera o pełną nazwę subdomeny (walidacja: regex `^[a-z0-9-]+\.[a-z.]+$`, musi się kończyć na `.byst.re|.bieda.it|.mikr.us|.mikrus.xyz` lub jego własna domena z DNS na Mikrusa)

Jeśli user wybrał coś innego niż 1 — sprawdź czy subdomena nie jest zajęta:
```bash
curl -fsSI "https://$SUBDOMAIN" -o /dev/null && echo "TAKEN" || echo "FREE"
```
Jeśli `TAKEN` → wróć do pytania (zaproponuj inny slug lub inną domenę).

### Krok 3: Sprawdź czy slug już istnieje na serwerze

```bash
ssh ... "/srv/wp-deploy/server-helper.sh exists <slug>"
# exit 0 = istnieje, exit 1 = nie istnieje
```

Jeśli istnieje:
1. Pokaż info: `ssh ... "/srv/wp-deploy/server-helper.sh info <slug>"` (URL, kiedy, kto)
2. **Pytanie do usera:** "Staging `<slug>` już istnieje (URL: ..., utworzony: ..., przez: ...). Nadpisać go (kasuje obecną zawartość)? (y/n)"
3. Jeśli `y` — przejdź dalej, helper sam ogarnie nadpisanie. Jeśli `n` — wróć do Kroku 2 (poproś o inny slug).

### Krok 4: Wybierz wolny port

```bash
PORT=$(ssh ... "/srv/wp-deploy/server-helper.sh port-free")
```

Jeśli zwróci pusty / kod błędu — pula portów wyczerpana. Powiadom usera: "Wszystkie porty zajęte (max 7 stagingów równolegle na planie Mikrus bez PRO). Usuń jakiś przez `remove <slug>` żeby zwolnić port."

### Krok 5: Eksport lokalny

Stwórz katalog tymczasowy:
```bash
TMPDIR=$(mktemp -d /tmp/wp-deploy-<slug>-XXXX)
```

**Eksport bazy** — wybierz socket według typu instancji (patrz Krok 1.4):

LocalWP:
```bash
# znajdź socket w ~/Library/Application Support/Local/run/*/mysql/mysqld.sock
SOCKET=$(ls ~/Library/Application\ Support/Local/run/*/mysql/mysqld.sock 2>/dev/null | head -1)
mysqldump --socket="$SOCKET" -u root -proot --add-drop-table <DB_NAME> > "$TMPDIR/db.sql"
```

MAMP:
```bash
mysqldump --socket=/Applications/MAMP/tmp/mysql/mysql.sock -u root -proot --add-drop-table <DB_NAME> > "$TMPDIR/db.sql"
```

Inne (TCP):
```bash
mysqldump -h <DB_HOST> -P <PORT> -u <DB_USER> -p<DB_PASSWORD> --add-drop-table <DB_NAME> > "$TMPDIR/db.sql"
```

**Eksport plików:**
```bash
tar czf "$TMPDIR/files.tar.gz" \
  --exclude='wp-content/cache' \
  --exclude='wp-content/upgrade' \
  --exclude='wp-content/uploads/cache' \
  --exclude='node_modules' \
  --exclude='.git' \
  --exclude='*.log' \
  --exclude='.DS_Store' \
  --exclude='docs/superpowers' \
  --exclude='.claude' \
  --exclude='.mcp.json' \
  --exclude='PROJECT-STATE.md' \
  --exclude='CLAUDE.md' \
  --exclude='.vscode' \
  --exclude='.idea' \
  -C <wp-root> .
```

**Dlaczego te dodatkowe excludes:** projekty WP rozwijane z Claude Code często mają w root katalogu artefakty (`docs/superpowers/`, `.claude/`, `.mcp.json`, `CLAUDE.md`, `PROJECT-STATE.md`) — nie należą do WP runtime, niepotrzebnie zwiększają rozmiar paczki i mogą zawierać wrażliwe info. Nie powinny być publicznie dostępne pod webroot.

### Krok 6: Upload na Mikrusa

```bash
ssh ... "mkdir -p /tmp/wp-deploy-incoming/<slug>"
scp ... "$TMPDIR/db.sql" "$TMPDIR/files.tar.gz" \
  root@tadek198.mikrus.xyz:/tmp/wp-deploy-incoming/<slug>/
```

Pokaż userowi progress (`scp` ma wbudowany pasek przy `-v` ale jest brzydki — albo użyj `pv`).

### Krok 7: Stwórz stronę na serwerze

```bash
# Z customową subdomeną (Krok 2.5 opcje 2-5):
ssh ... "/srv/wp-deploy/server-helper.sh create <slug> <PORT> '<user-email>' '<SUBDOMAIN>'"

# Z auto-subdomeną (Krok 2.5 opcja 1) — pomijasz ostatni argument:
ssh ... "/srv/wp-deploy/server-helper.sh create <slug> <PORT> '<user-email>'"
```

`<user-email>` = z `git config user.email` jeśli ustawione, inaczej `$USER@$(hostname)`.

Helper jest atomowy — albo zrobi wszystko, albo rollbackuje. Helper sam wywoła `domena <SUBDOMAIN> <PORT>` z odpowiednim adresem (custom lub `srv66-<PORT>.wykr.es`).

Helper zwraca JSON z polem `url` — odczytaj go, bo to ostateczny URL strony (potrzebny w Kroku 8 do `search-replace`):
```bash
ENTRY=$(ssh ... "/srv/wp-deploy/server-helper.sh create ...")
NEW_URL=$(echo "$ENTRY" | jq -r '.url')
```

### Krok 8: Import (pliki + baza + search-replace)

```bash
LOCAL_URL='<wykryty-lokalny-url>'  # np. http://klient-akme.local
# NEW_URL pochodzi z Kroku 7 (odczytany z odpowiedzi helpera)

ssh ... "/srv/wp-deploy/server-helper.sh unpack-files <slug>"
ssh ... "/srv/wp-deploy/server-helper.sh import-db <slug> '$LOCAL_URL' '$NEW_URL'"
```

Helper import-db wykonuje:
1. `mysql wp_<slug> < /tmp/wp-deploy-incoming/<slug>/db.sql`
2. `wp search-replace "$LOCAL_URL" "$NEW_URL" --all-tables --path=/srv/sites/<slug>` (poprawnie obsługuje PHP serialized)
3. `wp cache flush --path=/srv/sites/<slug>`
4. Sprzątanie `/tmp/wp-deploy-incoming/<slug>/`

### Krok 9: Weryfikacja i pokazanie wyniku

```bash
# Czekaj na propagację domena (zwykle 5-15s):
sleep 5
curl -fsSI "https://srv66-${PORT}.wykr.es" -o /dev/null && echo OK || echo PROBLEM
```

Powiedz userowi:
```
✓ Staging gotowy: https://srv66-<PORT>.wykr.es
   Slug:   <slug>
   Port:   <PORT>
   Baza:   wp_<slug>
```

Jeśli `curl` zwrócił PROBLEM — sprawdź helpera doctor i podpowiedź usera co może być nie tak.

Posprzątaj lokalnie: `rm -rf "$TMPDIR"`.

---

## Inne komendy

### "lista stagingów" / "pokaż stagingi" / "list"

```bash
ssh ... "/srv/wp-deploy/server-helper.sh list"
```

Zwraca JSON. Wyświetl jako tabelę markdown:

| slug | URL | port | utworzono | przez | status |
|------|-----|------|-----------|-------|--------|

Dla każdej strony — `curl -fsSI <url> -o /dev/null && echo ✓ || echo ⚠` żeby pokazać status.

### "usuń staging <slug>" / "remove <slug>"

**Pytanie do usera:** "Zrobić backup przed usunięciem? (zalecane) (y/n)"

```bash
# Z backupem:
ssh ... "/srv/wp-deploy/server-helper.sh remove <slug> --backup"
# Bez:
ssh ... "/srv/wp-deploy/server-helper.sh remove <slug>"
```

Jeśli z backupem — pokaż userowi ścieżkę pliku backup (helper ją wypisuje).

### "doctor" / "sprawdź serwer"

```bash
ssh ... "/srv/wp-deploy/server-helper.sh doctor"
```

Helper zwraca JSON. Wyrenderuj czytelnie z ikonkami ✓/⚠/✗.

---

## Bootstrap serwera (jednorazowo)

Wykonujesz to TYLKO raz, kiedy w Kroku 0 wyszło `NEEDS_BOOTSTRAP`. Po tym kroku helper będzie na serwerze i wszystko działa.

### B1) Zainstaluj zależności na Mikrusie

```bash
ssh ... "apt-get update && \
  DEBIAN_FRONTEND=noninteractive apt-get install -y \
    nginx php8.3-fpm php8.3-mysql php8.3-curl php8.3-gd php8.3-mbstring php8.3-xml php8.3-zip \
    mariadb-server curl tar jq unzip"
```

### B2) Zainstaluj WP-CLI

```bash
ssh ... "curl -fsSL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
  -o /usr/local/bin/wp && chmod +x /usr/local/bin/wp && wp --info --allow-root"
```

### B3) Stwórz strukturę katalogów

```bash
ssh ... "mkdir -p /srv/wp-deploy /srv/sites /srv/wp-deploy/backups /etc/nginx/sites-available /etc/nginx/sites-enabled && \
  echo '{}' > /srv/wp-deploy/sites.json && \
  touch /srv/wp-deploy/sites.json.lock"
```

### B4) Skonfiguruj pulę portów

**Pytanie do usera:** "Jakie porty masz dostępne w panelu Mikrusa (https://mikr.us/panel/?a=ports)? Plan podstawowy daje 2 domyślne porty TCP + można dodać 5 dodatkowych (łącznie 7, banner 'Osiągnąłeś limit' przy 7). Mikrus PRO odblokowuje więcej. Podaj listę portów inne niż 10198 (SSH), oddzielone przecinkami (np. 20198, 30198, 40457, 40458, 40459, 40460, 40461)"

Zapisz na serwerze:
```bash
ssh ... "echo '<porty oddzielone spacją>' > /srv/wp-deploy/port-pool"
```

### B5) Wgraj `server-helper.sh`

Cała treść skryptu poniżej. Wgraj przez `cat ... | ssh ... 'cat > /srv/wp-deploy/server-helper.sh && chmod +x ...'`:

```bash
ssh ... "cat > /srv/wp-deploy/server-helper.sh && chmod +x /srv/wp-deploy/server-helper.sh" <<'HELPER_EOF'
<TUTAJ WKLEJASZ TREŚĆ server-helper.sh Z SEKCJI PONIŻEJ>
HELPER_EOF
```

### B6) Wgraj template vhost nginx

```bash
ssh ... "cat > /srv/wp-deploy/vhost.conf.template" <<'VHOST_EOF'
<TUTAJ WKLEJASZ TREŚĆ vhost.conf.template Z SEKCJI PONIŻEJ>
VHOST_EOF
```

### B7) Uruchom doctor żeby zweryfikować

```bash
ssh ... "/srv/wp-deploy/server-helper.sh doctor"
```

Wszystko powinno być ✓.

---

## Skrypty serwerowe

### server-helper.sh

Skrypt bash uruchamiany przez SSH z runbooku. Każda komenda atomowa.

```bash
#!/usr/bin/env bash
# /srv/wp-deploy/server-helper.sh
# Helper do operacji deployu WP na Mikrusie. Wywoływany przez SSH z auto-deploy.md.

set -euo pipefail

REGISTRY=/srv/wp-deploy/sites.json
LOCK=/srv/wp-deploy/sites.json.lock
SITES_DIR=/srv/sites
PORT_POOL_FILE=/srv/wp-deploy/port-pool
INCOMING=/tmp/wp-deploy-incoming
VHOST_AVAILABLE=/etc/nginx/sites-available
VHOST_ENABLED=/etc/nginx/sites-enabled
BACKUPS=/srv/wp-deploy/backups
VHOST_TEMPLATE=/srv/wp-deploy/vhost.conf.template
SERVER_ID="srv66"

cmd="${1:-}"; shift || true

# --- helpery wewnętrzne ---

_with_lock() {
  exec 200>"$LOCK"
  flock -x -w 10 200 || { echo '{"error":"lock_timeout"}' >&2; exit 11; }
  "$@"
}

_registry_get() {
  jq -r --arg s "$1" '.[$s] // empty' "$REGISTRY"
}

_registry_set() {
  local slug="$1" entry="$2" tmp
  tmp=$(mktemp)
  jq --arg s "$slug" --argjson e "$entry" '.[$s] = $e' "$REGISTRY" > "$tmp"
  mv "$tmp" "$REGISTRY"
}

_registry_del() {
  local slug="$1" tmp
  tmp=$(mktemp)
  jq --arg s "$slug" 'del(.[$s])' "$REGISTRY" > "$tmp"
  mv "$tmp" "$REGISTRY"
}

_used_ports() {
  jq -r '.[].port' "$REGISTRY" 2>/dev/null | sort -n
}

_validate_slug() {
  [[ "$1" =~ ^[a-z0-9-]{3,40}$ ]] || { echo "invalid slug" >&2; exit 4; }
}

# --- komendy ---

cmd_list() {
  cat "$REGISTRY"
}

cmd_info() {
  local slug="$1"; _validate_slug "$slug"
  _registry_get "$slug"
}

cmd_exists() {
  local slug="$1"; _validate_slug "$slug"
  [ -n "$(_registry_get "$slug")" ]
}

cmd_port_free() {
  local used pool
  used=$(_used_ports)
  pool=$(cat "$PORT_POOL_FILE")
  for p in $pool; do
    if ! echo "$used" | grep -qx "$p"; then
      echo "$p"; return 0
    fi
  done
  echo "no_free_port" >&2; exit 3
}

cmd_create() {
  local slug="$1" port="$2" created_by="${3:-unknown}" subdomain="${4:-}"
  _validate_slug "$slug"

  # Jeśli pusta subdomena — użyj domyślnej wykr.es
  if [ -z "$subdomain" ]; then
    subdomain="${SERVER_ID}-${port}.wykr.es"
  fi

  # Walidacja subdomeny (litery/cyfry/myślniki/kropki)
  [[ "$subdomain" =~ ^[a-z0-9.-]+\.[a-z]+(\.[a-z]+)?$ ]] || { echo "invalid_subdomain" >&2; exit 4; }

  # Jeśli istnieje — usuń pierwszy (overwrite)
  if [ -n "$(_with_lock _registry_get "$slug")" ]; then
    cmd_remove "$slug"
  fi

  local site_dir="$SITES_DIR/$slug"
  local db_name="wp_${slug//-/_}"
  local db_user="wp_${slug//-/_}"
  local db_pass; db_pass=$(openssl rand -base64 16 | tr -d '=+/')
  local url="https://${subdomain}"

  trap '_rollback "'"$slug"'" "'"$port"'" "'"$db_name"'" "'"$db_user"'" "'"$subdomain"'"' ERR

  # Katalog
  mkdir -p "$site_dir"
  chown -R www-data:www-data "$site_dir"

  # Baza
  mysql <<SQL
CREATE DATABASE \`$db_name\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER '$db_user'@'localhost' IDENTIFIED BY '$db_pass';
GRANT ALL PRIVILEGES ON \`$db_name\`.* TO '$db_user'@'localhost';
FLUSH PRIVILEGES;
SQL

  # Vhost
  sed -e "s/__SLUG__/$slug/g" -e "s/__PORT__/$port/g" -e "s/__SERVER_NAME__/$subdomain/g" \
      "$VHOST_TEMPLATE" > "$VHOST_AVAILABLE/$slug.conf"
  ln -sf "$VHOST_AVAILABLE/$slug.conf" "$VHOST_ENABLED/$slug.conf"
  nginx -t >/dev/null
  nginx -s reload

  # Subdomena Mikrusa (domena zwraca exit 0 nawet przy błędzie API — parsujemy odpowiedź).
  # Uwaga: "Domena już istnieje" oznacza że już ją dodaliśmy do TEGO konta Mikrusa
  # (np. poprzedni nieudany rollback). Subdomenę można usunąć TYLKO ręcznie z
  # https://mikr.us/panel/?a=domain (API nie ma endpointu do usuwania). Traktujemy to
  # jako warning — najczęściej oznacza że subdomena już wskazuje na ten port.
  local domena_out
  domena_out=$(domena "$subdomain" "$port" 2>&1 | sed 's/\x1b\[[0-9;]*m//g')
  if echo "$domena_out" | grep -q '"error": "Domena już istnieje"'; then
    echo "warning: subdomena $subdomain już istnieje w panelu Mikrusa (może wskazywać na inny port — sprawdź https://mikr.us/panel/?a=domain)" >&2
  elif echo "$domena_out" | grep -qE '"error"|^.*błąd'; then
    # Inny błąd niż "już istnieje" — to faktyczna porażka
    echo "domena_failed: $(echo "$domena_out" | grep -oE '"error"[^,}]*' | head -1)" >&2
    exit 1
  fi

  # Zapis do rejestru — atomowy
  local entry
  entry=$(jq -n --arg port "$port" --arg url "$url" --arg sub "$subdomain" \
                --arg db "$db_name" --arg user "$db_user" --arg pass "$db_pass" \
                --arg by "$created_by" --arg ts "$(date -u +%FT%TZ)" \
    '{port:($port|tonumber), url:$url, subdomain:$sub, db:$db, db_user:$user, db_pass:$pass,
      created:$ts, created_by:$by}')
  _with_lock _registry_set "$slug" "$entry"

  trap - ERR
  echo "$entry"
}

_rollback() {
  local slug="$1" port="$2" db_name="$3" db_user="$4" subdomain="${5:-}"
  echo "rollback $slug" >&2
  rm -rf "$SITES_DIR/$slug"
  mysql -e "DROP DATABASE IF EXISTS \`$db_name\`; DROP USER IF EXISTS '$db_user'@'localhost';" 2>/dev/null || true
  rm -f "$VHOST_AVAILABLE/$slug.conf" "$VHOST_ENABLED/$slug.conf"
  nginx -s reload 2>/dev/null || true
  [ -n "$subdomain" ] && domena "$subdomain" "$port" --remove 2>/dev/null || true
  _with_lock _registry_del "$slug" 2>/dev/null || true
}

cmd_unpack_files() {
  local slug="$1"; _validate_slug "$slug"
  local src="$INCOMING/$slug/files.tar.gz"
  local dst="$SITES_DIR/$slug"
  [ -f "$src" ] || { echo "no_files_uploaded" >&2; exit 1; }
  tar xzf "$src" -C "$dst"
  chown -R www-data:www-data "$dst"
  find "$dst" -type d -exec chmod 755 {} \;
  find "$dst" -type f -exec chmod 644 {} \;
  rm -f "$src"

  # Aktualizuj wp-config.php credentialkami z rejestru
  local entry db_name db_user db_pass
  entry=$(_registry_get "$slug")
  db_name=$(echo "$entry" | jq -r '.db')
  db_user=$(echo "$entry" | jq -r '.db_user')
  db_pass=$(echo "$entry" | jq -r '.db_pass')
  wp --allow-root --path="$dst" config set DB_NAME "$db_name" --quiet
  wp --allow-root --path="$dst" config set DB_USER "$db_user" --quiet
  wp --allow-root --path="$dst" config set DB_PASSWORD "$db_pass" --quiet
  wp --allow-root --path="$dst" config set DB_HOST "localhost" --quiet

  # Wstrzyknij fix dla reverse-proxy SSL (Cloudflare za Mikrusem zwraca HTTP do nginx)
  if ! grep -q 'HTTP_X_FORWARDED_PROTO' "$dst/wp-config.php"; then
    awk '
      /That.s all, stop editing/ && !done {
        print "if (isset($_SERVER[\"HTTP_X_FORWARDED_PROTO\"]) && $_SERVER[\"HTTP_X_FORWARDED_PROTO\"] === \"https\") { $_SERVER[\"HTTPS\"] = \"on\"; }"
        done=1
      }
      { print }
    ' "$dst/wp-config.php" > "$dst/wp-config.php.new" && mv "$dst/wp-config.php.new" "$dst/wp-config.php"
    chown www-data:www-data "$dst/wp-config.php"
  fi
}

cmd_import_db() {
  local slug="$1" local_url="$2" new_url="$3"; _validate_slug "$slug"
  local entry db_name
  entry=$(_registry_get "$slug")
  [ -n "$entry" ] || { echo "not_in_registry" >&2; exit 1; }
  db_name=$(echo "$entry" | jq -r '.db')
  local dump="$INCOMING/$slug/db.sql"
  [ -f "$dump" ] || { echo "no_dump_uploaded" >&2; exit 1; }

  # Strip warningi mysqldump (np. MAMP wkleja Warning na początek)
  sed -i '/^mysqldump: \[Warning\]/d' "$dump"

  mysql "$db_name" < "$dump"
  wp --allow-root --path="$SITES_DIR/$slug" search-replace "$local_url" "$new_url" --all-tables --skip-columns=guid

  # Hardcoded URLe w plikach motywu (block theme parts .html, block.json, style.css)
  # search-replace ich nie widzi bo to nie baza
  local themes_dir="$SITES_DIR/$slug/wp-content/themes"
  if [ -d "$themes_dir" ]; then
    find "$themes_dir" -type f \( -name '*.html' -o -name '*.json' -o -name '*.css' \) -size -1048576c -print0 \
      | xargs -0 -r sed -i "s|$local_url|$new_url|g"
  fi

  wp --allow-root --path="$SITES_DIR/$slug" cache flush || true
  rm -f "$dump"; rmdir "$INCOMING/$slug" 2>/dev/null || true
}

cmd_remove() {
  local slug="$1"; _validate_slug "$slug"
  local backup_flag="${2:-}"
  local entry
  entry=$(_registry_get "$slug")
  [ -n "$entry" ] || { echo "not_found" >&2; exit 1; }
  local port db_name db_user subdomain
  port=$(echo "$entry" | jq -r '.port')
  db_name=$(echo "$entry" | jq -r '.db')
  db_user=$(echo "$entry" | jq -r '.db_user')
  subdomain=$(echo "$entry" | jq -r '.subdomain // empty')

  if [ "$backup_flag" = "--backup" ]; then
    local ts; ts=$(date -u +%Y%m%d-%H%M%S)
    local backup_path="$BACKUPS/${slug}-${ts}.tar.gz"
    mkdir -p "$BACKUPS"
    mysqldump "$db_name" > "/tmp/${slug}-db.sql"
    tar czf "$backup_path" -C "$SITES_DIR" "$slug" -C /tmp "${slug}-db.sql"
    rm "/tmp/${slug}-db.sql"
    echo "backup_path: $backup_path"
  fi

  rm -rf "$SITES_DIR/$slug"
  mysql -e "DROP DATABASE IF EXISTS \`$db_name\`; DROP USER IF EXISTS '$db_user'@'localhost';"
  rm -f "$VHOST_AVAILABLE/$slug.conf" "$VHOST_ENABLED/$slug.conf"
  nginx -s reload
  _with_lock _registry_del "$slug"

  # Subdomena WCIĄŻ wisi w panelu Mikrusa — API nie ma endpointu do usuwania (sprawdzone 2026-05-20)
  if [ -n "$subdomain" ]; then
    echo "" >&2
    echo "⚠ Subdomena '$subdomain' nadal istnieje w panelu Mikrusa — usuń ją ręcznie:" >&2
    echo "   https://mikr.us/panel/?a=domain" >&2
    echo "   (Mikrus API nie wystawia endpointu do usuwania subdomen)" >&2
  fi
}

cmd_doctor() {
  local disk_free mariadb_status nginx_status free_ports total_ports
  disk_free=$(df -BG /srv | awk 'NR==2 {print $4}' | tr -d 'G')
  mariadb_status=$(systemctl is-active mariadb || echo dead)
  if nginx -t >/dev/null 2>&1; then nginx_status=ok; else nginx_status=broken; fi
  total_ports=$(wc -w < "$PORT_POOL_FILE")
  free_ports=$((total_ports - $(_used_ports | wc -l)))

  jq -n \
    --argjson disk "$disk_free" \
    --arg mariadb "$mariadb_status" \
    --arg nginx "$nginx_status" \
    --argjson free "$free_ports" \
    --argjson total "$total_ports" \
    '{disk_free_gb:$disk, mariadb:$mariadb, nginx:$nginx,
      free_ports:$free, total_ports:$total,
      helper_version:"1.2.0"}'
}

cmd_rollback() {
  local slug="$1"; _validate_slug "$slug"
  _rollback "$slug" "?" "wp_${slug//-/_}" "wp_${slug//-/_}"
}

case "$cmd" in
  list)         cmd_list ;;
  info)         cmd_info "$@" ;;
  exists)       cmd_exists "$@" ;;
  port-free)    cmd_port_free ;;
  create)       cmd_create "$@" ;;
  unpack-files) cmd_unpack_files "$@" ;;
  import-db)    cmd_import_db "$@" ;;
  remove)       cmd_remove "$@" ;;
  rollback)     cmd_rollback "$@" ;;
  doctor)       cmd_doctor ;;
  *) echo "unknown command: $cmd" >&2; exit 4 ;;
esac
```

### vhost.conf.template

Template vhost nginx — `__SLUG__` i `__PORT__` zostają podmienione przez `cmd_create`.

```nginx
server {
    listen __PORT__;
    listen [::]:__PORT__;

    server_name __SERVER_NAME__;
    root /srv/sites/__SLUG__;
    index index.php index.html;

    access_log /var/log/nginx/__SLUG__.access.log;
    error_log  /var/log/nginx/__SLUG__.error.log;

    client_max_body_size 64m;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_read_timeout 300;
    }

    location ~ /\.ht {
        deny all;
    }

    location = /xmlrpc.php {
        deny all;
    }
}
```

---

## Troubleshooting

### SSH timeout / "connection refused"

Najpierw spytaj usera czy ma internet (otwórz `https://mikr.us/panel` w przeglądarce). Jeśli panel działa → spróbuj `ping6 2a01:4f9:4a:1a51::198` (Mikrus ma IPv6) lub `ping tadek198.mikrus.xyz` — jeśli timeout, Mikrus może być chwilowo niedostępny, retry za chwilę.

### `mysqldump: Got error: 2002` (lokalnie)

Socket nie istnieje pod oczekiwaną ścieżką. Dla LocalWP — strona musi być **włączona** (LocalWP startuje MySQL tylko gdy strona jest "started"). Sprawdź czy LocalWP pokazuje zieloną kropkę przy stronie.

### `wp search-replace` zwraca "no tables to search"

Najczęściej zły `--path`. Sprawdź czy w `/srv/sites/<slug>` są pliki WP (jest `wp-config.php`?).

### `domena` zwraca błąd na serwerze

Aplikacja na danym porcie musi odpowiadać żeby `domena` ją zaakceptowała. Helper wywołuje `domena` **po** `nginx -s reload`, więc port powinien działać. Jeśli błąd — sprawdź `nginx -t` i status PHP-FPM (`systemctl status php8.3-fpm`).

### Subdomena nie znika po `remove`

**Mikrus API nie ma endpointu do usuwania subdomen** (sprawdzone 2026-05-20). Po `remove` strona po stronie serwera jest skasowana (baza, pliki, nginx, sites.json), ale **wpis subdomeny w panelu Mikrusa pozostaje**. Trzeba usunąć ręcznie:

1. Otwórz https://mikr.us/panel/?a=domain
2. Znajdź wpis subdomeny (helper podpowiada nazwę w komunikacie po `remove`)
3. Kliknij "Usuń"

Wisząca subdomena nie szkodzi (port wolny, kolejny `create` może z niej skorzystać) — śmieci w panelu, sprzątaj co kilka deployów.

### "Domena już istnieje" przy `create`

To **nie** znaczy "zajęta przez innego usera" — to znaczy "już ją kiedyś dodałeś do swojego konta Mikrusa". Helper 1.2.0+ traktuje to jako warning i kontynuuje deploy. Subdomena wskaże na nowy port.

Jeśli widzisz **CYTR.US placeholder** zamiast strony przez >5 min po deployu — subdomena wskazuje na nie-twoją instancję (rzadko). Wybierz inną subdomenę.

### `flock: timeout` przy zapisie do `sites.json`

Inny user robi deploy w tym samym czasie. Poczekaj 10-15s i powtórz.

### Staging odpowiada 502 / 504

PHP-FPM nie działa albo nie ma dostępu do plików. Sprawdź:
```bash
ssh ... "systemctl status php8.3-fpm && ls -la /srv/sites/<slug>"
```

Prawdopodobnie zła wersja PHP (template wymaga 8.2). Jeśli na Mikrusie jest inna wersja — popraw ścieżkę socketu w `vhost.conf.template` i wykonaj ponownie B6 + reload nginx.

---

## Wersjonowanie

Wersja runbooka i helpera trzymane razem. Numer w `cmd_doctor` (`helper_version`) i w nagłówku tego pliku.

**Aktualizacja:** zmieniasz `auto-deploy.md` → przy najbliższym `deploy` Claude sprawdza `doctor`, jeśli wersja helpera ≠ runbooka → re-uploaduje helper (sekcja B5). User nie musi nic robić.

Wersja: `1.2.0` (2026-05-20)

**Changelog:**
- `1.2.0` — naprawione semantyki `cmd_create` i `cmd_remove` po odkryciu że Mikrus API nie ma endpointu do usuwania subdomen:
  - `cmd_create`: "Domena już istnieje" to warning (nie error) — najczęściej oznacza że subdomena jest już nasza (z poprzedniego deployu).
  - `cmd_remove`: nie próbuje usuwać subdomeny przez API (nie da się), pokazuje userowi instrukcję ręcznego usunięcia z panelu.
- `1.1.2` — sed na plikach motywu rozszerzony o `.json` (block.json custom blocks) i `.css` (style.css) — wcześniej tylko `.html`. Hardcoded URLe w block.json są częste w custom blocks z lokalnymi placeholderami.
- `1.1.1` — fix detekcji błędów `domena`: API Mikrusa zwraca błąd w JSON ale `domena` ma exit 0, więc parsujemy output. Plus fix dla `pipefail` w `cmd_doctor` (nginx_status używa if zamiast pipeline'a).
- `1.1.0` — fixy z pierwszego E2E: helper auto-aktualizuje DB creds w wp-config po unpack, wstrzykuje reverse-proxy SSL fix, robi sed na .html block theme parts, strippuje warningi mysqldump z dumpu.
- `1.0.0` — wersja początkowa.
