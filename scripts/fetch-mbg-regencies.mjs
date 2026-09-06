// One-off data-fetch helper: uses a real headless Chrome (via Puppeteer) to pull
// MBG kabupaten/kota recap JSON, since the source WAF blocks plain HTTP clients
// (Laravel's Http facade / curl) but allows real browser traffic.
//
// Usage:
//   npm install puppeteer   (one-time)
//   node scripts/fetch-mbg-regencies.mjs
//   php artisan mbg:import-directory mbg-raw-data
//
// Output lands in ./mbg-raw-data at the project root — a plain, standalone folder
// (not inside storage/), so it can be archived, moved elsewhere, or deleted once
// imported without touching anything Laravel depends on.
//
// Env overrides:
//   MBG_PROVINCES=060000,020000   limit to specific province codes (comma separated)
//   MBG_LEVELS=PAUD,SD            limit to specific jenjang (comma separated)
//   MBG_DELAY_MS=1200             delay between requests in ms (default 1200)
//   MBG_OUT_DIR=./some/other/dir  override the output directory

import puppeteer from 'puppeteer';
import { mkdir, writeFile, access } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OUT_DIR = process.env.MBG_OUT_DIR
    ? path.resolve(process.env.MBG_OUT_DIR)
    : path.join(__dirname, '..', 'mbg-raw-data');

const PROVINCES = {
    '010000': 'Prov. D.K.I. Jakarta', '020000': 'Prov. Jawa Barat', '030000': 'Prov. Jawa Tengah',
    '040000': 'Prov. D.I. Yogyakarta', '050000': 'Prov. Jawa Timur', '060000': 'Prov. Aceh',
    '070000': 'Prov. Sumatera Utara', '080000': 'Prov. Sumatera Barat', '090000': 'Prov. Riau',
    '100000': 'Prov. Jambi', '110000': 'Prov. Sumatera Selatan', '120000': 'Prov. Lampung',
    '130000': 'Prov. Kalimantan Barat', '140000': 'Prov. Kalimantan Tengah', '150000': 'Prov. Kalimantan Selatan',
    '160000': 'Prov. Kalimantan Timur', '170000': 'Prov. Sulawesi Utara', '180000': 'Prov. Sulawesi Tengah',
    '190000': 'Prov. Sulawesi Selatan', '200000': 'Prov. Sulawesi Tenggara', '210000': 'Prov. Maluku',
    '220000': 'Prov. Bali', '230000': 'Prov. Nusa Tenggara Barat', '240000': 'Prov. Nusa Tenggara Timur',
    '250000': 'Prov. Papua', '260000': 'Prov. Bengkulu', '270000': 'Prov. Maluku Utara',
    '280000': 'Prov. Banten', '290000': 'Prov. Kepulauan Bangka Belitung', '300000': 'Prov. Gorontalo',
    '310000': 'Prov. Kepulauan Riau', '320000': 'Prov. Papua Barat', '330000': 'Prov. Sulawesi Barat',
    '340000': 'Prov. Kalimantan Utara', '360000': 'Prov. Papua Tengah', '370000': 'Prov. Papua Selatan',
    '380000': 'Prov. Papua Pegunungan', '390000': 'Prov. Papua Barat Daya',
};

const LEVELS = ['PAUD', 'PKBM', 'SD', 'SKB', 'SLB', 'SMA', 'SMK', 'SMP'];

const provinces = process.env.MBG_PROVINCES
    ? process.env.MBG_PROVINCES.split(',').map((v) => v.trim())
    : Object.keys(PROVINCES);
const levels = process.env.MBG_LEVELS
    ? process.env.MBG_LEVELS.split(',').map((v) => v.trim())
    : LEVELS;
const delayMs = Number(process.env.MBG_DELAY_MS ?? 1200);

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function fileExists(filePath) {
    try {
        await access(filePath);
        return true;
    } catch {
        return false;
    }
}

async function main() {
    await mkdir(OUT_DIR, { recursive: true });

    const browser = await puppeteer.launch({
        headless: true,
        args: ['--disable-blink-features=AutomationControlled', '--no-sandbox', '--disable-setuid-sandbox'],
    });

    const page = await browser.newPage();
    await page.setUserAgent(
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    );
    await page.setExtraHTTPHeaders({ 'Accept-Language': 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7' });

    let done = 0;
    let failed = 0;
    const total = provinces.length * levels.length;

    for (const provinceCode of provinces) {
        const provinceName = PROVINCES[provinceCode] ?? provinceCode;

        for (const level of levels) {
            const outFile = path.join(OUT_DIR, `${provinceCode}_${level}.json`);

            if (await fileExists(outFile)) {
                done++;
                continue;
            }

            const url = `https://mbg.pdm.kemendikdasmen.go.id/rekapsatpen/getrekapkabupaten?kode_prov=${provinceCode}&jenjang=${level}`;

            try {
                const response = await page.goto(url, { waitUntil: 'networkidle0', timeout: 30000 });
                const text = await page.evaluate(() => document.body.innerText);
                const payload = JSON.parse(text);

                if (response.status() !== 200 || payload.status !== 'success') {
                    throw new Error(`HTTP ${response.status()} / status=${payload.status}`);
                }

                await writeFile(outFile, JSON.stringify(payload));
                done++;
                console.log(`OK   ${provinceName} (${provinceCode}) / ${level} -> ${Object.keys(payload.data ?? {}).length} kabupaten`);
            } catch (error) {
                failed++;
                console.warn(`GAGAL ${provinceName} (${provinceCode}) / ${level}: ${error.message}`);
            }

            await sleep(delayMs);
        }
    }

    await browser.close();

    console.log(`\nSelesai. ${done}/${total} berhasil, ${failed} gagal.`);
    console.log(`File JSON tersimpan di: ${OUT_DIR}`);
    console.log('Lanjutkan dengan: php artisan mbg:import-directory mbg-raw-data');
    console.log('Setelah diimpor, folder ini boleh dihapus atau dipindah/diarsipkan kapan saja.');
}

main().catch((error) => {
    console.error(error);
    process.exit(1);
});
