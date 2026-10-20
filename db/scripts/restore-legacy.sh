#!/usr/bin/env bash
#
# Restore data legacy lalu migrasikan ke PostgreSQL/PostgREST.
#
#   ETL_SOURCE=mysql (default) : restore dump ke MySQL, ETL membaca MySQL
#   ETL_SOURCE=dump            : ETL membaca file .sql.gz langsung (tanpa MySQL)
#   SEED_ADMIN=1               : (default) buat ulang akun admin dari env setelah ETL
#
# Contoh lengkap dari nol:
#   DATABASE_URL='postgres://postgres:...@127.0.0.1:5432/mcu' \
#   AUTHENTICATOR_PASSWORD=... MCU_AUTH_PASSWORD=... \
#   MCU_SERVICE_PASSWORD=... MCU_ADMIN_PASSWORD=... \
#   ADMIN_USERNAME=admin ADMIN_PASSWORD=... \
#   MYSQL_HOST=127.0.0.1 MYSQL_PORT=3306 MYSQL_USER=mcu MYSQL_PASSWORD=... \
#   BOOTSTRAP=1 db/scripts/restore-legacy.sh
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
DUMP_FILE="${1:-$ROOT_DIR/db/12 oktober_medical_check_up_2026-10-17_000004.sql.gz}"

: "${DATABASE_URL:?DATABASE_URL PostgreSQL tujuan wajib}"

ETL_SOURCE="${ETL_SOURCE:-mysql}"
BOOTSTRAP="${BOOTSTRAP:-0}"
VERIFY="${VERIFY:-1}"
# ETL mengganti isi tabel users dengan akun legacy (password tidak diketahui),
# jadi akun admin dari env di-seed ulang supaya bisa langsung login.
SEED_ADMIN="${SEED_ADMIN:-1}"

if [[ ! -f "$DUMP_FILE" ]]; then
  echo "Dump tidak ditemukan: $DUMP_FILE" >&2
  exit 1
fi

if [[ "$BOOTSTRAP" == "1" ]]; then
  echo "[setup] roles, migrasi, dan seed"
  "$ROOT_DIR/db/scripts/bootstrap-postgres.sh"
fi

if [[ "$ETL_SOURCE" == "dump" ]]; then
  echo "[etl] sumber data: dump $DUMP_FILE (tanpa MySQL)"
  ETL_ARGS=(--dump "$DUMP_FILE" --truncate)
else
  : "${MYSQL_HOST:=127.0.0.1}"
  : "${MYSQL_PORT:=3306}"
  : "${MYSQL_USER:=mcu}"
  : "${MYSQL_DATABASE:=medical_check_up}"
  : "${MYSQL_PASSWORD:=}"

  export MYSQL_HOST MYSQL_PORT MYSQL_USER MYSQL_DATABASE MYSQL_PASSWORD

  "$ROOT_DIR/db/restore/restore-mysql.sh" "$DUMP_FILE"

  echo "[etl] sumber data: MySQL $MYSQL_USER@$MYSQL_HOST:$MYSQL_PORT/$MYSQL_DATABASE"
  ETL_ARGS=(--truncate)
fi

if [[ ! -d "$ROOT_DIR/db/etl/node_modules" ]]; then
  echo "[etl] install dependency"
  npm --prefix "$ROOT_DIR/db/etl" ci
fi

echo "[etl] sumber legacy -> PostgreSQL"
DATABASE_URL="$DATABASE_URL" node "$ROOT_DIR/db/etl/migrate.mjs" "${ETL_ARGS[@]}"

if [[ "$SEED_ADMIN" == "1" ]]; then
  if [[ -n "${ADMIN_USERNAME:-}" && -n "${ADMIN_PASSWORD:-}" ]]; then
    echo "[seed] akun admin $ADMIN_USERNAME"
    psql "$DATABASE_URL" --no-psqlrc --set ON_ERROR_STOP=1 \
      --set admin_username="$ADMIN_USERNAME" \
      --set admin_email="${ADMIN_EMAIL:-}" \
      --set admin_password="$ADMIN_PASSWORD" \
      --file "$ROOT_DIR/db/seed/002_admin.sql"
  else
    echo "[seed] ADMIN_USERNAME/ADMIN_PASSWORD kosong; akun admin legacy dipakai apa adanya"
  fi
fi

if [[ "$VERIFY" == "1" ]]; then
  echo "[verify] cek data dan RLS"
  DATABASE_URL="$DATABASE_URL" "$ROOT_DIR/db/scripts/verify.sh"
fi

echo "[etl] selesai"
