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

export function initGuestForms() {
  const rsvp = document.querySelector('[data-rsvp-form]');
  if (rsvp) wire(rsvp);

  // A preview (template catalogue, editor) has its forms disabled and no real wishes to load.
  const preview = document.querySelector('[data-wishes-form] fieldset[disabled]') !== null;
  const list = preview ? null : document.querySelector('[data-wishes]');
  const reload = list ? initWishes(list) : null;
  const wishes = document.querySelector('[data-wishes-form]');
  if (wishes) wire(wishes, reload);
}
