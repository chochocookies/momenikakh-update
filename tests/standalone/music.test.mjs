import { JSDOM } from 'jsdom';
const dom = new JSDOM(`<!doctype html><body>
<button id="open" type="button" data-open-invitation>Buka</button>
<audio data-music loop></audio>
<button id="t" type="button" data-music-toggle aria-pressed="false" aria-label="Putar musik"></button></body>`, { url: 'http://x.test/' });
globalThis.window = dom.window; globalThis.document = dom.window.document; globalThis.Event = dom.window.Event; globalThis.MouseEvent = dom.window.MouseEvent;
let fail = 0; const ok = (n, c) => { console.log(c ? '  ok   ' : '  FAIL ', n); if (!c) fail++; };

// Fake browser autoplay policy: play() only resolves while a user gesture is active.
const audio = document.querySelector('audio');
const policy = { gesture: false, calls: [] };
let paused = true;
Object.defineProperty(audio, 'paused', { get: () => paused });
audio.play = () => { policy.calls.push(policy.gesture); if (!policy.gesture) return Promise.reject(new dom.window.DOMException('blocked', 'NotAllowedError')); paused = false; audio.dispatchEvent(new Event('playing')); return Promise.resolve(); };
audio.pause = () => { paused = true; audio.dispatchEvent(new Event('pause')); };
const tap = (el) => { policy.gesture = true; el.dispatchEvent(new MouseEvent('click', { bubbles: true })); policy.gesture = false; };
const flush = () => new Promise((r) => setTimeout(r, 5));

const { initMusic } = await import('../../resources/js/public/music.js');
const api = initMusic(document);
const t = document.getElementById('t');
ok('initialises', !!api);
ok('nothing played before the tap', policy.calls.length === 0 && paused);
ok('initial label', t.getAttribute('aria-label') === 'Putar musik' && t.getAttribute('aria-pressed') === 'false');

tap(document.getElementById('open')); await flush();
ok('cover tap calls play() inside the gesture', policy.calls.length === 1 && policy.calls[0] === true);
ok('music playing + UI updated', !paused && t.getAttribute('aria-pressed') === 'true' && t.getAttribute('aria-label') === 'Jeda musik');
tap(document.getElementById('open')); await flush();
ok('cover handler runs once only', policy.calls.length === 1);

tap(t); await flush();
ok('toggle pauses', paused && t.getAttribute('aria-pressed') === 'false' && t.dataset.state === 'paused');
tap(t); await flush();
ok('toggle resumes', !paused && t.dataset.state === 'playing');

// hidden tab
Object.defineProperty(document, 'hidden', { configurable: true, get: () => true });
document.dispatchEvent(new Event('visibilitychange')); await flush();
ok('pauses when the tab is hidden', paused);
Object.defineProperty(document, 'hidden', { configurable: true, get: () => false });
document.dispatchEvent(new Event('visibilitychange')); await flush();
ok('resume attempt outside a gesture is rejected gracefully (UI shows paused, toggle still usable)', paused && t.dataset.state === 'paused' && !t.disabled);
tap(t); await flush();
ok('visitor can resume with the toggle', !paused);

// policy rejection from a non-gesture call
api.pause();
api.play(); await flush();
ok('play() rejected by the browser leaves a consistent UI', paused && t.dataset.state === 'paused');

// error event
audio.dispatchEvent(new Event('error')); await flush();
ok('load error disables the toggle with a clear label', t.disabled && t.getAttribute('aria-label') === 'Musik tidak tersedia' && t.dataset.state === 'error');

// autostart disabled
const dom2 = new JSDOM(`<button id="o" type="button" data-open-invitation>x</button><audio data-music data-autostart="false"></audio><button type="button" data-music-toggle></button>`);
globalThis.document = dom2.window.document;
const a2 = dom2.window.document.querySelector('audio'); let calls2 = 0; a2.play = () => { calls2++; return Promise.resolve(); }; Object.defineProperty(a2, 'paused', { get: () => true });
initMusic(dom2.window.document);
dom2.window.document.getElementById('o').dispatchEvent(new dom2.window.MouseEvent('click', { bubbles: true }));
ok('data-autostart="false" does not start music on open', calls2 === 0);
ok('initMusic is idempotent (same instance)', initMusic(dom.window.document) === api);
ok('no audio or toggle -> initMusic returns null', initMusic(new JSDOM('<p>x</p>').window.document) === null);
process.exit(fail ? 1 : 0);
