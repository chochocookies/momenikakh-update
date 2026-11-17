# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: components.spec.ts >> tabs >> arrow keys, Home and End move selection and panels
- Location: tests\e2e\components.spec.ts:112:3

# Error details

```
Error: expect(received).toBeGreaterThanOrEqual(expected)

Expected: >= 2
Received:    0
```

# Page snapshot

```yaml
- main [ref=e2]:
  - generic [ref=e4]:
    - heading "404" [level=1] [ref=e5]
    - generic [ref=e6]: Not Found
```

# Test source

```ts
  16  |     await expect(page).toHaveURL(/#main$/);
  17  |     await expect(page.locator('main#main')).toBeFocused();
  18  |   });
  19  | });
  20  | 
  21  | test.describe('keyboard focus', () => {
  22  |   test('every focusable element shows a focus indicator', async ({ page }) => {
  23  |     await page.goto(PAGE);
  24  |     const missing: string[] = [];
  25  |     for (let i = 0; i < 40; i++) {
  26  |       await page.keyboard.press('Tab');
  27  |       const info = await page.evaluate(() => {
  28  |         const el = document.activeElement as HTMLElement;
  29  |         if (!el || el === document.body) return null;
  30  |         const s = getComputedStyle(el);
  31  |         const visible = s.outlineStyle !== 'none' && parseFloat(s.outlineWidth) >= 2;
  32  |         const shadow = s.boxShadow !== 'none';
  33  |         return { ok: visible || shadow, label: `${el.tagName.toLowerCase()}#${el.id}.${el.className}`.slice(0, 80) };
  34  |       });
  35  |       if (info && !info.ok) missing.push(info.label);
  36  |     }
  37  |     expect(missing).toEqual([]);
  38  |   });
  39  | });
  40  | 
  41  | test.describe('touch targets', () => {
  42  |   test('interactive controls are at least 44x44 CSS px', async ({ page }) => {
  43  |     await page.goto(PAGE);
  44  |     const small = await page.evaluate(() => {
  45  |       const out: string[] = [];
  46  |       const sel = 'button, [role=tab], select, textarea, input:not([type=hidden]):not([type=checkbox]):not([type=radio]), a[href]';
  47  |       document.querySelectorAll<HTMLElement>(sel).forEach((el) => {
  48  |         if (el.closest('p, li, td, dd, figcaption') && getComputedStyle(el).display === 'inline') return; // inline text links
  49  |         if (el.hasAttribute('data-allow-small') || el.classList.contains('sr-only')) return;
  50  |         const r = el.getBoundingClientRect();
  51  |         if (r.width === 0 || r.height === 0) return;
  52  |         if (r.width < 43.5 || r.height < 43.5) out.push(`${el.tagName.toLowerCase()} "${(el.textContent || el.getAttribute('aria-label') || '').trim().slice(0, 24)}" ${Math.round(r.width)}x${Math.round(r.height)}`);
  53  |       });
  54  |       document.querySelectorAll<HTMLInputElement>('input[type=checkbox], input[type=radio]').forEach((el) => {
  55  |         const t = (el.closest('label') ?? el).getBoundingClientRect();
  56  |         if (t.height < 43.5) out.push(`${el.type} target ${Math.round(t.width)}x${Math.round(t.height)}`);
  57  |       });
  58  |       return out;
  59  |     });
  60  |     expect(small).toEqual([]);
  61  |   });
  62  | });
  63  | 
  64  | test.describe('reflow and motion', () => {
  65  |   test('no horizontal scrolling at 360px width', async ({ page }) => {
  66  |     await page.setViewportSize({ width: 360, height: 800 });
  67  |     await page.goto(PAGE);
  68  |     const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
  69  |     expect(overflow).toBeLessThanOrEqual(0);
  70  |   });
  71  | 
  72  |   test('reduced motion removes transitions on buttons', async ({ page }) => {
  73  |     await page.emulateMedia({ reducedMotion: 'reduce' });
  74  |     await page.goto(PAGE);
  75  |     const dur = await page.locator('button, a[class*="rounded-full"]').first().evaluate((el) => getComputedStyle(el).transitionDuration);
  76  |     for (const d of dur.split(',')) expect(parseFloat(d)).toBeLessThanOrEqual(0.011);
  77  |   });
  78  | });
  79  | 
  80  | test.describe('modal (native dialog)', () => {
  81  |   test('opens, traps focus, closes with Escape and returns focus', async ({ page }) => {
  82  |     await page.goto(PAGE);
  83  |     const trigger = page.locator('[data-modal-open]').first();
  84  |     await trigger.focus();
  85  |     await trigger.click();
  86  |     const dialog = page.locator('dialog[open]');
  87  |     await expect(dialog).toBeVisible();
  88  |     const inside = await page.evaluate(() => !!document.activeElement?.closest('dialog'));
  89  |     expect(inside).toBe(true);
  90  |     for (let i = 0; i < 12; i++) {
  91  |       await page.keyboard.press('Tab');
  92  |       expect(await page.evaluate(() => !!document.activeElement?.closest('dialog'))).toBe(true);
  93  |     }
  94  |     await page.keyboard.press('Escape');
  95  |     await expect(page.locator('dialog[open]')).toHaveCount(0);
  96  |     await expect(trigger).toBeFocused();
  97  |   });
  98  | 
  99  |   test('closes from the close button and from a click on the backdrop', async ({ page }) => {
  100 |     await page.goto(PAGE);
  101 |     await page.locator('[data-modal-open]').first().click();
  102 |     await page.locator('dialog[open] [data-modal-close]').first().click();
  103 |     await expect(page.locator('dialog[open]')).toHaveCount(0);
  104 | 
  105 |     await page.locator('[data-modal-open]').first().click();
  106 |     await page.mouse.click(2, 2);
  107 |     await expect(page.locator('dialog[open]')).toHaveCount(0);
  108 |   });
  109 | });
  110 | 
  111 | test.describe('tabs', () => {
  112 |   test('arrow keys, Home and End move selection and panels', async ({ page }) => {
  113 |     await page.goto(PAGE);
  114 |     const tabs = page.locator('[role=tablist]').first().locator('[role=tab]');
  115 |     const n = await tabs.count();
> 116 |     expect(n).toBeGreaterThanOrEqual(2);
      |               ^ Error: expect(received).toBeGreaterThanOrEqual(expected)
  117 |     await tabs.first().focus();
  118 |     await page.keyboard.press('ArrowRight');
  119 |     await expect(tabs.nth(1)).toHaveAttribute('aria-selected', 'true');
  120 |     await expect(tabs.nth(1)).toBeFocused();
  121 |     const panelId = await tabs.nth(1).getAttribute('aria-controls');
  122 |     await expect(page.locator(`#${panelId}`)).toBeVisible();
  123 |     await page.keyboard.press('End');
  124 |     await expect(tabs.nth(n - 1)).toHaveAttribute('aria-selected', 'true');
  125 |     await page.keyboard.press('ArrowRight'); // wraps
  126 |     await expect(tabs.first()).toHaveAttribute('aria-selected', 'true');
  127 |     await page.keyboard.press('Home');
  128 |     await expect(tabs.first()).toHaveAttribute('aria-selected', 'true');
  129 |     expect(await page.locator('[role=tablist]').first().locator('[aria-selected=true]').count()).toBe(1);
  130 |   });
  131 | });
  132 | 
  133 | test.describe('toast', () => {
  134 |   test('announces politely, can be dismissed, and escapes html', async ({ page }) => {
  135 |     await page.goto(PAGE);
  136 |     const region = page.locator('#toasts');
  137 |     await expect(region).toHaveAttribute('role', 'region');
  138 |     await page.evaluate(() => window.dispatchEvent(new CustomEvent('toast', { detail: { message: '<img src=x onerror=alert(1)> Tersimpan', tone: 'success' } })));
  139 |     const toast = region.locator('[role=status]').first();
  140 |     await expect(toast).toContainText('Tersimpan');
  141 |     expect(await toast.locator('img').count()).toBe(0);
  142 |     await toast.getByRole('button', { name: /tutup/i }).click();
  143 |     await expect(region.locator('[role=status]')).toHaveCount(0);
  144 |   });
  145 | 
  146 |   test('auto-dismisses', async ({ page }) => {
  147 |     await page.goto(PAGE);
  148 |     await page.evaluate(() => window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Sementara', tone: 'info' } })));
  149 |     await expect(page.locator('#toasts [role=status]')).toHaveCount(1);
  150 |     await expect(page.locator('#toasts [role=status]')).toHaveCount(0, { timeout: 9000 });
  151 |   });
  152 | });
  153 | 
  154 | test.describe('forms', () => {
  155 |   test('error state is announced and linked to the control', async ({ page }) => {
  156 |     await page.goto(PAGE);
  157 |     const invalid = page.locator('[aria-invalid=true]').first();
  158 |     await expect(invalid).toBeVisible();
  159 |     const described = (await invalid.getAttribute('aria-describedby')) ?? '';
  160 |     expect(described).not.toBe('');
  161 |     for (const id of described.split(' ').filter(Boolean)) {
  162 |       await expect(page.locator(`#${id}`)).toHaveCount(1);
  163 |     }
  164 |   });
  165 | });
  166 | 
```