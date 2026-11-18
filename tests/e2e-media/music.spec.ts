import { test, expect } from '@playwright/test';
import { ORIGIN, fulfillRange, serve, silentWav } from './fixture';

const HTML = `<!doctype html><html lang="id"><body>
<button id="open" type="button" data-open-invitation>Buka undangan</button>
<audio data-music src="/song.wav" preload="none" loop playsinline></audio>
<button id="mute" type="button" data-music-toggle aria-pressed="false" aria-label="Putar musik" style="width:48px;height:48px">m</button>
<script type="module" src="/music.js"></script></body></html>`;

const WAV = silentWav();
const playState = (page: import('@playwright/test').Page) =>
  page.evaluate(() => { const a = document.querySelector('audio')!; return { paused: a.paused, loop: a.loop, autoplay: a.autoplay, t: a.currentTime }; });

test.beforeEach(async ({ page }) => {
  // Record whether play() is called while the browser still considers the click a user activation.
  await page.addInitScript(() => {
    (window as any).__playCalls = [];
    const original = HTMLMediaElement.prototype.play;
    HTMLMediaElement.prototype.play = function (...args) {
      (window as any).__playCalls.push({ active: (navigator as any).userActivation?.isActive ?? null });
      return original.apply(this, args as []);
    };
  });
  await serve(page, HTML, async (route, url) => {
    if (url.pathname === '/song.wav') { await fulfillRange(route, WAV, 'audio/wav'); return true; }
    return false;
  });
  await page.goto(`${ORIGIN}/raka-sinta-x7k2`);
});

test('nothing plays before the visitor taps', async ({ page }) => {
  await page.waitForTimeout(500);
  const s = await playState(page);
  expect(s.paused).toBe(true);
  expect(s.autoplay).toBe(false);
  expect(await page.evaluate(() => (window as any).__playCalls.length)).toBe(0);
  await expect(page.locator('#mute')).toHaveAttribute('aria-pressed', 'false');
  await expect(page.locator('#mute')).toHaveAttribute('aria-label', 'Putar musik');
});

test('plays after the tap that opens the cover', async ({ page }) => {
  await page.locator('#open').tap().catch(() => page.locator('#open').click());
  await expect.poll(async () => (await playState(page)).paused, { timeout: 5000 }).toBe(false);
  await expect.poll(async () => (await playState(page)).t, { timeout: 5000 }).toBeGreaterThan(0);
  await expect(page.locator('#mute')).toHaveAttribute('aria-pressed', 'true');
  await expect(page.locator('#mute')).toHaveAttribute('aria-label', 'Jeda musik');
  expect((await playState(page)).loop).toBe(true);
});

test('play() runs inside the user gesture (required by iOS and Android)', async ({ page }) => {
  await page.locator('#open').click();
  await expect.poll(() => page.evaluate(() => (window as any).__playCalls.length)).toBeGreaterThan(0);
  const calls = await page.evaluate(() => (window as any).__playCalls);
  for (const c of calls) {
    if (c.active !== null) expect(c.active).toBe(true);
  }
});

test('the toggle pauses and resumes', async ({ page }) => {
  await page.locator('#open').click();
  await expect.poll(async () => (await playState(page)).paused).toBe(false);
  await page.locator('#mute').click();
  await expect.poll(async () => (await playState(page)).paused).toBe(true);
  await expect(page.locator('#mute')).toHaveAttribute('aria-pressed', 'false');
  await page.locator('#mute').click();
  await expect.poll(async () => (await playState(page)).paused).toBe(false);
  await expect(page.locator('#mute')).toHaveAttribute('aria-pressed', 'true');
});

test('the toggle itself can start the music if the cover tap did not', async ({ page }) => {
  await page.locator('#mute').click();
  await expect.poll(async () => (await playState(page)).paused).toBe(false);
});

test('the toggle is a 44px touch target', async ({ page }) => {
  const box = await page.locator('#mute').boundingBox();
  expect(box!.width).toBeGreaterThanOrEqual(44);
  expect(box!.height).toBeGreaterThanOrEqual(44);
});

test('a missing audio file disables the toggle with a clear label and no crash', async ({ page }) => {
  const errors: string[] = [];
  page.on('pageerror', (e) => errors.push(e.message));
  await page.unroute(`${ORIGIN}/**`);
  await serve(page, HTML, async (route, url) => {
    if (url.pathname === '/song.wav') { await route.fulfill({ status: 404, body: 'nope' }); return true; }
    return false;
  });
  await page.goto(`${ORIGIN}/raka-sinta-x7k2`);
  await page.locator('#open').click();
  await expect(page.locator('#mute')).toHaveAttribute('aria-label', 'Musik tidak tersedia', { timeout: 5000 });
  await expect(page.locator('#mute')).toBeDisabled();
  expect(errors).toEqual([]);
});
