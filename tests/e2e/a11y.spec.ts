import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

// Pages that must have zero serious/critical axe violations. Add more as they appear.
const PAGES = ['/styleguide'];

for (const path of PAGES) {
  test(`axe: no serious violations on ${path}`, async ({ page }) => {
    await page.goto(path);
    const { violations } = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'])
      .analyze();
    const serious = violations.filter((v) => ['serious', 'critical'].includes(v.impact ?? ''));
    expect(serious.map((v) => `${v.id} (${v.nodes.length} nodes): ${v.help}`)).toEqual([]);
  });

  test(`axe: dark colour scheme on ${path}`, async ({ page }) => {
    await page.emulateMedia({ colorScheme: 'dark' });
    await page.goto(path);
    const { violations } = await new AxeBuilder({ page }).withTags(['wcag2aa']).analyze();
    const contrast = violations.filter((v) => v.id === 'color-contrast');
    expect(contrast.map((v) => v.nodes.length)).toEqual([]);
  });
}
