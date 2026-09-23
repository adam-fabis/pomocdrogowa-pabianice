// Zrzuty ekranu stron przez Playwright (podgląd lokalny musi działać na porcie z argumentu lub 8080).
// Użycie (wymaga playwright w katalogu uruchomienia, np. .superpowers/pw): cp tools/shots.mjs .superpowers/pw/ && cd .superpowers/pw && node shots.mjs [port] [ścieżka...]   → .superpowers/shots/<strona>-<szerokość>.png
import { chromium } from 'playwright';
import fs from 'node:fs';
const port = process.argv[2] || '8080';
const paths = process.argv.slice(3).length ? process.argv.slice(3) : ['/', '/oferta/', '/galeria/', '/kontakt/'];
const widths = [390, 1280];
fs.mkdirSync('../shots', { recursive: true });
const browser = await chromium.launch();
for (const w of widths) {
  const ctx = await browser.newContext({ viewport: { width: w, height: w < 600 ? 844 : 800 }, deviceScaleFactor: 1 });
  const page = await ctx.newPage();
  const errors = [];
  page.on('pageerror', e => errors.push('pageerror: ' + e.message));
  page.on('console', m => { if (m.type() === 'error') errors.push('console: ' + m.text()); });
  for (const p of paths) {
    await page.goto(`http://127.0.0.1:${port}${p}`, { waitUntil: 'networkidle' });
    await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 120)); } window.scrollTo(0, 0); });
    await page.waitForTimeout(800);
    const name = (p === '/' ? 'home' : p.replace(/\//g, '')) + '-' + w;
    await page.screenshot({ path: `../shots/${name}.png`, fullPage: true });
    console.log('shot', name, errors.length ? errors : 'no errors');
    errors.length = 0;
  }
  await ctx.close();
}
await browser.close();
