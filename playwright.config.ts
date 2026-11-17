import { defineConfig } from '@playwright/test';

// E2E_BASE_URL = host that serves /styleguide (the marketing surface locally).
const baseURL = process.env.E2E_BASE_URL ?? 'http://momenikah.test';

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  retries: process.env.CI ? 1 : 0,
  reporter: [['list'], ['html', { open: 'never' }]],
  use: { baseURL, ignoreHTTPSErrors: true, trace: 'retain-on-failure' },
  projects: [
    { name: 'mobile-360', use: { viewport: { width: 360, height: 800 }, hasTouch: true } },
    { name: 'tablet-768', use: { viewport: { width: 768, height: 1024 } } },
    { name: 'desktop-1280', use: { viewport: { width: 1280, height: 800 } } },
  ],
});
