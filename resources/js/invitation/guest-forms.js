// PUB-09 / PUB-10: sends the RSVP and wish forms, and loads the wishes in batches (cursor). Everything the guests wrote is
// written back with textContent, never innerHTML. Without JavaScript the page still reads; the forms need it to send.
import { toast } from '../ui';

const guestToken = () => new URLSearchParams(window.location.search).get('g');

async function post(form) {
  const body = new FormData(form);
  const g = guestToken();
  if (g) body.set('g', g);
  const res = await fetch(form.action, { method: 'POST', body, headers: { Accept: 'application/json' } });
  const json = await res.json().catch(() => ({}));
  if (res.ok) return json;
  const first = json.errors ? Object.values(json.errors)[0]?.[0] : null;
  throw new Error(first || json.message || 'Belum terkirim. Coba lagi sebentar lagi.');
}

function wire(form, onDone) {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const button = form.querySelector('button[type=submit]');
    button.disabled = true;
    try {
      const json = await post(form);
      toast(json.message ?? 'Terkirim.');
      form.reset();
      onDone?.();
    } catch (err) {
      toast(err.message, 'error');
    } finally {
      button.disabled = false;
    }
  });
}

function wishItem({ name, message }) {
  const li = document.createElement('li');
  li.className = 'inv-wish';
  const who = document.createElement('strong'); who.textContent = name;
  const text = document.createElement('p'); text.textContent = message;
  li.append(who, text);
  return li;
}

function initWishes(list) {
  const url = list.dataset.wishesUrl;
  let next = null;
  const more = document.createElement('button');
  more.type = 'button'; more.className = 'inv-button inv-button--ghost'; more.textContent = 'Muat ucapan lainnya'; more.hidden = true;
  // After the "no wishes yet" note: the stylesheet hides that note with a sibling selector right behind the list.
  (list.parentElement.querySelector('[data-wishes-empty]') ?? list).after(more);

  async function load(reset = false) {
    if (reset) { list.replaceChildren(); next = null; }
    const res = await fetch(next ? `${url}?cursor=${encodeURIComponent(next)}` : url, { headers: { Accept: 'application/json' } });
    if (!res.ok) return;
    const json = await res.json();
    list.append(...json.data.map(wishItem));
    next = json.next;
    more.hidden = !next;
  }
  more.addEventListener('click', () => load().catch(() => {}));
  load().catch(() => {});

  return () => load(true).catch(() => {});
}

/**
 * Initialize Cloudflare Turnstile widgets (PUB-09).
 * The widget renders into .inv-turnstile containers that have a data-cf-turnstile-site-key attribute.
 * We need to ensure the Turnstile script is loaded and render the widget in each container.
 */
function initTurnstileWidgets() {
  const siteKey = document.querySelector('[data-cf-turnstile-site-key]')?.dataset.cfTurnstileSiteKey;
  if (!siteKey) return;

  // Load Turnstile script if not already loaded
  if (!document.querySelector('script[src*="challenges.cloudflare.com"]')) {
    const script = document.createElement('script');
    script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
  }

  // Render widgets in all .inv-turnstile containers
  document.querySelectorAll('.inv-turnstile[data-cf-turnstile-site-key]').forEach((container) => {
    // Only render once
    if (container.dataset.turnstileRendered === 'true') return;
    container.dataset.turnstileRendered = 'true';

    // Turnstile uses a callback to provide the token
    window.turnstile?.render(container, {
      sitekey: siteKey,
      callback: (token) => {
        // Add the token as a hidden input to the associated form
        const form = container.closest('form');
        if (form) {
          let input = form.querySelector('input[name="cf-turnstile-response"]');
          if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'cf-turnstile-response';
            form.appendChild(input);
          }
          input.value = token;
        }
      },
      'expired-callback': () => {
        // Clear expired token
        const form = container.closest('form');
        if (form) {
          const input = form.querySelector('input[name="cf-turnstile-response"]');
          if (input) input.value = '';
        }
      },
      'error-callback': () => {
        // Clear on error
        const form = container.closest('form');
        if (form) {
          const input = form.querySelector('input[name="cf-turnstile-response"]');
          if (input) input.value = '';
        }
      },
    });
  });
}

export function initGuestForms() {
  const rsvp = document.querySelector('[data-rsvp-form]');
  if (rsvp) wire(rsvp);

  // A preview (template catalogue, editor) has its forms disabled and no real wishes to load.
  const preview = document.querySelector('[data-wishes-form] fieldset[disabled]') !== null;
  const list = preview ? null : document.querySelector('[data-wishes]');
  const reload = list ? initWishes(list) : null;
  const wishes = document.querySelector('[data-wishes-form]');
  if (wishes) wire(wishes, reload);

  // Initialize Cloudflare Turnstile widgets (PUB-09)
  initTurnstileWidgets();
}
