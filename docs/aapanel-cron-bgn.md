# Pembaruan Berita BGN di aapanel

Dokumen ini menjalankan pembaruan harian untuk 10 Siaran Pers BGN terbaru dan 5 Foto BGN terbaru sebagai carousel. Beranda Laravel akan menampilkan 5 berita BGN terbaru secara otomatis berdasarkan tanggal publikasi.

## Persiapan Server

Path proyek di VPS produksi (bgnpusat.org): `/www/wwwroot/bgnpusat.org`. Binary yang dipakai:
- PHP: `/usr/bin/php` (PHP 8.3, cek dengan `which php` kalau server lain beda)
- npm: `/www/server/nodejs/v20.19.6/bin/npm` (Node.js diinstall via App Store aapanel; cek `which npm` kalau versi Node berbeda di server lain)

1. Install Node.js LTS melalui menu `App Store` aapanel atau paket server.
2. Masuk ke folder proyek dan install dependency, termasuk Puppeteer:

```bash
cd /www/wwwroot/bgnpusat.org
npm install
```

3. Pastikan Laravel sudah dapat dijalankan dan `public/storage` sudah menjadi symbolic link. Bila belum, lihat langkah pengecekan & perbaikan di [aapanel-deployment-checklist.md](aapanel-deployment-checklist.md) (jangan langsung jalankan `php artisan storage:link` tanpa cek dulu, bisa jadi folder asli bukan symlink).

## Cron Job Harian

Di aapanel, buka `Cron` → `Add Task` → Task type `Shell Script`, atur jadwal setiap hari (misal pukul 01:00 atau 01:30), Execute user `root`, lalu isi kolom **Script content** dengan:

```bash
cd /www/wwwroot/bgnpusat.org && /www/server/nodejs/v20.19.6/bin/npm run fetch:bgn-press-releases && /usr/bin/php artisan bgn:import-press-releases bgn-press-releases.json && /www/server/nodejs/v20.19.6/bin/npm run fetch:bgn-photos && /usr/bin/php artisan bgn:import-photos bgn-photos.json
```

Kalau deploy di VPS lain dengan path/versi Node berbeda, cek dulu dengan `which npm` dan `which php` di SSH, lalu sesuaikan path di perintah Cron.

## Uji Manual

Jalankan perintah berikut dari terminal server sebelum mengaktifkan Cron:

```bash
cd /www/wwwroot/bgnpusat.org
/www/server/nodejs/v20.19.6/bin/npm run fetch:bgn-press-releases
/usr/bin/php artisan bgn:import-press-releases bgn-press-releases.json
/www/server/nodejs/v20.19.6/bin/npm run fetch:bgn-photos
/usr/bin/php artisan bgn:import-photos bgn-photos.json
```

Perintah siaran pers hanya mengganti berita berkategori `Siaran Pers`, sedangkan perintah foto hanya mengganti isi carousel. Artikel BGN yang sudah ada tidak akan dihapus.
