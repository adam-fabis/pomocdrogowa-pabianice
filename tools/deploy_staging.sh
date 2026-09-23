#!/usr/bin/env bash
# Deploy statycznego PHP na staging Mikrus (runbook: auto-deploy.md, zaadaptowany: bez WordPressa, bez bazy).
# Użycie: tools/deploy_staging.sh            (FREE_SLUG=<slug> — staging do usunięcia, gdy brak wolnych portów)
set -euo pipefail
SLUG=pomocdrogowa-pabianice
SUBDOMAIN=${SUBDOMAIN:-pomoc-pabianice.byst.re}   # Mikrus odrzuca etykiety >20 znaków ("pomocdrogowa-pabianice" = 22)
FREE_SLUG=${FREE_SLUG:-patecwariatec}
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOST=root@tadek198.mikrus.xyz
SSH="ssh -i $HOME/.ssh/wp-deploy-mikrus -p 10198 -o StrictHostKeyChecking=accept-new $HOST"
SCP="scp -i $HOME/.ssh/wp-deploy-mikrus -P 10198 -o StrictHostKeyChecking=accept-new"
H=/srv/wp-deploy/server-helper.sh
EMAIL=$(git -C "$ROOT" config user.email || echo "$USER@$(hostname)")

[ -f "$HOME/.ssh/wp-deploy-mikrus" ] || { echo "Brak klucza ~/.ssh/wp-deploy-mikrus (patrz auto-deploy.md)"; exit 1; }
$SSH "test -f $H" || { echo "Serwer wymaga bootstrapu (auto-deploy.md)"; exit 1; }

if ! $SSH "$H exists $SLUG"; then
  if ! PORT=$($SSH "$H port-free" 2>/dev/null) || [ -z "$PORT" ]; then
    echo "Brak wolnych portów — usuwam staging '$FREE_SLUG' (z backupem)"
    $SSH "$H remove $FREE_SLUG --backup" || true
    echo "UWAGA: wpis subdomeny stagingu '$FREE_SLUG' usuń ręcznie: https://mikr.us/panel/?a=domain"
    PORT=$($SSH "$H port-free")
  fi
  echo "Tworzę $SLUG na porcie $PORT ($SUBDOMAIN)"
  $SSH "$H create $SLUG $PORT '$EMAIL' '$SUBDOMAIN'"
fi

TMP=$(mktemp -d)
tar czf "$TMP/files.tar.gz" --exclude='.DS_Store' -C "$ROOT/public_html" .
$SSH "mkdir -p /tmp/wp-deploy-incoming/$SLUG"
$SCP "$TMP/files.tar.gz" "$HOST:/tmp/wp-deploy-incoming/$SLUG/"
$SSH "set -e; D=/srv/sites/$SLUG; mkdir -p \$D; find \$D -mindepth 1 -maxdepth 1 -exec rm -rf {} +; tar xzf /tmp/wp-deploy-incoming/$SLUG/files.tar.gz -C \$D; chown -R www-data:www-data \$D; find \$D -type d -exec chmod 755 {} +; find \$D -type f -exec chmod 644 {} +; rm -rf /tmp/wp-deploy-incoming/$SLUG"
rm -rf "$TMP"

URL="https://$SUBDOMAIN"
sleep 5
for u in / /oferta/ /galeria/ /kontakt/ /robots.txt /sitemap.xml /nie-ma; do
  printf '%-14s %s\n' "$u" "$(curl -s -o /dev/null -w '%{http_code}' "$URL$u")"
done
curl -sI "$URL/" | grep -i 'x-robots-tag' || echo "UWAGA: brak X-Robots-Tag"
echo "✓ Staging: $URL"
