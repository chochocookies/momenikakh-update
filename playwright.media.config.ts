import { defineConfig, devices } from '@playwright/test';

// Fixture-based browser tests for the public-page scripts (guest name, music). No Laravel needed.
//   npx playwright install webkit chromium
//   npx playwright test -c playwright.media.config.ts
export default defineConfig({
  testDir: './tests/e2e-media',
  fullyParallel: true,
  retries: process.env.CI ? 1 : 0,
  reporter: [['list']],
  projects: [
    { name: 'iphone-webkit', use: { ...devices['iPhone 13'] } },
    { name: 'android-chromium', use: { ...devices['Pixel 5'] } },
    { name: 'desktop-chromium', use: { viewport: { width: 1280, height: 800 } } },
  ],
});
