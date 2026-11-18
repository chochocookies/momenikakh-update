import { test, expect } from '@playwright/test';
import { ORIGIN, serve } from './fixture';

const HTML = `<!doctype html><html lang="id"><body>
<h1>Kepada Yth.</h1>
<p><span id="name" data-guest data-guest-default="Tamu Undangan">Tamu Undangan</span></p>
<button id="open" type="button" data-open-invitation>Buka undangan</button>
<script type="module">
  import { reportOpened } from '/guest-name.js';
  document.getElementById('open').addEventListener('click', () => reportOpened());
</script></body></html>`;

const name = async (page: import('@playwright/test').Page) => (await page.locator('#name').textContent())!;

test.beforeEach(async ({ page }) => {
  await serve(page, HTML);
});

test.describe('?to= shows the right guest name', () => {
  const cases: Array<[string, string]> = [
    ['Budi%20Santoso', 'Budi Santoso'],
    ['Budi+Santoso', 'Budi Santoso'],
    ['Ni%20Luh%20Ayu%20%26%20Keluarga', 'Ni Luh Ayu & Keluarga'],
    ["O'Brien", "O'Brien"],
    ['Budi%2FAni', 'Budi/Ani'],
    ['Zo%C3%AB%20%E6%97%A5%E6%9C%AC', 'Zoë 日本'],
    ['%D9%85%DB%8C%E2%80%8C%D8%AE%D9%88%D8%A7%D9%87%D9%85', 'می‌خواهم'],
  ];
  for (const [raw, expected] of cases) {
    test(`?to=${raw}`, async ({ page }) => {
      await page.goto(`${ORIGIN}/raka-sinta-x7k2?to=${raw}&g=abc123def456`);
      await expect(page.locator('#name')).toHaveText(expected);
    });
  }

  test('keeps the default when there is no name or only spaces', async ({ page }) => {
    await page.goto(`${ORIGIN}/raka-sinta-x7k2`);
    await expect(page.locator('#name')).toHaveText('Tamu Undangan');
    await page.goto(`${ORIGIN}/raka-sinta-x7k2?to=%20%20`);
    await expect(page.locator('#name')).toHaveText('Tamu Undangan');
    await page.goto(`${ORIGIN}/raka-sinta-x7k2?to=`);
    await expect(page.locator('#name')).toHaveText('Tamu Undangan');
  });

  test('truncates very long names to 60 characters', async ({ page }) => {
    await page.goto(`${ORIGIN}/x?to=${'a'.repeat(200)}`);
    expect((await name(page)).length).toBe(60);
  });
});

test.describe('hostile names are only ever text', () => {
  const payloads = [
    '<img src=x onerror=alert(1)>',
    '<script>alert(1)</script>',
    '"><svg onload=alert(1)>',
    "javascript:alert(1)",
    '{{7*7}}',
    '${7*7}',
    '</span><h1>pwned</h1>',
  ];
  for (const p of payloads) {
    test(`payload ${p}`, async ({ page }) => {
      let dialog = false;
      page.on('dialog', async (d) => { dialog = true; await d.dismiss(); });
      await page.goto(`${ORIGIN}/raka-sinta-x7k2?to=${encodeURIComponent(p)}`);
      await expect(page.locator('#name')).toHaveText(p);          // shown literally
      expect(await page.locator('body img, body svg, body h1:has-text("pwned")').count()).toBe(0);
      expect(await page.locator('#name *').count()).toBe(0);       // no child elements were created
      await page.waitForTimeout(150);
      expect(dialog).toBe(false);
    });
  }

  test('bidi override and control characters are removed', async ({ page }) => {
    await page.goto(`${ORIGIN}/x?to=${encodeURIComponent('Ahmad\u202Eevil\u0000\u200B')}`);
    const text = await name(page);
    expect(text).not.toMatch(/[\u202E\u0000\u200B]/);
  });
});

test.describe('opened beacon (?g=)', () => {
  test('reports once, only after the tap, to /{slug}/opened', async ({ page }) => {
    const posts: string[] = [];
    await page.unroute(`${ORIGIN}/**`);
    await serve(page, HTML, async (route, url) => {
      if (url.pathname.endsWith('/opened')) {
        posts.push(`${url.pathname} ${route.request().postData()}`);
        await route.fulfill({ status: 204 });
        return true;
      }
      return false;
    });
    await page.goto(`${ORIGIN}/raka-sinta-x7k2?to=Budi&g=abc123def456`);
    await page.waitForTimeout(200);
    expect(posts).toEqual([]);                                   // nothing before the tap
    await page.locator('#open').click();
    await expect.poll(() => posts.length).toBe(1);
    expect(posts[0]).toBe('/raka-sinta-x7k2/opened g=abc123def456');
    await page.locator('#open').click();
    await page.waitForTimeout(200);
    expect(posts.length).toBe(1);                                // idempotent
  });

  test('ignores a malformed token', async ({ page }) => {
    const posts: string[] = [];
    await page.unroute(`${ORIGIN}/**`);
    await serve(page, HTML, async (route, url) => {
      if (url.pathname.endsWith('/opened')) { posts.push(url.pathname); await route.fulfill({ status: 204 }); return true; }
      return false;
    });
    await page.goto(`${ORIGIN}/x?g=BAD!`);
    await page.locator('#open').click();
    await page.waitForTimeout(200);
    expect(posts).toEqual([]);
  });
});
