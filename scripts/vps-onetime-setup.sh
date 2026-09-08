#!/usr/bin/env bash
# Setup satu kali per VPS baru (aaPanel + Ubuntu), sebelum deploy situs pertama.
# Lihat docs/aapanel-deployment-checklist.md Bagian 1 untuk detail tiap langkah.
#
# Bisa dijalankan langsung dari repo yang sudah di-clone, ATAU sebelum ada
# kode sama sekali di VPS (repo ini public) via:
#   curl -o vps-onetime-setup.sh https://raw.githubusercontent.com/hiskaerwi-commits/web-mbg/main/scripts/vps-onetime-setup.sh
#   bash vps-onetime-setup.sh
#
# Idempotent: aman dijalankan ulang kalau sebagian langkah sudah pernah jalan.

set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
    echo "Jalankan sebagai root (aaPanel biasanya root by default)." >&2
    exit 1
fi

echo "== 1/4 Update Composer =="
if ! command -v composer >/dev/null 2>&1; then
    echo "Composer tidak ditemukan di PATH. Install Composer dulu lewat aaPanel App Store, lalu jalankan ulang script ini." >&2
    exit 1
fi
composer self-update
composer --version

echo
echo "== 2/4 Install PostgreSQL client =="
if command -v psql >/dev/null 2>&1; then
    echo "psql sudah ada: $(psql --version)"
else
    apt-get update -qq
    apt-get install -y postgresql-client
fi

echo
echo "== 3/4 Symlink Node.js ke /usr/local/bin (biar kepake di semua sesi termasuk cron) =="
NODE_DIR=""
if [ -d /www/server/nodejs ]; then
    # Ambil folder versi node terbaru (skip folder "cache")
    NODE_DIR=$(find /www/server/nodejs -maxdepth 1 -type d -name 'v*' | sort -V | tail -n1)
fi

if [ -z "$NODE_DIR" ]; then
    echo "Tidak ketemu folder Node.js di /www/server/nodejs/. Install dulu lewat aaPanel App Store, lalu jalankan ulang script ini." >&2
    exit 1
fi

echo "Pakai Node.js dari: $NODE_DIR"
for bin in node npm npx; do
    if [ -f "$NODE_DIR/bin/$bin" ]; then
        ln -sf "$NODE_DIR/bin/$bin" "/usr/local/bin/$bin"
    fi
done
hash -r
node -v
npm -v

echo
echo "== 4/4 Install system library buat Puppeteer/Chrome headless =="
apt-get update -qq
apt-get install -y \
    libatk1.0-0 libatk-bridge2.0-0 libcups2 libdrm2 libxkbcommon0 \
    libxcomposite1 libxdamage1 libxfixes3 libxrandr2 libgbm1 \
    libasound2t64 libpango-1.0-0 libcairo2 libnss3 libnspr4 \
    libxss1 libx11-xcb1 fonts-liberation libxext6 xdg-utils \
    || echo "Beberapa paket mungkin gak ketemu (beda nama per versi Ubuntu) — itu normal, lanjut aja kalau library utama udah kepasang."

echo
echo "=================================================="
echo " Setup satu kali VPS ini selesai. Ringkasan versi:"
echo "=================================================="
echo "Composer : $(composer --version)"
echo "Node.js  : $(node -v)"
echo "npm      : $(npm -v)"
echo "psql     : $(psql --version)"
echo
echo "Lanjut deploy situs pertama pakai scripts/deploy-site.sh (mode clone)."
