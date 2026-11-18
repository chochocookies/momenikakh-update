// Copy-to-clipboard for any element with data-copy="text". Shows a toast (see ui.js) and works on plain http too.
async function copyText(text) {
  try {
    await navigator.clipboard.writeText(text);
    return true;
  } catch {
    const area = document.createElement('textarea');
    area.value = text;
    area.setAttribute('readonly', '');
    area.className = 'sr-only';
    document.body.append(area);
    area.select();
    const ok = document.execCommand('copy');
    area.remove();
    return ok;
  }
}

document.addEventListener('click', async (event) => {
  const trigger = event.target.closest('[data-copy]');
  if (!trigger) return;
  const ok = await copyText(trigger.dataset.copy);
  window.dispatchEvent(new CustomEvent('toast', {
    detail: { message: ok ? (trigger.dataset.copyDone || 'Disalin.') : 'Tidak bisa menyalin. Salin secara manual.', tone: ok ? 'success' : 'error' },
  }));
});
