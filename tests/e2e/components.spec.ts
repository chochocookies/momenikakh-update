import { test, expect } from '@playwright/test';

const PAGE = '/styleguide';

test.describe('skip link and landmarks', () => {
  test('first Tab reveals a visible skip link that moves focus to main', async ({ page }) => {
    await page.goto(PAGE);
    await page.keyboard.press('Tab');
    const skip = page.locator(':focus');
    await expect(skip).toHaveAttribute('href', '#main');
    await expect(skip).toBeVisible();
    const box = await skip.boundingBox();
    expect(box!.y).toBeGreaterThanOrEqual(0);
    expect(box!.x).toBeGreaterThanOrEqual(0);
    await page.keyboard.press('Enter');
    await expect(page).toHaveURL(/#main$/);
    await expect(page.locator('main#main')).toBeFocused();
  });
});

test.describe('keyboard focus', () => {
  test('every focusable element shows a focus indicator', async ({ page }) => {
    await page.goto(PAGE);
    const missing: string[] = [];
    for (let i = 0; i < 40; i++) {
      await page.keyboard.press('Tab');
      const info = await page.evaluate(() => {
        const el = document.activeElement as HTMLElement;
        if (!el || el === document.body) return null;
        const s = getComputedStyle(el);
        const visible = s.outlineStyle !== 'none' && parseFloat(s.outlineWidth) >= 2;
        const shadow = s.boxShadow !== 'none';
        return { ok: visible || shadow, label: `${el.tagName.toLowerCase()}#${el.id}.${el.className}`.slice(0, 80) };
      });
      if (info && !info.ok) missing.push(info.label);
    }
    expect(missing).toEqual([]);
  });
});

test.describe('touch targets', () => {
  test('interactive controls are at least 44x44 CSS px', async ({ page }) => {
    await page.goto(PAGE);
    const small = await page.evaluate(() => {
      const out: string[] = [];
      const sel = 'button, [role=tab], select, textarea, input:not([type=hidden]):not([type=checkbox]):not([type=radio]), a[href]';
      document.querySelectorAll<HTMLElement>(sel).forEach((el) => {
        if (el.closest('p, li, td, dd, figcaption') && getComputedStyle(el).display === 'inline') return; // inline text links
        if (el.hasAttribute('data-allow-small') || el.classList.contains('sr-only')) return;
        const r = el.getBoundingClientRect();
        if (r.width === 0 || r.height === 0) return;
        if (r.width < 43.5 || r.height < 43.5) out.push(`${el.tagName.toLowerCase()} "${(el.textContent || el.getAttribute('aria-label') || '').trim().slice(0, 24)}" ${Math.round(r.width)}x${Math.round(r.height)}`);
      });
      document.querySelectorAll<HTMLInputElement>('input[type=checkbox], input[type=radio]').forEach((el) => {
        const t = (el.closest('label') ?? el).getBoundingClientRect();
        if (t.height < 43.5) out.push(`${el.type} target ${Math.round(t.width)}x${Math.round(t.height)}`);
      });
      return out;
    });
    expect(small).toEqual([]);
  });
});

test.describe('reflow and motion', () => {
  test('no horizontal scrolling at 360px width', async ({ page }) => {
    await page.setViewportSize({ width: 360, height: 800 });
    await page.goto(PAGE);
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    expect(overflow).toBeLessThanOrEqual(0);
  });

  test('reduced motion removes transitions on buttons', async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto(PAGE);
    const dur = await page.locator('button, a[class*="rounded-full"]').first().evaluate((el) => getComputedStyle(el).transitionDuration);
    for (const d of dur.split(',')) expect(parseFloat(d)).toBeLessThanOrEqual(0.011);
  });
});

test.describe('modal (native dialog)', () => {
  test('opens, traps focus, closes with Escape and returns focus', async ({ page }) => {
    await page.goto(PAGE);
    const trigger = page.locator('[data-modal-open]').first();
    await trigger.focus();
    await trigger.click();
    const dialog = page.locator('dialog[open]');
    await expect(dialog).toBeVisible();
    const inside = await page.evaluate(() => !!document.activeElement?.closest('dialog'));
    expect(inside).toBe(true);
    for (let i = 0; i < 12; i++) {
      await page.keyboard.press('Tab');
      expect(await page.evaluate(() => !!document.activeElement?.closest('dialog'))).toBe(true);
    }
    await page.keyboard.press('Escape');
    await expect(page.locator('dialog[open]')).toHaveCount(0);
    await expect(trigger).toBeFocused();
  });

  test('closes from the close button and from a click on the backdrop', async ({ page }) => {
    await page.goto(PAGE);
    await page.locator('[data-modal-open]').first().click();
    await page.locator('dialog[open] [data-modal-close]').first().click();
    await expect(page.locator('dialog[open]')).toHaveCount(0);

    await page.locator('[data-modal-open]').first().click();
    await page.mouse.click(2, 2);
    await expect(page.locator('dialog[open]')).toHaveCount(0);
  });
});

test.describe('tabs', () => {
  test('arrow keys, Home and End move selection and panels', async ({ page }) => {
    await page.goto(PAGE);
    const tabs = page.locator('[role=tablist]').first().locator('[role=tab]');
    const n = await tabs.count();
    expect(n).toBeGreaterThanOrEqual(2);
    await tabs.first().focus();
    await page.keyboard.press('ArrowRight');
    await expect(tabs.nth(1)).toHaveAttribute('aria-selected', 'true');
    await expect(tabs.nth(1)).toBeFocused();
    const panelId = await tabs.nth(1).getAttribute('aria-controls');
    await expect(page.locator(`#${panelId}`)).toBeVisible();
    await page.keyboard.press('End');
    await expect(tabs.nth(n - 1)).toHaveAttribute('aria-selected', 'true');
    await page.keyboard.press('ArrowRight'); // wraps
    await expect(tabs.first()).toHaveAttribute('aria-selected', 'true');
    await page.keyboard.press('Home');
    await expect(tabs.first()).toHaveAttribute('aria-selected', 'true');
    expect(await page.locator('[role=tablist]').first().locator('[aria-selected=true]').count()).toBe(1);
  });
});

test.describe('toast', () => {
  test('announces politely, can be dismissed, and escapes html', async ({ page }) => {
    await page.goto(PAGE);
    const region = page.locator('#toasts');
    await expect(region).toHaveAttribute('role', 'region');
    await page.evaluate(() => window.dispatchEvent(new CustomEvent('toast', { detail: { message: '<img src=x onerror=alert(1)> Tersimpan', tone: 'success' } })));
    const toast = region.locator('[role=status]').first();
    await expect(toast).toContainText('Tersimpan');
    expect(await toast.locator('img').count()).toBe(0);
    await toast.getByRole('button', { name: /tutup/i }).click();
    await expect(region.locator('[role=status]')).toHaveCount(0);
  });

  test('auto-dismisses', async ({ page }) => {
    await page.goto(PAGE);
    await page.evaluate(() => window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Sementara', tone: 'info' } })));
    await expect(page.locator('#toasts [role=status]')).toHaveCount(1);
    await expect(page.locator('#toasts [role=status]')).toHaveCount(0, { timeout: 9000 });
  });
});

test.describe('forms', () => {
  test('error state is announced and linked to the control', async ({ page }) => {
    await page.goto(PAGE);
    const invalid = page.locator('[aria-invalid=true]').first();
    await expect(invalid).toBeVisible();
    const described = (await invalid.getAttribute('aria-describedby')) ?? '';
    expect(described).not.toBe('');
    for (const id of described.split(' ').filter(Boolean)) {
      await expect(page.locator(`#${id}`)).toHaveCount(1);
    }
  });
});
