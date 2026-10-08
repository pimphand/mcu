#!/usr/bin/env bash
#
# Perbaikan nginx vhost untuk aplikasi Docker (app di 127.0.0.1:9002).
#
# Pemakaian:
#   sudo ./scripts/fix-server.sh
#   sudo ./scripts/fix-server.sh --domain klinik.dwiki.id --port 9002 --up
#
# Opsi:
#   -d, --domain   nama domain vhost        (default: klinik.dwiki.id)
#   -p, --port     port backend di loopback  (default: 9002)
#   -u, --up       jalankan `docker compose up -d` bila backend mati
#   -h, --help     bantuan

set -euo pipefail

DOMAIN="klinik.dwiki.id"
APP_PORT="9002"
RUN_UP=0
ORIG_ARGS=("$@")

usage() {
    sed -n '3,14p' "$0" | sed 's/^# \{0,1\}//'
}

while [ $# -gt 0 ]; do
    case "$1" in
        -d|--domain) DOMAIN="$2"; shift 2 ;;
        -p|--port)   APP_PORT="$2"; shift 2 ;;
        -u|--up)     RUN_UP=1; shift ;;
        -h|--help)   usage; exit 0 ;;
        *) echo "argumen tidak dikenal: $1" >&2; usage; exit 1 ;;
    esac
done

if [ "$(id -u)" -ne 0 ]; then
    echo "[info] butuh root, menjalankan ulang dengan sudo ..."
    exec sudo "$0" "${ORIG_ARGS[@]}"
fi

PASS=0
WARN=0
FAIL=0

ok()   { PASS=$((PASS + 1)); echo "  [OK]   $*"; }
warn() { WARN=$((WARN + 1)); echo "  [WARN] $*"; }
fail() { FAIL=$((FAIL + 1)); echo "  [FAIL] $*"; }
info() { echo; echo "== $* =="; }

SITE_AVAIL="/etc/nginx/sites-available/${DOMAIN}"
SITE_LINK="/etc/nginx/sites-enabled/${DOMAIN}"
APP_URL="http://127.0.0.1:${APP_PORT}/"

info "1/8 nginx terpasang dan berjalan"
if ! command -v nginx >/dev/null 2>&1; then
    fail "nginx tidak terpasang"
    echo "  -> apt install nginx"
else
    ok "nginx $(nginx -v 2>&1 | awk -F/ '{print $2}')"
    if command -v systemctl >/dev/null 2>&1 && systemctl is-active --quiet nginx; then
        ok "service systemd aktif"
    elif pgrep -x nginx >/dev/null 2>&1; then
        ok "proses nginx aktif (tanpa systemd)"
    else
        warn "nginx tidak berjalan, mencoba start"
        if command -v systemctl >/dev/null 2>&1; then
            systemctl start nginx && ok "nginx di-start" || fail "gagal start nginx"
        else
            nginx && ok "nginx di-start" || fail "gagal start nginx"
        fi
    fi
fi

info "2/8 vhost ${DOMAIN} terdaftar"
if [ -e "$SITE_LINK" ]; then
    ok "enabled: $SITE_LINK"
elif [ -f "$SITE_AVAIL" ]; then
    ln -sf "$SITE_AVAIL" "$SITE_LINK"
    ok "di-enable: $SITE_LINK -> $SITE_AVAIL"
else
    fail "vhost tidak ditemukan (sites-available maupun sites-enabled)"
    cat <<EOF
  -> buat file $SITE_AVAIL berisi:

server {
    listen 80;
    server_name ${DOMAIN};

    location / {
        proxy_pass http://127.0.0.1:${APP_PORT};
        proxy_http_version 1.1;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}

  -> lalu: ln -sf $SITE_AVAIL $SITE_LINK
EOF
fi

info "3/8 tidak ada server_name duplikat"
DUPES=$(nginx -T 2>/dev/null | grep -c "server_name.*${DOMAIN}" || true)
if [ "${DUPES:-0}" -gt 1 ]; then
    warn "server_name ${DOMAIN} muncul ${DUPES}x -> cek file lain di sites-enabled/conf.d"
else
    ok "hanya 1 vhost untuk ${DOMAIN}"
fi

info "4/8 validasi konfigurasi + reload"
if nginx -t 2>&1; then
    if command -v systemctl >/dev/null 2>&1 && systemctl list-unit-files 2>/dev/null | grep -q '^nginx'; then
        systemctl reload nginx && ok "nginx -t sukses, service di-reload" || fail "reload gagal"
    else
        nginx -s reload && ok "nginx -t sukses, nginx di-reload" || fail "reload gagal"
    fi
else
    fail "konfigurasi nginx INVALID - perbaiki dulu sebelum lanjut"
    echo "  -> file yang error ada di output nginx -t di atas"
fi

info "5/8 backend 127.0.0.1:${APP_PORT} merespons"
if curl -sI --max-time 5 "$APP_URL" | head -n 1 | grep -q 'HTTP/'; then
    ok "backend hidup ($(curl -sI --max-time 5 "$APP_URL" | head -n 1 | tr -d '\r'))"
else
    fail "backend di 127.0.0.1:${APP_PORT} tidak merespons"
    if [ "$RUN_UP" -eq 1 ]; then
        if [ -f docker-compose.yml ]; then
            docker compose up -d && ok "docker compose up -d dijalankan"
        else
            fail "--up diberikan tapi tidak ada docker-compose.yml di $(pwd)"
            echo "  -> jalankan dari folder proyek: sudo ./scripts/fix-server.sh --up"
        fi
    else
        echo "  -> jalankan dari folder proyek: docker compose up -d"
        echo "  -> atau tambahkan flag --up pada script ini"
    fi
fi

info "6/8 uji vhost dari dalam server"
RESPONSE=$(curl -sI --max-time 5 -H "Host: ${DOMAIN}" http://127.0.0.1/ 2>/dev/null | head -n 1 | tr -d '\r' || true)
if echo "$RESPONSE" | grep -qE 'HTTP/[0-9.]+ (200|301|302)'; then
    ok "vhost menjawab: ${RESPONSE}"
elif echo "$RESPONSE" | grep -q 'HTTP/'; then
    warn "vhost menjawab tapi status: ${RESPONSE}"
else
    fail "vhost tidak menjawab sama sekali"
fi

if command -v ss >/dev/null 2>&1; then
    if ss -ltnp 2>/dev/null | grep -q ':80 '; then
        LISTENER=$(ss -ltnp 2>/dev/null | grep ':80 ' | head -n 1 | grep -o 'users:((.*)' || echo "?")
        if echo "$LISTENER" | grep -qi nginx; then
            ok "port 80 dipegang nginx"
        else
            warn "port 80 dipegang proses lain: ${LISTENER}"
        fi
    else
        fail "tidak ada yang listen di port 80"
    fi
fi

info "7/8 DNS ${DOMAIN}"
PUBLIC_IP=$(curl -4 -s --max-time 5 https://api.ipify.org 2>/dev/null || curl -4 -s --max-time 5 ifconfig.me 2>/dev/null || true)
RESOLVED=$(getent ahostsv4 "${DOMAIN}" 2>/dev/null | awk '{print $1}' | sort -u | tr '\n' ' ' | sed 's/ $//')

if [ -z "$RESOLVED" ]; then
    fail "domain tidak bisa di-resolve -> DNS record (A) belum ada / belum propaged"
    echo "  -> tambahkan di DNS provider: A  ${DOMAIN}  ->  ${PUBLIC_IP:-<ip-publik-server>}"
elif [ -n "$PUBLIC_IP" ] && ! echo "$RESOLVED" | grep -q "$PUBLIC_IP"; then
    warn "DNS mengarah ke '${RESOLVED}', sedangkan IP publik server '${PUBLIC_IP}'"
    echo "  -> pastikan record A domain menunjuk ke IP server"
else
    ok "DNS -> ${RESOLVED} (IP publik server: ${PUBLIC_IP:-?})"
fi

info "8/8 firewall port 80"
if command -v ufw >/dev/null 2>&1 && ufw status 2>/dev/null | grep -q 'Status: active'; then
    ufw allow 80/tcp >/dev/null 2>&1 && ufw allow 443/tcp >/dev/null 2>&1
    ok "ufw aktif: port 80/443 diizinkan ($(ufw status | grep -c '80/tcp') aturan 80/tcp)"
elif command -v firewall-cmd >/dev/null 2>&1 && firewall-cmd --state >/dev/null 2>&1; then
    firewall-cmd --permanent --add-service=http >/dev/null 2>&1 || true
    firewall-cmd --permanent --add-service=https >/dev/null 2>&1 || true
    firewall-cmd --reload >/dev/null 2>&1 || true
    ok "firewalld: service http/https diizinkan"
else
    ok "tidak ada firewall host aktif (ufw/firewalld)"
fi

if [ -n "${PUBLIC_IP}" ]; then
    EXT=$(curl -sI --max-time 6 -H "Host: ${DOMAIN}" "http://${PUBLIC_IP}/" 2>/dev/null | head -n 1 | tr -d '\r' || true)
    if echo "$EXT" | grep -qE 'HTTP/[0-9.]+ (200|301|302)'; then
        ok "uji dari IP publik: ${EXT}"
    elif [ -n "$EXT" ]; then
        warn "uji dari IP publik balas: ${EXT}"
    else
        warn "IP publik tidak menjawab -> kemungkinan firewall/provider VPS (security group) memblokir port 80"
    fi
fi

echo
echo "=================================================="
echo " hasil: ${PASS} OK, ${WARN} WARN, ${FAIL} FAIL"
if [ "$FAIL" -gt 0 ]; then
    echo " ada langkah gagal - lihat baris [FAIL] di atas"
    exit 1
fi
echo " selesai. cek dari browser: http://${DOMAIN}/"
echo "=================================================="
