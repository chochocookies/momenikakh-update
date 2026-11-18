// Public invitation: shows the guest name from ?to= and reports "opened" from ?g=.
// The server never renders the name, so the page stays fully cacheable. Names are written with textContent only.

// Strips control characters, zero-width space, word joiners, BOM and the bidi overrides/isolates used for spoofing.
// Keeps ZWJ/ZWNJ (U+200C/D) and LRM/RLM: real names (Persian, Indic) and emoji sequences need them.
const INVISIBLE = /[\u0000-\u001F\u007F-\u009F\u200B\u2060-\u2064\u2066-\u2069\u202A-\u202E\uFEFF]/g;

export function cleanGuestName(value, max = 60) {
  if (typeof value !== 'string') return null;
  const text = value.replace(INVISIBLE, ' ').replace(/\s+/g, ' ').trim();
  if (!text) return null;
  const parts = typeof Intl !== 'undefined' && Intl.Segmenter
    ? Array.from(new Intl.Segmenter(undefined, { granularity: 'grapheme' }).segment(text), (s) => s.segment)
    : Array.from(text);
  return parts.slice(0, max).join('').trim() || null;
}

export function readGuestName(search = window.location.search, max = 60) {
  return cleanGuestName(new URLSearchParams(search).get('to'), max);
}

/** Fills every [data-guest]. Keeps the server-rendered default (e.g. "Tamu Undangan") when there is no name. */
export function applyGuestName(root = document, search = window.location.search) {
  const name = readGuestName(search);
  root.querySelectorAll('[data-guest]').forEach((el) => {
    el.textContent = name ?? el.dataset.guestDefault ?? el.textContent;
  });
  return name;
}

export function readGuestToken(search = window.location.search) {
  const token = new URLSearchParams(search).get('g');
  return token && /^[a-z0-9]{12}$/.test(token) ? token : null;
}

let reported = false;
/** Call once, from the click that opens the cover. Idempotent per page load and per browser session. */
export function reportOpened({ search = window.location.search, pathname = window.location.pathname } = {}) {
  const token = readGuestToken(search);
  if (!token || reported) return false;
  reported = true;
  try {
    if (sessionStorage.getItem(`opened:${token}`)) return false;
    sessionStorage.setItem(`opened:${token}`, '1');
  } catch { /* storage can be blocked; the server is idempotent anyway */ }
  const url = `${pathname.replace(/\/$/, '')}/opened`;
  const body = new URLSearchParams({ g: token });
  if (navigator.sendBeacon) return navigator.sendBeacon(url, body);
  fetch(url, { method: 'POST', body, keepalive: true }).catch(() => {});
  return true;
}

if (typeof document !== 'undefined') {
  document.addEventListener('DOMContentLoaded', () => applyGuestName());
}
