import puppeteer from 'puppeteer';
import { mkdir, writeFile, rename } from 'node:fs/promises';
import path from 'node:path';

const sourceUrl = 'https://www.bgn.go.id/faq';
const output = path.resolve('resources/data/faq.json');
const browser = await puppeteer.launch({ headless: true, args: ['--no-sandbox', '--disable-setuid-sandbox'] });

try {
    const page = await browser.newPage();
    await page.goto(sourceUrl, { waitUntil: 'networkidle0', timeout: 45000 });
    const categories = await page.$$eval('dl', (lists) => {
        const clean = (node) => node?.textContent?.replace(/\s+/g, ' ').trim() ?? '';
        return lists.filter((list) => list.querySelector('.faq-button')).map((list, index) => ({
            id: `kategori-${index + 1}`,
            title: clean(list.parentElement.querySelector(':scope > span')),
            items: [...list.querySelectorAll('.faq-button')].map((button, questionIndex) => {
                const answer = document.getElementById(button.getAttribute('aria-controls'));
                const blocks = [...answer.querySelectorAll('p, ol, ul')]
                    .filter((node) => !node.parentElement.closest('ol, ul'))
                    .map((node) => node.matches('ol, ul')
                        ? { type: 'list', ordered: node.matches('ol'), items: [...node.children].map(clean).filter(Boolean) }
                        : { type: 'paragraph', text: clean(node) })
                    .filter((block) => block.type === 'list' ? block.items.length : block.text);
                if (!blocks.length && clean(answer)) blocks.push({ type: 'paragraph', text: clean(answer) });
                return { id: `faq-${index + 1}-${questionIndex + 1}`, question: clean(button.querySelector('span')), blocks };
            }),
        }));
    });

    if (!categories.length || categories.some((category) => !category.title || !category.items.length || category.items.some((item) => !item.question || !item.blocks.length))) {
        throw new Error('Konten FAQ tidak lengkap; data lokal tidak diubah.');
    }
    await mkdir(path.dirname(output), { recursive: true });
    await writeFile(`${output}.tmp`, JSON.stringify({ source_url: sourceUrl, fetched_at: new Date().toISOString(), categories }, null, 2) + '\n');
    await rename(`${output}.tmp`, output);
    console.log(JSON.stringify(categories.map((category) => ({ title: category.title, questions: category.items.length })), null, 2));
} finally {
    await browser.close();
}
