import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

// The landing page lives on the marketing surface: E2E_BASE_URL.
test.describe('landing page', () => {
  test('shows the headline and a phone that opens by itself', async ({ page }) => {
    await page.goto('/');
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Satu link untuk semua momen nikahmu.');
    await expect(page.locator('html.lp-js')).toHaveCount(1);
    await expect(page.locator('#hero .ph:not([hidden])')).toHaveClass(/\bop\b/, { timeout: 4000 });
  });

  test('switches the sample template with the chips and replays the opening', async ({ page }) => {
    await page.goto('/');
    const chips = page.locator('.lp-chip');
    await chips.nth(3).click();
    await expect(chips.nth(3)).toHaveAttribute('aria-pressed', 'true');
    await expect(chips.nth(0)).toHaveAttribute('aria-pressed', 'false');
    await expect(page.locator('#hero .ph:not([hidden])')).toHaveClass(/t-lux/);
    await expect(page.locator('#hero')).toHaveAttribute('aria-label', /Luxury Gold/);
    expect(await page.locator('#hero .ph:not([hidden])').count()).toBe(1);

    await expect(page.locator('#hero .ph:not([hidden])')).toHaveClass(/\bop\b/, { timeout: 4000 });
    await page.locator('#replay').click();
    await expect(page.locator('#hero .ph:not([hidden])')).not.toHaveClass(/\bop\b/);
    await expect(page.locator('#hero .ph:not([hidden])')).toHaveClass(/\bop\b/, { timeout: 4000 });
  });

  test('opens the phone from its own button', async ({ page }) => {
    await page.clock.install(); // freeze timers so the automatic opening cannot race with the click
    await page.goto('/');
    await expect(page.locator('#hero .ph:not([hidden])')).not.toHaveClass(/\bop\b/);
    await page.locator('#hero .ph:not([hidden]) [data-open]').click();
    await expect(page.locator('#hero .ph:not([hidden])')).toHaveClass(/\bop\b/);
  });

  test('"Coba template" in the gallery picks that template and scrolls to the top', async ({ page }) => {
    await page.goto('/');
    await page.locator('[data-try="1"]').scrollIntoViewIfNeeded();
    await page.locator('[data-try="1"]').click();
    await expect(page.locator('#hero .ph:not([hidden])')).toHaveClass(/t-beach/);
    await expect.poll(() => page.evaluate(() => window.scrollY), { timeout: 4000 }).toBeLessThan(200);
  });

  test('counts down to a future sample date', async ({ page }) => {
    await page.goto('/');
    const days = Number(await page.locator('#hero .ph:not([hidden]) [data-d]').textContent());
    expect(days).toBeGreaterThanOrEqual(80);
  });

  test('sends "Buat undangan" to the registration page', async ({ page }) => {
    await page.goto('/');
    const href = await page.getByRole('link', { name: 'Buat undangan' }).first().getAttribute('href');
    expect(href).toMatch(/\/register$/);
  });

  test('has no horizontal scroll and every control is at least 44px', async ({ page }) => {
    await page.goto('/');
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    expect(overflow).toBeLessThanOrEqual(0);
    const small = await page.evaluate(() => {
      const out: string[] = [];
      document.querySelectorAll<HTMLElement>('main button, main a[href], header a[href]').forEach((el) => {
        if (el.closest('[aria-hidden="true"]') || el.classList.contains('sr-only')) return;
        const r = el.getBoundingClientRect();
        if (r.width === 0 || r.height === 0) return;
        if (r.height < 43.5 || r.width < 43.5) out.push(`${el.tagName} "${(el.textContent || '').trim().slice(0, 24)}" ${Math.round(r.width)}x${Math.round(r.height)}`);
      });
      return out;
    });
    expect(small).toEqual([]);
  });

  test('shows the final state at once with reduced motion', async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto('/');
    await expect(page.locator('#hero .ph:not([hidden])')).toHaveClass(/\bop\b/, { timeout: 1000 });
    const animated = await page.evaluate(() => [...document.querySelectorAll('.lp *')].filter((el) => {
      const s = getComputedStyle(el);
      return s.animationName !== 'none' && parseFloat(s.animationDuration) > 0.011;
    }).length);
    expect(animated).toBe(0);
  });

  test('is readable without JavaScript', async ({ browser }) => {
    const context = await browser.newContext({ javaScriptEnabled: false });
    const page = await context.newPage();
    await page.goto('/');
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
    await expect(page.locator('#hero .ph:not([hidden]) .in p').first()).toBeVisible(); // the phone is simply open
    await expect(page.locator('#hero .ph:not([hidden]) .cv')).toBeHidden();
    await context.close();
  });

  test('axe finds no serious violation', async ({ page }) => {
    await page.goto('/');
    await page.locator('#hero .ph:not([hidden])').waitFor();
    await page.waitForTimeout(3500); // let the opening transition finish before measuring contrast
    const { violations } = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']).analyze();
    const serious = violations.filter((v) => ['serious', 'critical'].includes(v.impact ?? ''));
    expect(serious.map((v) => `${v.id} (${v.nodes.length}): ${v.help} → ${v.nodes.slice(0, 2).map((n) => n.target.join(' ')).join(' | ')}`)).toEqual([]);
  });
});
