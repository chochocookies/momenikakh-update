import { test, expect, type Page } from '@playwright/test';

// TPL-06: the invitation page in a real browser. Needs the public surface: serve with PUBLIC_DOMAIN=127.0.0.1 and
// run with E2E_PUBLIC_URL=http://127.0.0.1:<port> (default http://undangan.momenikah.test).
const PUBLIC = process.env.E2E_PUBLIC_URL ?? 'http://undangan.momenikah.test';
const FRAME = `${PUBLIC}/preview/classic-elegant/frame?event=wedding`;

const open = async (page: Page, query = '') => {
  await page.goto(`${FRAME}${query}`);
  await expect(page.locator('html.inv-js')).toHaveCount(1);
};

test.describe('cover', () => {
  test('opens from the keyboard, moves focus and unlocks the page', async ({ page }) => {
    await open(page);
    const cover = page.locator('[data-cover]');
    await expect(cover).toBeVisible();
    await expect(page.locator('#opening')).toHaveJSProperty('inert', true); // page behind the cover is inert

    await page.locator('[data-open-invitation]').focus();
    await page.keyboard.press('Enter');

    await expect(cover).toHaveAttribute('data-state', 'open');
    await expect(cover).toBeHidden({ timeout: 4000 });
    await expect(page.locator('#opening')).toHaveJSProperty('inert', false);
    await expect(page.locator('#main')).toBeFocused();
    expect(await page.evaluate(() => document.documentElement.hasAttribute('data-opened'))).toBe(true);
  });

  test('is only an overlay when JavaScript runs', async ({ browser }) => {
    const context = await browser.newContext({ javaScriptEnabled: false });
    const page = await context.newPage();
    await page.goto(FRAME);
    await expect(page.locator('#cover')).toBeVisible();
    await expect(page.locator('#opening')).toHaveCount(1); // content is in the page, not hidden behind a dead overlay
    expect(await page.locator('#cover').evaluate((el) => getComputedStyle(el).position)).not.toBe('fixed');
    await context.close();
  });

  test('closes instantly with reduced motion', async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await open(page);
    await page.locator('[data-open-invitation]').click();
    await expect(page.locator('[data-cover]')).toBeHidden({ timeout: 500 });
  });
});

test.describe('guest name from ?to=', () => {
  test('shows the placeholder without ?to=', async ({ page }) => {
    await open(page);
    await expect(page.locator('[data-guest]')).toHaveText('Tamu Undangan');
  });

  test('writes hostile input as text only and caps it at 60 characters', async ({ page }) => {
    let dialog = false;
    page.on('dialog', async (d) => { dialog = true; await d.dismiss(); });
    await open(page, `&to=${encodeURIComponent('<img src=x onerror=alert(1)>')}`);
    await expect(page.locator('[data-guest]')).toHaveText('<img src=x onerror=alert(1)>');
    expect(await page.locator('[data-guest] img').count()).toBe(0);
    expect(dialog).toBe(false);

    await open(page, `&to=${'a'.repeat(100)}`);
    expect((await page.locator('[data-guest]').textContent())!.length).toBe(60);
  });

  test('prefills the name field with .value', async ({ page }) => {
    await open(page, '&to=Ni%20Luh%20Ayu%20%26%20Keluarga');
    await expect(page.locator('#rsvp-name')).toHaveValue('Ni Luh Ayu & Keluarga');
  });
});

test.describe('music', () => {
  test('does not play before the tap and starts from the open button', async ({ page }) => {
    await open(page);
    expect(await page.locator('audio[data-music]').evaluate((a: HTMLAudioElement) => a.paused)).toBe(true);
    await page.locator('[data-open-invitation]').click();
    await expect.poll(() => page.locator('audio[data-music]').evaluate((a: HTMLAudioElement) => !a.paused || a.currentTime > 0), { timeout: 4000 }).toBe(true);
  });

  test('keeps a labelled toggle of at least 44px visible', async ({ page }) => {
    await open(page);
    await page.locator('[data-open-invitation]').click();
    const toggle = page.locator('[data-music-toggle]');
    await expect(toggle).toBeVisible();
    await expect(toggle).toHaveAttribute('aria-label', /musik/i);
    const box = (await toggle.boundingBox())!;
    expect(box.width).toBeGreaterThanOrEqual(44);
    expect(box.height).toBeGreaterThanOrEqual(44);
  });
});

test.describe('countdown', () => {
  test('counts down to a future event and never shows negative numbers', async ({ page }) => {
    await open(page);
    await page.locator('[data-open-invitation]').click();
    const days = Number(await page.locator('[data-unit="days"]').textContent());
    expect(days).toBeGreaterThanOrEqual(40);
    for (const unit of ['hours', 'minutes', 'seconds']) {
      expect(Number(await page.locator(`[data-unit="${unit}"]`).textContent())).toBeGreaterThanOrEqual(0);
    }
  });

  test('switches to a message once the event has started', async ({ page }) => {
    await page.addInitScript(() => {
      const real = Date.now;
      Date.now = () => real() + 400 * 86400 * 1000; // a year later
    });
    await open(page);
    await expect(page.locator('[data-countdown]')).toBeHidden();
    await expect(page.locator('[data-countdown-done]')).toBeVisible();
  });
});

test.describe('copy and toast', () => {
  test('copies an account number and says so', async ({ page, context }) => {
    await context.grantPermissions(['clipboard-read', 'clipboard-write'], { origin: PUBLIC }).catch(() => {});
    await open(page);
    await page.locator('[data-open-invitation]').click();
    const button = page.locator('[data-copy]').first();
    await button.scrollIntoViewIfNeeded();
    await button.click();
    await expect(page.locator('#toasts [role=status]')).toContainText(/disalin/i);
  });
});

test.describe('gallery lightbox', () => {
  test('opens, moves with the keyboard, closes with Escape and returns focus', async ({ page }) => {
    await open(page);
    await page.locator('[data-open-invitation]').click();
    const first = page.locator('[data-lightbox-open]').first();
    await first.scrollIntoViewIfNeeded();
    await first.focus();
    await page.keyboard.press('Enter');

    const dialog = page.locator('dialog[data-lightbox][open]');
    await expect(dialog).toBeVisible();
    await expect(page.locator('[data-lightbox-count]')).toHaveText('Foto 1 dari 6');
    await page.keyboard.press('ArrowRight');
    await expect(page.locator('[data-lightbox-count]')).toHaveText('Foto 2 dari 6');
    await page.keyboard.press('ArrowLeft');
    await page.keyboard.press('ArrowLeft');
    await expect(page.locator('[data-lightbox-count]')).toHaveText('Foto 6 dari 6'); // wraps

    for (let i = 0; i < 8; i++) {
      await page.keyboard.press('Tab');
      expect(await page.evaluate(() => !!document.activeElement?.closest('dialog'))).toBe(true);
    }
    await page.keyboard.press('Escape');
    await expect(page.locator('dialog[data-lightbox][open]')).toHaveCount(0);
    await expect(first).toBeFocused();
  });

  test('loads gallery thumbnails lazily and with their size', async ({ page }) => {
    await open(page);
    const imgs = page.locator('.inv-gallery img');
    expect(await imgs.count()).toBe(6);
    for (let i = 0; i < 6; i++) {
      await expect(imgs.nth(i)).toHaveAttribute('loading', 'lazy');
      await expect(imgs.nth(i)).toHaveAttribute('width', /\d+/);
      await expect(imgs.nth(i)).toHaveAttribute('height', /\d+/);
    }
  });
});

test.describe('reflow', () => {
  test('has no horizontal scroll once opened', async ({ page }) => {
    await open(page);
    await page.locator('[data-open-invitation]').click();
    await page.locator('[data-cover]').waitFor({ state: 'hidden', timeout: 4000 });
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    expect(overflow).toBeLessThanOrEqual(0);
  });

  test('reveals sections without hiding them under reduced motion', async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await open(page);
    const opacity = await page.locator('#events').evaluate((el) => getComputedStyle(el).opacity);
    expect(Number(opacity)).toBe(1);
  });
});

test.describe('ornaments', () => {
  test('one SVG takes the colour of three different themes through --t-orn', async ({ page }) => {
    await open(page);
    const colours = ['rgb(200, 30, 30)', 'rgb(20, 120, 60)', 'rgb(30, 60, 200)'];
    for (const colour of colours) {
      await page.evaluate((c) => document.querySelector<HTMLElement>('.inv')!.style.setProperty('--t-orn', c), colour);
      const result = await page.locator('#cover [data-orn="corner-flourish"]').first().evaluate((el) => ({
        span: getComputedStyle(el).color,
        stroke: getComputedStyle(el.querySelector('path')!).stroke,
      }));
      expect(result.span).toBe(colour);
      expect(result.stroke).toBe(colour);
    }
  });

  test('decorative layers never take a click', async ({ page }) => {
    await open(page);
    const hit = await page.evaluate(() => {
      const corner = document.querySelector('#cover [data-orn="corner-flourish"]')!.getBoundingClientRect();
      const el = document.elementFromPoint(corner.left + corner.width / 2, corner.top + corner.height / 2);
      return el?.closest('[data-orn]') !== null;
    });
    expect(hit).toBe(false);
    await expect(page.locator('#cover [data-orn]').first()).toHaveAttribute('aria-hidden', 'true');
  });
});

test.describe('accessibility', () => {
  test('axe finds no serious violation on the cover or on the opened invitation', async ({ page }) => {
    const { default: AxeBuilder } = await import('@axe-core/playwright');
    const scan = async () => (await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']).analyze())
      .violations.filter((v) => ['serious', 'critical'].includes(v.impact ?? ''))
      .map((v) => `${v.id} (${v.nodes.length}): ${v.help}`);

    await open(page);
    expect(await scan()).toEqual([]);

    await page.locator('[data-open-invitation]').click();
    await page.locator('[data-cover]').waitFor({ state: 'hidden', timeout: 4000 });
    expect(await scan()).toEqual([]);
  });
});
