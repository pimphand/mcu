#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
: "${DATABASE_URL:?DATABASE_URL wajib}"

PSQL=(psql "$DATABASE_URL" --no-psqlrc --set ON_ERROR_STOP=1)

echo "[seed] roles (menu & permission diisi migrasi 0012)"
"${PSQL[@]}" --file "$ROOT_DIR/db/seed/001_roles_menus.sql"

if [[ -n "${ADMIN_USERNAME:-}" && -n "${ADMIN_PASSWORD:-}" ]]; then
  echo "[seed] administrator"
  "${PSQL[@]}" \
    --set admin_username="$ADMIN_USERNAME" \
    --set admin_email="${ADMIN_EMAIL:-}" \
    --set admin_password="$ADMIN_PASSWORD" \
    --file "$ROOT_DIR/db/seed/002_admin.sql"
else
  echo "[seed] ADMIN_USERNAME/ADMIN_PASSWORD kosong; lewati administrator"
fi
