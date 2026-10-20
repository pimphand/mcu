#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

: "${DATABASE_URL:?DATABASE_URL wajib, gunakan koneksi superuser/owner}"
: "${AUTHENTICATOR_PASSWORD:?AUTHENTICATOR_PASSWORD wajib}"
: "${MCU_AUTH_PASSWORD:?MCU_AUTH_PASSWORD wajib}"
: "${MCU_SERVICE_PASSWORD:?MCU_SERVICE_PASSWORD wajib}"
: "${MCU_ADMIN_PASSWORD:?MCU_ADMIN_PASSWORD wajib}"

PSQL=(psql "$DATABASE_URL" --no-psqlrc --set ON_ERROR_STOP=1)

# Nama role login PostgREST. Di Supabase default-nya `mcu_authenticator` karena
# `authenticator` milik platform; lihat db/roles.sql.
: "${PGRST_AUTH_ROLE:=mcu_authenticator}"
export PGRST_AUTH_ROLE

echo "[db] roles (login PostgREST: $PGRST_AUTH_ROLE)"
"${PSQL[@]}" \
  --set authenticator_role="$PGRST_AUTH_ROLE" \
  --set authenticator_password="$AUTHENTICATOR_PASSWORD" \
  --set auth_password="$MCU_AUTH_PASSWORD" \
  --set service_password="$MCU_SERVICE_PASSWORD" \
  --set admin_password="$MCU_ADMIN_PASSWORD" \
  --file "$ROOT_DIR/db/roles.sql"

echo "[db] migrations"
for file in "$ROOT_DIR"/db/migrations/*.sql; do
  echo "[db]  -> $(basename "$file")"
  "${PSQL[@]}" --file "$file"
done

echo "[db] seed roles & menus"
"${PSQL[@]}" --file "$ROOT_DIR/db/seed/001_roles_menus.sql"

if [[ -n "${ADMIN_USERNAME:-}" && -n "${ADMIN_PASSWORD:-}" ]]; then
  echo "[db] seed administrator"
  "${PSQL[@]}" \
    --set admin_username="$ADMIN_USERNAME" \
    --set admin_email="${ADMIN_EMAIL:-}" \
    --set admin_password="$ADMIN_PASSWORD" \
    --file "$ROOT_DIR/db/seed/002_admin.sql"
else
  echo "[db] ADMIN_USERNAME/ADMIN_PASSWORD kosong; lewati seed administrator"
fi

echo "[db] selesai"
