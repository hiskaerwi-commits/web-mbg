import puppeteer from 'puppeteer';
import { mkdir, writeFile, rename } from 'node:fs/promises';
import path from 'node:path';

const sourceUrl = 'https://www.bgn.go.id/juknis';
const output = path.resolve('resources/data/juknis.json');
const browser = await puppeteer.launch({ headless: true, args: ['--no-sandbox', '--disable-setuid-sandbox'] });

try {
    const page = await browser.newPage();
    const pending = [sourceUrl];
    const visited = new Set();
    const documents = new Map();

    while (pending.length) {
        const url = pending.shift();
        if (visited.has(url)) continue;
        visited.add(url);
        await page.goto(url, { waitUntil: 'networkidle0', timeout: 45000 });
        const result = await page.$eval('main', (main) => {
            const clean = (node) => node?.textContent?.replace(/\s+/g, ' ').trim() ?? '';
            const items = [...main.querySelectorAll('a.lihatDetail')].map((link) => {
                const card = link.parentElement;
                const headings = [...card.querySelectorAll('header h3')].map(clean);
                const download = card.querySelector('.downloadBtn')?.getAttribute('onclick')?.match(/window\.open\('([^']+)'/)?.[1];
                return {
                    id: link.dataset.id,
                    title: headings[0],
                    year: Number(headings[1]),
                    type: headings[2],
                    number: headings[3],
                    status: headings[4],
                    description: card.querySelector('.prose')?.innerText.trim() ?? '',
                    source_url: link.href,
                    download_url: download,
                };
            });
            const pages = [...main.querySelectorAll('a[href]')].map((link) => link.href).filter((href) => {
                const url = new URL(href);
                return url.origin === location.origin && url.pathname === '/juknis' && url.searchParams.has('page');
            });
            return { items, pages };
        });

        if (!result.items.length) throw new Error('Daftar Juknis kosong; data lokal tidak diubah.');
        for (const item of result.items) {
            if (!item.title || !item.type || !item.status || !Number.isInteger(item.year) || !/^https:\/\/cdn-web\.bgn\.go\.id\/juknis\/.+\.pdf$/i.test(item.download_url ?? '')) {
                throw new Error(`Dokumen tidak lengkap: ${item.title ?? item.id}`);
            }
            documents.set(item.id, item);
        }
        for (const next of result.pages) if (!visited.has(next)) pending.push(next);
    }

    await mkdir(path.dirname(output), { recursive: true });
    await writeFile(`${output}.tmp`, JSON.stringify({
        source_url: sourceUrl,
        fetched_at: new Date().toISOString(),
        documents: [...documents.values()],
    }, null, 2) + '\n');
    await rename(`${output}.tmp`, output);
    console.log(`${documents.size} dokumen Juknis disimpan ke ${output}`);
} finally {
    await browser.close();
}
