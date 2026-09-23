#!/usr/bin/env bash
# Smoke test projektu: php -l, node --check, routing/noindex przez php -S + router.php, ślady DC, istnienie plików.
# Użycie: tools/check.sh [scaffold|images|core|partials|home|oferta|galeria|kontakt|all]
set -u
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
FAIL=0
ok()   { printf '  ok   %s\n' "$1"; }
fail() { printf '  FAIL %s\n' "$1"; FAIL=$((FAIL+1)); }
check() { local desc=$1; shift; if "$@" >/dev/null 2>&1; then ok "$desc"; else fail "$desc"; fi; }
eq() { local desc=$1 got=$2 exp=$3; if [ "$got" = "$exp" ]; then ok "$desc ($got)"; else fail "$desc (got '$got', expected '$exp')"; fi; }
ge() { local desc=$1 got=$2 min=$3; if [ "${got:-0}" -ge "$min" ] 2>/dev/null; then ok "$desc ($got)"; else fail "$desc (got '$got', expected >= $min)"; fi; }
le() { local desc=$1 got=$2 max=$3; if [ "${got:-999}" -le "$max" ] 2>/dev/null; then ok "$desc ($got)"; else fail "$desc (got '$got', expected <= $max)"; fi; }

PORT=8089
SERVER_PID=""
start_server() {
  php -S 127.0.0.1:$PORT -t public_html tools/router.php >"$ROOT/.superpowers/php-server.log" 2>&1 &
  SERVER_PID=$!
  for i in 1 2 3 4 5 6 7 8 9 10; do curl -s -o /dev/null "http://127.0.0.1:$PORT/" && break; sleep 0.3; done
}
stop_server() { [ -n "$SERVER_PID" ] && kill "$SERVER_PID" 2>/dev/null; SERVER_PID=""; }
trap stop_server EXIT
code() { curl -s -o /dev/null -w '%{http_code}' "$@"; }
fetch() { curl -s "$@"; }
export -f fetch code
assets_exist() { # stdin: ścieżki /assets/...
  local missing=0 p
  while read -r p; do [ -z "$p" ] && continue; [ -f "public_html$p" ] || { echo "    BRAK $p"; missing=$((missing+1)); }; done
  [ "$missing" -eq 0 ]
}
page_assets() { # $1 = plik html
  { grep -o '\(src\|href\|data-full\)="/assets/[^"?]*' "$1" | sed 's/.*="//';
    grep -o 'srcset="[^"]*"' "$1" | tr ',' '\n' | grep -o '/assets/[^ ]*';
    grep -o 'imagesrcset="[^"]*"' "$1" | tr ',' '\n' | grep -o '/assets/[^ ]*'; } | sort -u
}
traces() { grep -cE '\{\{|<sc-|<x-dc|<helmet|hint-placeholder|support\.js|fonts\.googleapis|lh3\.googleusercontent|dc-import|style-hover=|_ds_bundle|unpkg' "$1"; }
title_len() { python3 -c "import re,sys;m=re.search(r'<title>(.*?)</title>',open(sys.argv[1],encoding='utf-8').read());print(len(m.group(1)) if m else 999)" "$1"; }
jsonld_ok() { python3 - "$1" <<'EOF'
import re,json,sys
s=open(sys.argv[1],encoding='utf-8').read()
blocks=re.findall(r'<script type="application/ld\+json">(.*?)</script>',s,re.S)
assert blocks, 'brak JSON-LD'
for b in blocks: json.loads(b)
EOF
}
jsonld_types() { python3 - "$1" <<'EOF'
import re,json,sys,collections
s=open(sys.argv[1],encoding='utf-8').read()
c=collections.Counter()
def walk(o):
    if isinstance(o,dict):
        if '@type' in o: c[o['@type']]+=1
        for v in o.values(): walk(v)
    elif isinstance(o,list):
        for v in o: walk(v)
for b in re.findall(r'<script type="application/ld\+json">(.*?)</script>',s,re.S): walk(json.loads(b))
print(' '.join(f'{k}={v}' for k,v in sorted(c.items())))
EOF
}

section_scaffold() {
  echo "[scaffold]"
  eq "6 plików designu" "$(ls design/*.dc.html 2>/dev/null | wc -l | tr -d ' ')" 6
  check "design/uploads/logo.png" test -f design/uploads/logo.png
  eq "14 plików fontów woff2" "$(ls public_html/assets/fonts/*.woff2 2>/dev/null | wc -l | tr -d ' ')" 14
  check "tools/fonts.css ma 14 @font-face" bash -c "[ \$(grep -c '@font-face' tools/fonts.css) -eq 14 ]"
  check "leaflet.js" test -f public_html/assets/vendor/leaflet/leaflet.js
  check "leaflet.css" test -f public_html/assets/vendor/leaflet/leaflet.css
  check "logo.png w assets (<=384px)" python3 -c "from PIL import Image; im=Image.open('public_html/assets/img/logo.png'); assert im.width<=384 and im.mode=='RGBA'"
  check "favicon.svg" test -f public_html/assets/favicon.svg
  check "router.php lint" php -l tools/router.php
}

section_images() {
  echo "[images]"
  eq "14 zdjęć źródłowych" "$(ls zrodla/google/*.jpg 2>/dev/null | wc -l | tr -d ' ')" 14
  check "każde źródło >= 700px" python3 -c "
from PIL import Image; import glob
for p in glob.glob('zrodla/google/*.jpg'): assert Image.open(p).width>=700, p"
  check "variants.json: 14 full + 14 thumbs + 4 hero" python3 -c "
import json; v=json.load(open('public_html/assets/img/variants.json'))
full=[k for k in v if '/' not in k]; th=[k for k in v if k.startswith('thumbs/')]; he=[k for k in v if k.startswith('hero/')]
assert (len(full),len(th),len(he))==(14,14,4), (len(full),len(th),len(he))"
  check "każdy wariant istnieje na dysku" python3 -c "
import json,os; v=json.load(open('public_html/assets/img/variants.json'))
for k,d in v.items():
    assert os.path.exists(f'public_html/assets/img/{k}.jpg'), k
    for w in d['widths']:
        for e in ('avif','webp'): assert os.path.exists(f'public_html/assets/img/{k}-{w}.{e}'), f'{k}-{w}.{e}'"
  check "alts.json ma 14 wpisów" python3 -c "import json; assert len(json.load(open('tools/alts.json')))==14"
  check "roles.json ma 23 role" python3 -c "import json; assert len(json.load(open('public_html/assets/img/roles.json')))==23"
  check "manifest.json: 14 items, role hero x4" python3 -c "
import json; m=json.load(open('tools/manifest.json'))['items']
assert len(m)==14; assert sum(1 for i in m if any(r.endswith('-hero') for r in i['roles']))==4"
  check "assets/img < 60 MB" bash -c "[ \$(du -sm public_html/assets/img | cut -f1) -lt 60 ]"
}

lint_all() {
  local f
  for f in public_html/*.php public_html/partials/*.php; do [ -f "$f" ] && { php -l "$f" >/dev/null 2>&1 || fail "php -l $f"; }; done
  for f in public_html/assets/js/*.js; do [ -f "$f" ] && { node --check "$f" >/dev/null 2>&1 || fail "node --check $f"; }; done
  ok "lint php/js"
}

section_core() {
  echo "[core]"
  lint_all
  start_server
  local B="http://127.0.0.1:$PORT"
  eq "GET / 200" "$(code $B/)" 200
  eq "GET /robots.txt 200" "$(code $B/robots.txt)" 200
  eq "GET /sitemap.xml 200" "$(code $B/sitemap.xml)" 200
  eq "GET /oferta 301" "$(code $B/oferta)" 301
  eq "GET /oferta.php 301" "$(code $B/oferta.php)" 301
  eq "GET /index.php 301" "$(code $B/index.php)" 301
  eq "Location /oferta -> /oferta/" "$(curl -s -o /dev/null -w '%{redirect_url}' $B/oferta)" "$B/oferta/"
  eq "GET /nie-ma 404" "$(code $B/nie-ma)" 404
  eq "GET /partials/config.php 403" "$(code $B/partials/config.php)" 403
  check "robots (dev) Disallow: /" bash -c "fetch $B/robots.txt | grep -q '^Disallow: /$'"
  check "X-Robots-Tag noindex (dev)" bash -c "curl -sI $B/ | grep -qi 'x-robots-tag: noindex'"
  check "meta noindex (dev)" bash -c "fetch $B/ | grep -q 'name=\"robots\" content=\"noindex'"
  check "robots (prod host) Allow: /" bash -c "curl -s -H 'Host: www.pomocdrogowa-pabianice.pl' $B/robots.txt | grep -q '^Allow: /$'"
  check "brak noindex (prod host)" bash -c "! curl -s -H 'Host: www.pomocdrogowa-pabianice.pl' $B/ | grep -q noindex"
  check "canonical -> produkcja (dev host)" bash -c "fetch $B/ | grep -q 'rel=\"canonical\" href=\"https://www.pomocdrogowa-pabianice.pl/\"'"
  check "sitemap: 4 url" bash -c "[ \$(fetch $B/sitemap.xml | grep -c '<loc>') -eq 4 ]"
  check "404 ma nawigację" bash -c "fetch $B/nie-ma | grep -q 'data-nav'"
  stop_server
}

page_common() { # $1 label, $2 url path, $3 out file
  local B="http://127.0.0.1:$PORT" out=$3
  eq "$1: GET $2 200" "$(code "$B$2")" 200
  fetch "$B$2" > "$out"
  eq "$1: ślady DC" "$(traces "$out")" 0
  eq "$1: h1 x1" "$(grep -c '<h1' "$out")" 1
  le "$1: title <= 60" "$(title_len "$out")" 60
  check "$1: JSON-LD parsuje się" jsonld_ok "$out"
  check "$1: wszystkie /assets istnieją" bash -c "page_assets '$out' | assets_exist"
  check "$1: main.js z ?v=" bash -c "grep -q 'main.js?v=[0-9]' '$out'"
  eq "$1: <main> x1" "$(grep -c '<main>' "$out")" 1
  eq "$1: bez PHP Warning/Notice/Fatal" "$(grep -cE '<b>(Warning|Notice|Fatal error|Deprecated)</b>' "$out")" 0
}
export -f page_assets assets_exist

section_partials() {
  echo "[partials]"
  lint_all; start_server
  local out=.superpowers/p_404.html
  fetch "http://127.0.0.1:$PORT/nie-ma" > "$out"
  eq "404: ślady DC" "$(traces "$out")" 0
  check "404: data-nav/data-fab/data-menu" bash -c "grep -q data-nav '$out' && grep -q data-fab '$out' && grep -q data-menu '$out'"
  check "404: burger aria-expanded" bash -c "grep -q 'data-burger aria-label=\"Menu\" aria-expanded=\"false\"' '$out'"
  eq "404: bez CTA" "$(grep -c 'id="cta-h"' "$out")" 0
  eq "404: <main> x1" "$(grep -c '<main>' "$out")" 1
  check "404: main.js ?v=" bash -c "grep -q 'main.js?v=[0-9]' '$out'"
  stop_server
}

section_home() {
  echo "[home]"
  lint_all; start_server
  local out=.superpowers/p_home.html
  page_common home / "$out"
  ge "home: h2" "$(grep -c '<h2' "$out")" 6
  eq "home: data-faq x7" "$(grep -c 'data-faq=' "$out")" 7
  eq "home: data-town x9" "$(grep -c 'data-town=' "$out")" 9
  check "home: svc-grid + 8 kart" bash -c "grep -q 'class=\"svc-grid\"' '$out' && [ \$(grep -o 'aspect-ratio:16/10' '$out' | wc -l) -eq 8 ]"
  check "home: sekcje id" bash -c "for i in top o-firmie obszar zakres opinie faq; do grep -q \"id=\\\"\$i\\\"\" '$out' || exit 1; done"
  check "home: JSON-LD FAQPage+AutomotiveBusiness+Service, bez Breadcrumb" bash -c "t=\$(jsonld_types '$out'); echo \"\$t\" | grep -q 'FAQPage=1' && echo \"\$t\" | grep -q 'AutomotiveBusiness=1' && echo \"\$t\" | grep -q 'Service=1' && echo \"\$t\" | grep -q 'Question=7' && ! echo \"\$t\" | grep -q Breadcrumb"
  check "home: hero fetchpriority + preload" bash -c "grep -q 'fetchpriority=\"high\"' '$out' && grep -q 'rel=\"preload\" as=\"image\"' '$out'"
  check "home: data-leaflet" bash -c "grep -q 'data-leaflet' '$out'"
  check "home: FAQ aria" bash -c "[ \$(grep -o 'aria-expanded=\"true\"' '$out' | wc -l) -ge 1 ] && [ \$(grep -o 'aria-controls=\"faq-a' '$out' | wc -l) -eq 7 ]"
  check "home: CTA" bash -c "grep -q 'id=\"cta-h\"' '$out'"
  stop_server
}
export -f jsonld_types

section_oferta() {
  echo "[oferta]"
  lint_all; start_server
  local out=.superpowers/p_oferta.html
  page_common oferta /oferta/ "$out"
  eq "oferta: 8 article" "$(grep -c '<article id=' "$out")" 8
  eq "oferta: 4 is-rev" "$(grep -c 'class="svc-row is-rev"' "$out")" 4
  check "oferta: BreadcrumbList" bash -c "jsonld_types '$out' | grep -q 'BreadcrumbList=1'"
  check "oferta: kotwice PRZEJDŹ DO" bash -c "for i in holowanie naprawa akumulator opony paliwo kolizja transport oc; do grep -q \"href=\\\"#\$i\\\"\" '$out' || exit 1; done"
  stop_server
}

section_galeria() {
  echo "[galeria]"
  lint_all; start_server
  local out=.superpowers/p_galeria.html
  page_common galeria /galeria/ "$out"
  eq "galeria: 14 data-shot" "$(grep -c 'data-shot=' "$out")" 14
  eq "galeria: 4 gal-more" "$(grep -c 'class="gal-cell gal-more"' "$out")" 4
  check "galeria: bez atrybutu hidden na kafelkach" bash -c "! grep -q 'data-shot=\"[0-9]*\"[^>]* hidden' '$out'"
  check "html.js + FAQ/galeria bez JS w CSS" bash -c "grep -q \"classList.add('js')\" '$out' && grep -q '\.js \.faq-wrap' '$out' && grep -q 'html:not(.js) \[data-more-wrap\]' '$out'"
  eq "galeria: 2 is-big" "$(grep -c 'class="gal-cell is-big"' "$out")" 2
  eq "galeria: bez preload hero" "$(grep -c 'rel="preload" as="image"' "$out")" 0
  check "galeria: lightbox" bash -c "grep -q 'data-lightbox' '$out' && grep -q 'data-lbprev' '$out' && grep -q 'data-more' '$out'"
  check "galeria: BreadcrumbList" bash -c "jsonld_types '$out' | grep -q 'BreadcrumbList=1'"
  stop_server
}

section_kontakt() {
  echo "[kontakt]"
  lint_all; start_server
  local out=.superpowers/p_kontakt.html
  page_common kontakt /kontakt/ "$out"
  eq "kontakt: bez CTA" "$(grep -c 'id="cta-h"' "$out")" 0
  eq "kontakt: data-leaflet x1" "$(grep -c '<div data-leaflet' "$out")" 1
  eq "kontakt: bez iframe google" "$(grep -c 'maps.google.com/maps?q' "$out")" 0
  check "kontakt: BreadcrumbList" bash -c "jsonld_types '$out' | grep -q 'BreadcrumbList=1'"
  check "kontakt: Wyznacz trasę" bash -c "grep -q 'maps/dir/' '$out'"
  stop_server
}

section_all() {
  section_scaffold; section_images; section_core; section_partials; section_home; section_oferta; section_galeria; section_kontakt
  echo "[global]"
  eq "brak śladów DC w public_html" "$(grep -rlE --include='*.php' --include='*.js' --include='*.css' --include='*.txt' --include='.htaccess' '\{\{|<sc-|<x-dc|<helmet|hint-placeholder|support\.js|fonts\.googleapis|lh3\.googleusercontent|dc-import|_ds_bundle|unpkg' public_html | wc -l | tr -d ' ')" 0
  eq "workflow bez 'tuszyn'" "$(grep -ci tuszyn .github/workflows/deploy.yml)" 0
  check "llms.txt" test -f public_html/llms.txt
}

case "${1:-all}" in
  scaffold) section_scaffold ;; images) section_images ;; core) section_core ;; partials) section_partials ;;
  home) section_home ;; oferta) section_oferta ;; galeria) section_galeria ;; kontakt) section_kontakt ;; all) section_all ;;
  *) echo "unknown section $1"; exit 2 ;;
esac
echo "----"
if [ "$FAIL" -eq 0 ]; then echo "PASS"; else echo "FAIL: $FAIL"; exit 1; fi
