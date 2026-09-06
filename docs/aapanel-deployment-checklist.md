# Checklist Deploy ke VPS Baru (aaPanel + Nginx + PostgreSQL)

Urutan lengkap deploy project ini ke VPS baru, disusun dari pengalaman deploy pertama (bgnpusat.org). Ikuti urut dari atas ke bawah supaya tidak perlu debug ulang dari nol.

## 1. Install PHP 8.3

1. aaPanel → App Store → install **PHP-8.3** (project butuh PHP ^8.3, cek `composer.json`).
2. Install extension yang dibutuhkan untuk **PHP-FPM** (buat website):
   - App Store → PHP-8.3 → Settings → tab Install extension
   - Install: `pdo_pgsql` dan `pgsql`
   - Restart PHP-8.3

3. **Penting — aktifkan extension yang sama untuk CLI juga**, karena aaPanel PHP punya file config terpisah untuk web (FPM) dan terminal/cron (CLI):
   ```bash
   echo "extension = /www/server/php/83/lib/php/extensions/no-debug-non-zts-20230831/pdo_pgsql.so" >> /www/server/php/83/etc/php-cli.ini
   ```
   Path `no-debug-non-zts-20230831` ini adalah ID API Zend Engine untuk PHP 8.3.x — selalu sama selama masih pakai PHP 8.3 lewat aaPanel, jadi tinggal copy-paste langsung. Kalau suatu saat pindah ke versi PHP lain (8.2/8.4/dst), cek dulu nilai yang benar dengan:
   ```bash
   grep -i "pgsql" /www/server/php/83/etc/php.ini
   ```
   dan cari baris `extension = .../pdo_pgsql.so` di situ, lalu pakai path yang sama persis untuk `php-cli.ini`.

   Cek sudah benar:
   ```bash
   php -m | grep -i pgsql
   ```
   Harus muncul `pdo_pgsql`. Tanpa ini, artisan command lewat cron/SSH akan gagal dengan `could not find driver` walau website-nya sendiri sudah jalan normal.

## 2. Upload project & install dependency

```bash
cd /www/wwwroot/<domain>
npm install
```

## 3. Setup `.env`

- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` sesuai database Postgres di VPS ini.
- `APP_URL` isi domain final dengan `https://`, contoh `https://www.bgnpusat.org`.
- `APP_KEY` generate baru khusus untuk environment ini kalau belum ada.

## 4. Import database

1. Install client `psql` dulu kalau belum ada:
   ```bash
   apt install -y postgresql-client
   ```
2. Buat database kosong di Postgres (nama sesuai `DB_DATABASE` di `.env`).
3. Import file dump `.sql`:
   ```bash
   psql -h 127.0.0.1 -U <db_superuser> -d <db_name> -f institution_portal_xxx.sql
   ```
   (Pakai `-h 127.0.0.1` karena Postgres di VPS biasanya cuma listen TCP, bukan lewat unix socket — tanpa `-h`, psql akan error "connection to server on socket ... failed".)

4. **Wajib — kasih izin ke DB user aplikasi.** Dump di-generate dengan opsi `--no-owner`, jadi semua tabel & sequence otomatis dimiliki oleh user yang menjalankan import (`<db_superuser>`, biasanya `postgres`), BUKAN user yang dipakai aplikasi (`DB_USERNAME` di `.env`, misal `bgnpusat_user`). Tanpa langkah ini, aplikasi bisa connect & baca data, tapi akan gagal INSERT dengan error `permission denied for sequence ..._id_seq`.
   ```bash
   psql -h 127.0.0.1 -U <db_superuser> -d <db_name> -c "GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO <app_db_user>;"
   psql -h 127.0.0.1 -U <db_superuser> -d <db_name> -c "GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO <app_db_user>;"
   psql -h 127.0.0.1 -U <db_superuser> -d <db_name> -c "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO <app_db_user>;"
   psql -h 127.0.0.1 -U <db_superuser> -d <db_name> -c "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO <app_db_user>;"
   ```
   (2 baris terakhir `ALTER DEFAULT PRIVILEGES` biar tabel/sequence baru dari migrasi berikutnya otomatis ikut ke-grant juga.)

5. Cek versi PostgreSQL di VPS kompatibel dengan dump (idealnya 15+; dump terbaru sudah dibersihkan dari command `\restrict`/`\unrestrict` yang cuma dikenali psql 17, jadi aman untuk versi lebih lama juga).

## 5. Storage symlink — cek dulu, jangan asal jalanin `storage:link`

Kalau proses upload/deploy menyalin folder project apa adanya, `public/storage` di VPS bisa jadi ke-copy sebagai **folder asli**, bukan symlink. Efeknya: `php artisan storage:link` gagal dengan `The [public/storage] link already exists`, dan upload file baru dari aplikasi tidak akan muncul ke publik.

**Cek:**
```bash
ls -la public/ | grep storage
```
- Ada tanda `->` (contoh: `lrwxrwxrwx ... storage -> /path/storage/app/public`) → sudah benar, lanjut saja.
- Diawali `d` tanpa tanda panah → folder asli, perlu diperbaiki.

**Perbaikan (kalau folder asli):**
```bash
# 1. Pastikan isinya sama dulu dengan storage/app/public
diff -rq storage/app/public public/storage
# Kalau hasilnya kosong (identik), lanjut. Kalau ada beda, JANGAN lanjut — gabung manual dulu.

# 2. Amankan folder lama (jangan langsung hapus)
mv public/storage public/storage_backup

# 3. Buat symlink yang benar
php artisan storage:link

# 4. Pastikan sudah benar symlink
ls -la public/ | grep storage

# 5. Setelah dicek beberapa file bisa diakses normal via browser, baru hapus backup
rm -rf public/storage_backup
```

## 6. Nginx — wajib tambah `try_files` di block `.js`/`.css`

Template default aaPanel biasanya generate block seperti ini:

```nginx
location ~ .*\.(js|css)?$
{
    expires      12h;
    error_log /dev/null;
    access_log /dev/null;
}
```

Ini **tidak ada fallback ke PHP**, jadi request apa pun yang endingnya `.js`/`.css` dicari sebagai file fisik. Livewire generate file JS-nya secara dinamis (`/livewire-xxx/livewire.js`), bukan file fisik — jadi selalu 404, dan Livewire jadi tidak aktif sama sekali di production. Gejalanya: form yang pakai Livewire (login, dsb) kelihatan seperti reload penuh saat submit, semua isian ke-reset.

**Fix:**
```nginx
location ~ .*\.(js|css)?$
{
    try_files $uri $uri/ /index.php?$query_string;
    expires      12h;
    error_log /dev/null;
    access_log /dev/null;
}
```

Apply: aaPanel → Website → situs → tombol **Config** → cari block itu → tambah baris `try_files` → Save → restart/reload Nginx.

**Cek:** DevTools browser (tab Network) di halaman login, request ke `livewire-xxx/livewire.js` harus `200`, bukan `404`. Kalau sebelumnya sempat 404 dan domain pakai Cloudflare proxy, **purge cache Cloudflare** juga (Dashboard → Caching → Configuration → Purge Everything) karena 404 lama bisa ke-cache di edge Cloudflare.

## 7. Redirect non-www ke www (opsional)

1. aaPanel → situs → tab **Domain Management** → pastikan `domain.com` dan `www.domain.com` dua-duanya terdaftar di situs yang sama.
2. Kalau pakai SSL Let's Encrypt, waktu request/renew sertifikat centang **kedua** domain.
3. Tambahkan server block redirect terpisah di awal Config Nginx:
   ```nginx
   server {
       listen 80;
       listen 443 ssl;
       server_name domain.com;
       ssl_certificate     /www/server/panel/vhost/cert/domain.com/fullchain.pem;
       ssl_certificate_key /www/server/panel/vhost/cert/domain.com/privkey.pem;
       return 301 https://www.domain.com$request_uri;
   }
   ```
4. Restart Nginx. Pastikan `.env` → `APP_URL` juga pakai domain www yang sama.

## 8. Setup Puppeteer (buat cron pembaruan berita BGN)

Beberapa `npm run fetch:*` di project ini pakai Puppeteer (scraping halaman bgn.go.id). Butuh 3 hal supaya jalan di VPS:

1. **Download browser Chrome-nya** (paket `puppeteer` di `package.json` biasanya auto-download saat `npm install`, tapi kalau gagal/ke-skip, jalankan manual — pastikan permission `.bin` juga bisa dieksekusi):
   ```bash
   chmod +x node_modules/.bin/*
   npx puppeteer browsers install chrome
   ```
2. **Install system library** yang dibutuhkan Chrome headless (Ubuntu 24.04 — kalau distro/versi beda, sesuaikan nama paket, terutama yang ada suffix `t64`):
   ```bash
   apt-get update
   apt-get install -y \
     libatk1.0-0 libatk-bridge2.0-0 libcups2 libdrm2 libxkbcommon0 \
     libxcomposite1 libxdamage1 libxfixes3 libxrandr2 libgbm1 \
     libasound2t64 libpango-1.0-0 libcairo2 libnss3 libnspr4 \
     libxss1 libx11-xcb1 fonts-liberation libxext6 xdg-utils
   ```
   Kalau ada 1-2 nama paket "Unable to locate package", skip aja (biasanya Ubuntu rename sebagian paket per versi), lanjut yang lain.
3. **Sudah ditangani di source code**: semua script di `scripts/fetch-bgn-*.mjs` dan `scripts/fetch-mbg-regencies.mjs` sudah pakai flag `--no-sandbox` di `puppeteer.launch()`, karena cron dijalankan sebagai user `root` dan Chrome menolak jalan sebagai root tanpa flag ini. Tidak perlu diubah lagi kalau source code sudah versi terbaru — cukup pastikan file di VPS sudah sinkron dengan yang di repo lokal.

**Tes manual sebelum aktifkan cron:**
```bash
cd /www/wwwroot/<domain>
npm run fetch:bgn-press-releases
php artisan bgn:import-press-releases bgn-press-releases.json
npm run fetch:bgn-photos
php artisan bgn:import-photos bgn-photos.json
```

## 9. Setup Cron Job di aaPanel

1. aaPanel → **Cron Job** → **Add Task**
2. Task type: **Shell Script**
3. Task name: bebas, misal `Update Berita BGN Harian`
4. Execute cycle: **Daily**, jam sesuai keinginan (misal 01:00)
5. Execute user: **root**
6. Script content (sesuaikan path domain & path `npm`/`php` — cek dengan `which npm` dan `which php` kalau beda dari contoh):
   ```bash
   cd /www/wwwroot/<domain> && <path-npm> run fetch:bgn-press-releases && <path-php> artisan bgn:import-press-releases bgn-press-releases.json && <path-npm> run fetch:bgn-photos && <path-php> artisan bgn:import-photos bgn-photos.json
   ```
7. Klik **Add Task**, lalu klik **Execute** manual sekali buat tes, cek hasilnya lewat **Log**.

## 10. Cek akhir

- Buka halaman login, pastikan captcha gambar muncul dan submit form berhasil (tanpa semua field ke-reset).
- Login ke panel admin, cek menu Profile bisa ganti password.
- Cek error log kalau masih ada masalah:
  ```bash
  tail -n 100 storage/logs/laravel.log            # error Laravel
  tail -n 100 /www/wwwlogs/<domain>.error.log     # error Nginx
  ```
