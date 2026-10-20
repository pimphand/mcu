#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
: "${DATABASE_URL:?DATABASE_URL wajib}"

# Migrasi (0004) memberi grant ke role internal. Kalau role belum ada, migrasi
# berhenti di tengah transaksi dan RLS tidak pernah terpasang — jadi periksa dulu.
missing="$(
  psql "$DATABASE_URL" --no-psqlrc -At -c "
    select coalesce(string_agg(r, ', ' order by r), '')
      from unnest(array['anon', 'authenticated', 'mcu_auth', 'mcu_service', 'mcu_admin']) as r
     where not exists (select 1 from pg_roles where rolname = r)
  "
)"

if [[ -n "$missing" ]]; then
  echo "[migrate] role belum ada: $missing" >&2
  echo "[migrate] jalankan db/scripts/bootstrap-postgres.sh atau psql -f db/roles.sql lebih dulu." >&2
  exit 1
fi

for file in "$ROOT_DIR"/db/migrations/*.sql; do
  echo "[migrate] $(basename "$file")"
  psql "$DATABASE_URL" --no-psqlrc --set ON_ERROR_STOP=1 --file "$file"
done
