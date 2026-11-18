import { readFileSync } from 'node:fs';
import type { Page, Route } from '@playwright/test';

export const ORIGIN = 'http://fixture.test';
export const script = (name: string) => readFileSync(`resources/js/public/${name}`, 'utf8');

/** Silent 8-bit mono WAV, long enough to keep "playing" while a test inspects it. */
export function silentWav(seconds = 30, rate = 8000): Buffer {
  const n = seconds * rate;
  const b = Buffer.alloc(44 + n, 0x80);
  b.write('RIFF', 0); b.writeUInt32LE(36 + n, 4); b.write('WAVEfmt ', 8);
  b.writeUInt32LE(16, 16); b.writeUInt16LE(1, 20); b.writeUInt16LE(1, 22);
  b.writeUInt32LE(rate, 24); b.writeUInt32LE(rate, 28); b.writeUInt16LE(1, 32); b.writeUInt16LE(8, 34);
  b.write('data', 36); b.writeUInt32LE(n, 40);
  return b;
}

/** Serves bytes with Range support: Safari/WebKit refuses to play media from servers that ignore Range. */
export async function fulfillRange(route: Route, body: Buffer, contentType: string) {
  const range = route.request().headers()['range'];
  if (!range) {
    return route.fulfill({ status: 200, contentType, headers: { 'accept-ranges': 'bytes' }, body });
  }
  const m = /bytes=(\d*)-(\d*)/.exec(range)!;
  const start = m[1] ? parseInt(m[1], 10) : 0;
  const end = m[2] ? Math.min(parseInt(m[2], 10), body.length - 1) : body.length - 1;
  return route.fulfill({
    status: 206, contentType,
    headers: { 'accept-ranges': 'bytes', 'content-range': `bytes ${start}-${end}/${body.length}` },
    body: body.subarray(start, end + 1),
  });
}

export async function serve(page: Page, html: string, extra: (route: Route, url: URL) => Promise<boolean> | boolean = () => false) {
  await page.route(`${ORIGIN}/**`, async (route) => {
    const url = new URL(route.request().url());
    if (await extra(route, url)) return;
    if (url.pathname === '/guest-name.js') return route.fulfill({ contentType: 'text/javascript', body: script('guest-name.js') });
    if (url.pathname === '/music.js') return route.fulfill({ contentType: 'text/javascript', body: script('music.js') });
    return route.fulfill({ contentType: 'text/html', body: html });
  });
}
