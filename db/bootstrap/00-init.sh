#!/usr/bin/env bash
set -euo pipefail

: "${POSTGRES_DB:?POSTGRES_DB is required}"
: "${POSTGRES_USER:?POSTGRES_USER is required}"
: "${AUTHENTICATOR_PASSWORD:?AUTHENTICATOR_PASSWORD is required}"
: "${MCU_AUTH_PASSWORD:?MCU_AUTH_PASSWORD is required}"
: "${MCU_SERVICE_PASSWORD:?MCU_SERVICE_PASSWORD is required}"
: "${MCU_ADMIN_PASSWORD:?MCU_ADMIN_PASSWORD is required}"

psql_base=(
  psql
  --no-psqlrc
  --set ON_ERROR_STOP=1
  --username "$POSTGRES_USER"
  --dbname "$POSTGRES_DB"
)

# Nama role login PostgREST; `authenticator` dipakai platform terkelola
# (Supabase) sehingga default-nya `mcu_authenticator`.
: "${PGRST_AUTH_ROLE:=mcu_authenticator}"

echo "[db] creating roles (login PostgREST: $PGRST_AUTH_ROLE)"
"${psql_base[@]}" \
  --set authenticator_role="$PGRST_AUTH_ROLE" \
  --set authenticator_password="$AUTHENTICATOR_PASSWORD" \
  --set auth_password="$MCU_AUTH_PASSWORD" \
  --set service_password="$MCU_SERVICE_PASSWORD" \
  --set admin_password="$MCU_ADMIN_PASSWORD" \
  --file /db/roles.sql

echo "[db] applying migrations"
for file in /db/migrations/*.sql; do
  echo "[db]  -> $(basename "$file")"
  "${psql_base[@]}" --file "$file"
done

echo "[db] seeding roles and menus"
"${psql_base[@]}" --file /db/seed/001_roles_menus.sql

if [[ -n "${ADMIN_PASSWORD:-}" && -n "${ADMIN_USERNAME:-}" ]]; then
  echo "[db] seeding administrator"
  "${psql_base[@]}" \
    --set admin_username="$ADMIN_USERNAME" \
    --set admin_email="${ADMIN_EMAIL:-}" \
    --set admin_password="$ADMIN_PASSWORD" \
    --file /db/seed/002_admin.sql
else
  echo "[db] ADMIN_PASSWORD/ADMIN_USERNAME empty; skip administrator seed"
fi

echo "[db] bootstrap complete"
