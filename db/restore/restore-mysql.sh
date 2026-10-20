#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
DUMP_FILE="${1:-$ROOT_DIR/db/12 oktober_medical_check_up_2026-10-17_000004.sql.gz}"

: "${MYSQL_HOST:=127.0.0.1}"
: "${MYSQL_PORT:=3306}"
: "${MYSQL_USER:=root}"
: "${MYSQL_DATABASE:=medical_check_up}"

if [[ ! -f "$DUMP_FILE" ]]; then
  echo "Dump tidak ditemukan: $DUMP_FILE" >&2
  exit 1
fi

if [[ ! "$MYSQL_DATABASE" =~ ^[A-Za-z0-9_]+$ ]]; then
  echo "MYSQL_DATABASE hanya boleh huruf, angka, dan underscore." >&2
  exit 1
fi

MYSQL_ARGS=(
  --protocol=TCP
  --host="$MYSQL_HOST"
  --port="$MYSQL_PORT"
  --user="$MYSQL_USER"
  --default-character-set=utf8mb4
)

if [[ -n "${MYSQL_PASSWORD:-}" ]]; then
  export MYSQL_PWD="$MYSQL_PASSWORD"
fi

echo "[mysql] membuat database $MYSQL_DATABASE"
mysql "${MYSQL_ARGS[@]}" \
  -e "create database if not exists \`$MYSQL_DATABASE\` character set utf8mb4 collate utf8mb4_unicode_ci"

echo "[mysql] restore $DUMP_FILE"
gzip -dc "$DUMP_FILE" | mysql "${MYSQL_ARGS[@]}" "$MYSQL_DATABASE"

echo "[mysql] verifikasi jumlah baris"
mysql "${MYSQL_ARGS[@]}" --batch --skip-column-names "$MYSQL_DATABASE" <<'SQL'
select concat('clients=', count(*)) from clients;
select concat('participants=', count(*)) from participants;
select concat('users=', count(*)) from users;
select concat('laboratoria=', count(*)) from laboratoria;
select concat('radiologis=', count(*)) from radiologis;
SQL

echo "[mysql] selesai"
