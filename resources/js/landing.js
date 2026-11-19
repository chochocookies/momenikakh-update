// Landing page (/): the sample phone, the template chips and the countdown of the sample invitation.
// Without this file the page is complete: the hero phone shows an opened invitation (see landing.css, .lp-js).
document.documentElement.classList.add('lp-js');

const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const hero = document.getElementById('hero');
const phones = hero ? [...hero.querySelectorAll('.ph')] : [];
const chips = [...document.querySelectorAll('.lp-chip')];
const target = Date.parse(document.querySelector('[data-countdown-target]')?.dataset.countdownTarget ?? '');
let current = 0;
let timer = 0;

function tick() {
  if (Number.isNaN(target)) return;
  const s = Math.max(0, Math.floor((target - Date.now()) / 1000));
  const parts = { d: Math.floor(s / 86400), h: Math.floor((s % 86400) / 3600), m: Math.floor((s % 3600) / 60) };
  for (const [key, value] of Object.entries(parts)) {
    document.querySelectorAll(`[data-${key}]`).forEach((el) => { el.textContent = String(value); });
  }
}

function open() {
  phones[current]?.classList.add('op');
}

function show(index) {
  current = index;
  clearTimeout(timer);
  phones.forEach((phone, i) => {
    phone.hidden = i !== index;
    phone.classList.remove('op');
  });
  chips.forEach((chip, i) => chip.setAttribute('aria-pressed', String(i === index)));
  hero?.setAttribute('aria-label', `Contoh undangan: ${phones[index]?.dataset.name ?? ''}`);
  tick();
  timer = setTimeout(open, reduce ? 100 : 1500);
}

if (phones.length > 0) {
  hero.addEventListener('click', (e) => { if (e.target.closest('[data-open]')) open(); });
  chips.forEach((chip) => chip.addEventListener('click', () => show(Number(chip.dataset.i))));
  document.getElementById('replay')?.addEventListener('click', () => show(current));
  document.querySelectorAll('[data-try]').forEach((button) => button.addEventListener('click', () => {
    show(Number(button.dataset.try));
    document.getElementById('top')?.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth' });
  }));
  show(0);
  setInterval(tick, 30000);
}
