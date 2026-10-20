#!/usr/bin/env bash
#
# Jalankan PostgREST untuk database di DATABASE_URL (lokal, Supabase, dsb).
# Bisa dipakai tanpa Docker, termasuk ketika PostgreSQL-nya hosted.
#
#   DATABASE_URL='postgresql://postgres:pw@db.<ref>.supabase.co:5432/postgres?sslmode=require' \
#   AUTHENTICATOR_PASSWORD='pw-role-mcu_authenticator' \
#   JWT_SECRET='sama-dengan-apps/web/.env' \
#   db/scripts/postgrest.sh
#
# Variabel:
#   PGRST_DB_URI          opsional; kalau diisi, DATABASE_URL tidak dipakai
#   PGRST_AUTH_ROLE       default mcu_authenticator (role login PostgREST)
#   AUTHENTICATOR_PASSWORD password role di atas (wajib bila PGRST_DB_URI kosong)
#   POSTGREST_PORT        default 3001
#   PGRST_DB_SCHEMAS      default api,public
#   PGRST_DB_POOL         default 6
#   PGRST_DB_PREPARED_STATEMENTS
#                         otomatis false bila PGRST_DB_URI memakai port 6543
#                         (pooler transaksi Supabase)
#   POSTGREST_BIN         default postgrest
set -euo pipefail

: "${JWT_SECRET:?JWT_SECRET wajib (harus sama dengan apps/web/.env)}"
: "${PGRST_AUTH_ROLE:=mcu_authenticator}"
: "${POSTGREST_PORT:=3001}"
: "${PGRST_DB_SCHEMAS:=api,public}"
: "${POSTGREST_BIN:=postgrest}"

if ! command -v "$POSTGREST_BIN" >/dev/null 2>&1; then
  echo "PostgREST tidak ditemukan: $POSTGREST_BIN" >&2
  echo "Unduh dari https://github.com/PostgREST/postgrest/releases atau set POSTGREST_BIN." >&2
  exit 1
fi

if [[ -z "${PGRST_DB_URI:-}" ]]; then
  : "${DATABASE_URL:?DATABASE_URL atau PGRST_DB_URI wajib}"
  : "${AUTHENTICATOR_PASSWORD:?AUTHENTICATOR_PASSWORD wajib bila PGRST_DB_URI kosong}"

  # Ambil host:port/database (+ query) dari DATABASE_URL, lalu ganti kredensial
  # dengan role login PostgREST. Password di-URL-encode agar karakter khusus aman.
  target="$(printf '%s' "$DATABASE_URL" | sed -E 's#^[a-zA-Z0-9+.-]+://[^@]*@##')"
  encoded_password="$(
    printf '%s' "$AUTHENTICATOR_PASSWORD" |
      node -e "let s='';process.stdin.on('data',(d)=>s+=d).on('end',()=>process.stdout.write(encodeURIComponent(s.trim())))"
  )"

  PGRST_DB_URI="postgresql://${PGRST_AUTH_ROLE}:${encoded_password}@${target}"
fi

# Pooler transaksi (Supabase port 6543) tidak mendukung prepared statement,
# jadi default-nya dimatikan kecuali user menentukan lain.
if [[ -z "${PGRST_DB_PREPARED_STATEMENTS:-}" && "$PGRST_DB_URI" == *":6543"* ]]; then
  PGRST_DB_PREPARED_STATEMENTS=false
  echo "[postgrest] terdeteksi pooler port 6543: prepared statement dimatikan"
fi

echo "[postgrest] role=$PGRST_AUTH_ROLE schemas=$PGRST_DB_SCHEMAS port=$POSTGREST_PORT"

# Pakai export + exec (bukan `env`) supaya variabel lokal seperti
# DYLD_LIBRARY_PATH di macOS tidak dihapus oleh SIP.
export PGRST_DB_URI PGRST_DB_SCHEMAS POSTGREST_PORT JWT_SECRET
export PGRST_DB_ANON_ROLE="${PGRST_DB_ANON_ROLE:-anon}"
export PGRST_DB_EXTRA_SEARCH_PATH="${PGRST_DB_EXTRA_SEARCH_PATH:-public}"
export PGRST_DB_POOL="${PGRST_DB_POOL:-6}"
export PGRST_DB_PREPARED_STATEMENTS="${PGRST_DB_PREPARED_STATEMENTS:-true}"
export PGRST_SERVER_PORT="$POSTGREST_PORT"
export PGRST_JWT_SECRET="$JWT_SECRET"
export PGRST_LOG_LEVEL="${PGRST_LOG_LEVEL:-info}"

exec "$POSTGREST_BIN"
