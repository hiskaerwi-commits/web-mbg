import puppeteer from 'puppeteer';
import { writeFile } from 'node:fs/promises';
import path from 'node:path';

const baseUrl = 'https://www.bgn.go.id/news/foto/';
const outFile = path.resolve(process.env.BGN_PHOTOS_OUT ?? 'bgn-photos.json');
const limit = Number(process.env.BGN_PHOTO_LIMIT ?? 5);
const delay = Number(process.env.BGN_DELAY_MS ?? 1200);
const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

const browser = await puppeteer.launch({ headless: true, args: ['--no-sandbox', '--disable-setuid-sandbox'] });

try {
    const page = await browser.newPage();
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124.0.0.0 Safari/537.36');
    await page.goto(baseUrl, { waitUntil: 'networkidle0', timeout: 60000 });

    const photos = await page.$$eval('main a[href]', (links) => links.map((link) => {
        const image = link.querySelector('img');
        const heading = link.querySelector('h1, h2, h3');
        const date = [...link.querySelectorAll('p')].find((node) => /\d{1,2}\s+\w+\s+\d{4}/.test(node.textContent));
        return { href: link.href, title: heading?.textContent?.trim(), imageUrl: image?.src, date: date?.textContent?.trim() };
    }).filter((item) => item.title && item.imageUrl && item.href.includes('/news/foto/'))).then((items) => items.slice(0, limit));

    const output = [];
    for (const [index, item] of photos.entries()) {
        await page.goto(item.href, { waitUntil: 'networkidle0', timeout: 60000 });
        const detail = await page.$eval('main', (main) => {
            const text = main.innerText;
            return {
                content: main.querySelector('section.prose')?.innerHTML.trim() ?? '',
                documentNumber: text.match(/Nomor:\s*([^\n]+)/i)?.[1]?.trim() ?? null,
                source: text.match(/Sumber:\s*([^\n]+)/i)?.[1]?.trim() ?? 'BGN',
            };
        });

        output.push({ ...item, ...detail, category: 'Foto' });
        console.log(`Detail ${index + 1}/${photos.length}: ${item.title}`);
        await sleep(delay);
    }

    await writeFile(outFile, JSON.stringify({ fetchedAt: new Date().toISOString(), articles: output }, null, 2));
    console.log(`Selesai: ${output.length} foto BGN disimpan ke ${outFile}`);
} finally {
    await browser.close();
}
