#!/usr/bin/env bash
# Deploy satu situs ke aaPanel (mode clone atau copy).
# Lihat docs/aapanel-deployment-checklist.md Bagian 2 (clone) / Bagian 3 (copy).
#
# WAJIB dikerjakan MANUAL dulu sebelum jalanin script ini:
#   1. Bikin website baru di aaPanel (domain + www, request SSL untuk keduanya)
#   2. Pastikan DNS record domain + www ada (Cloudflare dll)
#   3. Bikin database + user PostgreSQL kosong di aaPanel (nama sesuai --db-name/--db-user)
#   4. Jalankan dari DALAM folder situs ini: cd /www/wwwroot/<domain>
#
# Contoh mode clone (situs pertama di VPS / situs mandiri):
#   ./deploy-site.sh clone --domain=contoh.org \
#     --repo=https://github.com/hiskaerwi-commits/web-mbg.git \
#     --db-name=contoh_db --db-user=contoh_user --db-pass='RahasiaBanget123'
#
# Contoh mode copy (situs ke-2 dst di VPS yang sama, lebih cepat):
#   ./deploy-site.sh copy --domain=contoh.org \
#     --source=/www/wwwroot/situs-sumber.org \
#     --db-name=contoh_db --db-user=contoh_user --db-pass='RahasiaBanget123'
#
# Setelah dump di-import (langkah manual, lihat checklist Bagian 2.6/3.5),
# script ini yang jalanin grant privileges-nya otomatis.

set -euo pipefail

MODE="${1:-}"
shift || true

usage() {
    cat >&2 <<'USAGE'
Usage:
  deploy-site.sh <clone|copy> --domain=<domain> --db-name=<db> --db-user=<user> --db-pass=<pass> [opsi lain]

Wajib:
  --domain=contoh.org        Domain utama (tanpa www)
  --db-name=contoh_db        Nama database PostgreSQL (sudah dibuat manual)
  --db-user=contoh_user      User aplikasi PostgreSQL (sudah dibuat manual)
  --db-pass=xxxxx            Password user aplikasi

Mode clone wajib:
  --repo=https://github.com/.../repo.git

Mode copy wajib:
  --source=/www/wwwroot/situs-sumber.org

Opsional:
  --db-host=127.0.0.1        Default 127.0.0.1
  --db-port=5432             Default 5432
  --db-superuser=postgres    Default postgres
  --db-superuser-pass=xxxxx  Kosongin kalau auth lokal trust (default di sesi ini)
  --admin-path=ops-xxxxxx    Isi ADMIN_PANEL_PATH kalau mau beda dari default
  --yes / -y                 Skip konfirmasi interaktif
USAGE
}

if [[ "$MODE" != "clone" && "$MODE" != "copy" ]]; then
    echo "Mode pertama harus 'clone' atau 'copy'." >&2
    usage
    exit 1
fi

if [ "$(id -u)" -ne 0 ]; then
    echo "Jalankan sebagai root." >&2
    exit 1
fi

DOMAIN=""
REPO_URL=""
SOURCE_DIR=""
DB_NAME=""
DB_USER=""
DB_PASS=""
DB_HOST="127.0.0.1"
DB_PORT="5432"
DB_SUPERUSER="postgres"
DB_SUPERUSER_PASS=""
ADMIN_PATH=""
ASSUME_YES=0

for arg in "$@"; do
    case "$arg" in
        --domain=*) DOMAIN="${arg#*=}" ;;
        --repo=*) REPO_URL="${arg#*=}" ;;
        --source=*) SOURCE_DIR="${arg#*=}" ;;
        --db-name=*) DB_NAME="${arg#*=}" ;;
        --db-user=*) DB_USER="${arg#*=}" ;;
        --db-pass=*) DB_PASS="${arg#*=}" ;;
        --db-host=*) DB_HOST="${arg#*=}" ;;
        --db-port=*) DB_PORT="${arg#*=}" ;;
        --db-superuser=*) DB_SUPERUSER="${arg#*=}" ;;
        --db-superuser-pass=*) DB_SUPERUSER_PASS="${arg#*=}" ;;
        --admin-path=*) ADMIN_PATH="${arg#*=}" ;;
        --yes|-y) ASSUME_YES=1 ;;
        -h|--help) usage; exit 0 ;;
        *)
            echo "Argumen tidak dikenal: $arg" >&2
            usage
            exit 1
            ;;
    esac
done

if [ -z "$DOMAIN" ] || [ -z "$DB_NAME" ] || [ -z "$DB_USER" ] || [ -z "$DB_PASS" ]; then
    echo "Wajib isi --domain, --db-name, --db-user, --db-pass." >&2
    usage
    exit 1
fi

if [ "$MODE" = "clone" ] && [ -z "$REPO_URL" ]; then
    echo "Mode clone wajib isi --repo=<url git>." >&2
    exit 1
fi

if [ "$MODE" = "copy" ]; then
    if [ -z "$SOURCE_DIR" ]; then
        echo "Mode copy wajib isi --source=<folder situs sumber>." >&2
        exit 1
    fi
    if [ ! -d "$SOURCE_DIR" ]; then
        echo "Folder source tidak ditemukan: $SOURCE_DIR" >&2
        exit 1
    fi
fi

SITE_DIR="$(pwd)"

echo "=================================================="
echo " Ringkasan deploy"
echo "=================================================="
echo "Mode        : $MODE"
echo "Domain      : $DOMAIN (www.$DOMAIN)"
echo "Direktori   : $SITE_DIR"
if [ "$MODE" = "clone" ]; then
    echo "Repo        : $REPO_URL"
else
    echo "Source      : $SOURCE_DIR"
fi
echo "DB Name     : $DB_NAME"
echo "DB User     : $DB_USER"
echo "=================================================="
echo "Pastikan sudah dikerjakan manual sebelum lanjut:"
echo "  1. Website + domain + www + SSL sudah dibuat di aaPanel"
echo "  2. DNS record domain + www sudah ada"
echo "  3. Database '$DB_NAME' + user '$DB_USER' sudah dibuat kosong di aaPanel"
if [ "$MODE" = "clone" ]; then
    echo "  4. Dump database (kalau ada) sudah di-import ke '$DB_NAME'"
fi
echo "=================================================="

if [ "$ASSUME_YES" -ne 1 ]; then
    read -r -p "Lanjutkan? Isi folder '$SITE_DIR' akan ditimpa. (ketik 'lanjut') " CONFIRM
    if [ "$CONFIRM" != "lanjut" ]; then
        echo "Dibatalkan."
        exit 1
    fi
fi

set_env() {
    local key="$1" val="$2" esc
    esc=$(printf '%s' "$val" | sed -e 's/\\/\\\\/g; s/[&/]/\\&/g')
    if grep -qE "^${key}=" .env; then
        sed -i "s/^${key}=.*/${key}=${esc}/" .env
    else
        printf '%s=%s\n' "$key" "$val" >> .env
    fi
}

echo
echo "== 1/9 Siapin folder project =="
if [ "$MODE" = "clone" ]; then
    if [ -f .user.ini ]; then
        chattr -i .user.ini 2>/dev/null || true
    fi
    rm -f .htaccess .user.ini 404.html 502.html index.html

    TMP_CLONE_DIR=$(mktemp -d)
    git clone "$REPO_URL" "$TMP_CLONE_DIR"
    shopt -s dotglob nullglob
    mv "$TMP_CLONE_DIR"/* .
    shopt -u dotglob nullglob
    rm -rf "$TMP_CLONE_DIR"
else
    cp -a "$SOURCE_DIR"/. .
fi

chown -R www:www "$SITE_DIR"
git config --global --add safe.directory "$SITE_DIR"

if [ "$MODE" = "clone" ]; then
    echo
    echo "== 2/9 Install dependency (composer + npm) =="
    composer install --no-dev --optimize-autoloader
    npm install
else
    echo
    echo "== 2/9 Skip composer/npm install (mode copy, sudah ikut ke-copy) =="
fi

echo
echo "== 3/9 Setup .env =="
if [ "$MODE" = "clone" ]; then
    cp .env.example .env
fi

set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_URL "https://www.${DOMAIN}"
set_env DB_CONNECTION pgsql
set_env DB_HOST "$DB_HOST"
set_env DB_PORT "$DB_PORT"
set_env DB_DATABASE "$DB_NAME"
set_env DB_USERNAME "$DB_USER"
set_env DB_PASSWORD "$DB_PASS"
if [ -n "$ADMIN_PATH" ]; then
    set_env ADMIN_PANEL_PATH "$ADMIN_PATH"
fi

php artisan key:generate --force

echo "---- Cek ulang isi .env (WAJIB dicek manual, bukan cuma dipercaya) ----"
grep -E "^(APP_URL|DB_DATABASE|DB_USERNAME)=" .env
echo "------------------------------------------------------------------------"

echo
echo "== 4/9 Clear cache lama (sebelum storage:link) =="
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo
echo "== 5/9 Storage symlink =="
rm -rf public/storage
php artisan storage:link

if [ "$MODE" = "copy" ]; then
    echo
    echo "== 6/9 Isi ulang storage/app/public dari situs sumber =="
    rm -rf storage/app/public/*
    cp -r "$SOURCE_DIR"/storage/app/public/. storage/app/public/
    chown -R www:www storage/app/public
else
    echo
    echo "== 6/9 Migrate & build asset =="
    php artisan migrate --force
    npm run build
fi

echo
echo "== 7/9 Grant privileges database =="
if [ -n "$DB_SUPERUSER_PASS" ]; then
    export PGPASSWORD="$DB_SUPERUSER_PASS"
fi
psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_SUPERUSER" -d "$DB_NAME" -v ON_ERROR_STOP=1 -c "GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO \"$DB_USER\";"
psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_SUPERUSER" -d "$DB_NAME" -v ON_ERROR_STOP=1 -c "GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO \"$DB_USER\";"
psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_SUPERUSER" -d "$DB_NAME" -v ON_ERROR_STOP=1 -c "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO \"$DB_USER\";"
psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_SUPERUSER" -d "$DB_NAME" -v ON_ERROR_STOP=1 -c "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO \"$DB_USER\";"
unset PGPASSWORD

echo
echo "== 8/9 Cache ulang + permission =="
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www:www "$SITE_DIR"
chmod -R 775 storage bootstrap/cache

echo
echo "== 9/9 Selesai — sisanya manual di aaPanel =="

PHP_BIN=$(command -v php || echo /www/server/php/83/bin/php)
NPM_BIN=$(command -v npm || echo npm)

echo
echo "=================================================="
echo " 1) Nginx — paste ini ke aaPanel > Website > $DOMAIN > Config"
echo "=================================================="
cat <<EOF
server
{
    listen 80;
    listen 443 ssl;
    server_name ${DOMAIN};
    ssl_certificate     /www/server/panel/vhost/cert/${DOMAIN}/fullchain.pem;
    ssl_certificate_key /www/server/panel/vhost/cert/${DOMAIN}/privkey.pem;
    return 301 https://www.${DOMAIN}\$request_uri;
}

server
{
    listen 80;
    listen 443 ssl;
    listen 443 quic;
    listen [::]:443 ssl;
    listen [::]:443 quic;
    http2 on;
    http3 on;
    listen [::]:80;
    server_name www.${DOMAIN};
    index index.php index.html index.htm default.php default.htm default.html;
    root /www/wwwroot/${DOMAIN}/public;
    include /www/server/panel/vhost/nginx/extension/${DOMAIN}/*.conf;

    #CERT-APPLY-CHECK--START
    include /www/server/panel/vhost/nginx/well-known/${DOMAIN}.conf;
    #CERT-APPLY-CHECK--END
    #SSL-START
    ssl_certificate    /www/server/panel/vhost/cert/${DOMAIN}/fullchain.pem;
    ssl_certificate_key    /www/server/panel/vhost/cert/${DOMAIN}/privkey.pem;
    ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3;
    ssl_ciphers EECDH+CHACHA20:EECDH+CHACHA20-draft:EECDH+AES128:RSA+AES128:EECDH+AES256:RSA+AES256:EECDH+3DES:RSA+3DES:!MD5;
    ssl_prefer_server_ciphers on;
    error_page 497  https://\$host\$request_uri;
    #SSL-END

    error_page 404 /404.html;
    error_page 502 /502.html;

    include enable-php-83.conf;
    include /www/server/panel/vhost/rewrite/${DOMAIN}.conf;

    location ~ ^/(\.user.ini|\.htaccess|\.git|\.env|\.svn|\.project|LICENSE|README.md)
    {
        return 404;
    }

    location ~ \.well-known{
        allow all;
    }

    if ( \$uri ~ "^/\.well-known/.*\.(php|jsp|py|js|css|lua|ts|go|zip|tar\.gz|rar|7z|sql|bak)\$" ) {
        return 403;
    }

    location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)\$
    {
        expires      30d;
        error_log /dev/null;
        access_log /dev/null;
    }

    location ~ .*\.(js|css)?\$
    {
        try_files \$uri \$uri/ /index.php?\$query_string;
        expires      12h;
        error_log /dev/null;
        access_log /dev/null;
    }
    access_log  /www/wwwlogs/${DOMAIN}.log;
    error_log  /www/wwwlogs/${DOMAIN}.error.log;
}
EOF

echo
echo "=================================================="
echo " 2) Cron — paste ini ke aaPanel > Cron Job > Add Task (Shell Script, Daily, user root)"
echo "=================================================="
echo "cd ${SITE_DIR} && ${NPM_BIN} run fetch:bgn-press-releases && ${PHP_BIN} artisan bgn:import-press-releases bgn-press-releases.json && ${NPM_BIN} run fetch:bgn-photos && ${PHP_BIN} artisan bgn:import-photos bgn-photos.json"

echo
echo "=================================================="
echo " 3) Cek akhir manual"
echo "=================================================="
echo "- Buka https://www.${DOMAIN} setelah Nginx di-reload, cek login + captcha + gambar"
if [ -n "$ADMIN_PATH" ]; then
    echo "- Login admin: https://www.${DOMAIN}/${ADMIN_PATH}"
fi
echo "- tail -n 100 ${SITE_DIR}/storage/logs/laravel.log"
echo "- tail -n 100 /www/wwwlogs/${DOMAIN}.error.log"
echo
echo "Deploy situs $DOMAIN selesai."
