// Modal: <button data-modal-open="id"> membuka <dialog id="id">
document.addEventListener('click', (e) => {
  const open = e.target.closest('[data-modal-open]');
  if (open) return document.getElementById(open.dataset.modalOpen)?.showModal();
  if (e.target.closest('[data-modal-close]')) return e.target.closest('dialog')?.close();
  if (e.target instanceof HTMLDialogElement) e.target.close();           // klik backdrop
});

// Toast: window.dispatchEvent(new CustomEvent('toast', { detail: { message, tone } }))
const TONES = { success: 'bg-ink text-ivory', error: 'bg-danger text-white', info: 'bg-rose text-white' };
export function toast(message, tone = 'success') {
  const region = document.getElementById('toasts'); if (!region) return;
  const el = document.createElement('div');
  el.setAttribute('role', 'status');
  el.className = `flex items-center justify-between gap-3 rounded-xl px-4 py-3 shadow-lg ${TONES[tone] ?? TONES.info}`;
  const p = document.createElement('p'); p.textContent = message;           // textContent, bukan innerHTML
  const x = document.createElement('button'); x.type = 'button'; x.textContent = '×';
  x.className = 'min-h-11 min-w-11 text-xl'; x.setAttribute('aria-label', 'Tutup notifikasi'); x.onclick = () => el.remove();
  el.append(p, x); region.append(el);
  let t = setTimeout(() => el.remove(), 6000);
  el.addEventListener('mouseenter', () => clearTimeout(t));
  el.addEventListener('mouseleave', () => (t = setTimeout(() => el.remove(), 3000)));
}
window.addEventListener('toast', (e) => toast(e.detail?.message ?? '', e.detail?.tone));
document.addEventListener('DOMContentLoaded', () =>
  document.querySelectorAll('[data-toast]').forEach((n) => toast(n.dataset.toast, n.dataset.tone)));

// Tabs: pola ARIA (panah kiri/kanan, Home, End)
function selectTab(tab, focus = false) {
  tab.closest('[role=tablist]').querySelectorAll('[role=tab]').forEach((t) => {
    const on = t === tab;
    t.setAttribute('aria-selected', on); t.tabIndex = on ? 0 : -1;
    document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
  });
  if (focus) tab.focus();
}
document.addEventListener('click', (e) => { const t = e.target.closest('[role=tab]'); if (t) selectTab(t); });
document.addEventListener('keydown', (e) => {
  const tab = e.target.closest('[role=tab]'); if (!tab) return;
  const tabs = [...tab.closest('[role=tablist]').querySelectorAll('[role=tab]')], i = tabs.indexOf(tab);
  const next = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: tabs.length - 1 }[e.key];
  if (next === undefined) return;
  e.preventDefault(); selectTab(tabs[(next + tabs.length) % tabs.length], true);
});
