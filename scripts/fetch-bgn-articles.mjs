import puppeteer from 'puppeteer';
import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';

const baseUrl = 'https://www.bgn.go.id/news/artikel/';
const outFile = path.resolve(process.env.BGN_ARTICLES_OUT ?? 'bgn-articles.json');
const indexFile = path.resolve(process.env.BGN_ARTICLE_INDEX_OUT ?? 'bgn-article-index.json');
const pages = Number(process.env.BGN_ARTICLE_PAGES ?? 15);
const delay = Number(process.env.BGN_DELAY_MS ?? 1200);
const refreshDetails = process.env.BGN_REFRESH_DETAILS === '1';
const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function readJson(file, fallback) {
    try {
        return JSON.parse(await readFile(file, 'utf8'));
    } catch {
        return fallback;
    }
}

async function saveJson(file, articles) {
    await writeFile(file, JSON.stringify({ fetchedAt: new Date().toISOString(), articles }, null, 2));
}

const index = await readJson(indexFile, { articles: [] });
const articles = new Map(index.articles.map((article) => [article.href, article]));
const collected = await readJson(outFile, { articles: [] });
const output = new Map(collected.articles.map((article) => [article.href, article]));

const browser = await puppeteer.launch({ headless: true, args: ['--no-sandbox', '--disable-setuid-sandbox'] });

try {
    const page = await browser.newPage();
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124.0.0.0 Safari/537.36');

    for (let number = 1; number <= pages; number++) {
        await page.goto(`${baseUrl}?page=${number}`, { waitUntil: 'networkidle0', timeout: 60000 });
        const items = await page.$$eval('main a[href]', (links) => links.map((link) => {
            const image = link.querySelector('img');
            const heading = link.querySelector('h1, h2, h3');
            const date = [...link.querySelectorAll('p')].find((node) => /\d{1,2}\s+\w+\s+\d{4}/.test(node.textContent));
            return { href: link.href, title: heading?.textContent?.trim(), imageUrl: image?.src, date: date?.textContent?.trim() };
        }).filter((item) => item.title && item.imageUrl && item.href.includes('/news/artikel/')));
        items.forEach((item) => articles.set(item.href, item));
        await saveJson(indexFile, [...articles.values()]);
        console.log(`Halaman ${number}/${pages}: ${items.length} artikel (${articles.size} terkumpul)`);
        await sleep(delay);
    }

    for (const item of articles.values()) {
        if (output.has(item.href) && !refreshDetails) {
            console.log(`Lewati detail tersimpan: ${item.title}`);
            continue;
        }

        await page.goto(item.href, { waitUntil: 'networkidle0', timeout: 60000 });
        const detail = await page.$eval('main', (main) => {
            const text = main.innerText;
            const documentNumber = text.match(/Nomor:\s*([^\n]+)/i)?.[1]?.trim() ?? null;
            const source = text.match(/Sumber:\s*([^\n]+)/i)?.[1]?.trim() ?? 'BGN';
            const content = main.querySelector('section.prose')?.innerHTML.trim() ?? '';

            return { content, documentNumber, source };
        });
        output.set(item.href, { ...item, ...detail, category: 'Artikel' });
        await saveJson(outFile, [...output.values()]);
        console.log(`Detail ${output.size}/${articles.size}: ${item.title}`);
        await sleep(delay);
    }

    console.log(`Selesai: ${output.size} artikel disimpan ke ${outFile}`);
} finally {
    await browser.close();
}
