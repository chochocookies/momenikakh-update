// DSH-12/13: drag-and-drop onto Livewire file inputs, upload progress, and gallery reorder by drag.
// Keeps Alpine out of the CSP-safe Livewire surface; talks to Livewire via $wire / component lookup.

function rootOf(el) {
  return el?.closest?.('[data-editor-media]') ?? null;
}

function setProgress(root, percent, visible) {
  const box = root?.querySelector('[data-upload-progress]');
  if (!box) return;
  box.hidden = !visible;
  const bar = box.querySelector('[data-upload-bar]');
  const label = box.querySelector('[data-upload-percent]');
  if (bar) bar.style.width = `${percent}%`;
  if (label) label.textContent = String(percent);
}

function toastError(message) {
  window.dispatchEvent(new CustomEvent('toast', { detail: { message, tone: 'error' } }));
}

function maxBytes(root) {
  const n = Number(root?.dataset.maxBytes ?? 0);
  return Number.isFinite(n) && n > 0 ? n : null;
}

function rejectOversized(root, files) {
  const max = maxBytes(root);
  if (!max) return [...files];
  const ok = [];
  for (const file of files) {
    if (file.size > max) {
      const mb = Math.max(1, Math.floor(max / (1024 * 1024)));
      toastError(`Berkas terlalu besar. Maksimal ${mb} MB.`);
      continue;
    }
    ok.push(file);
  }
  return ok;
}

function wireFrom(el) {
  const host = el.closest('[wire\\:id], [wire-id]');
  const id = host?.getAttribute('wire:id') || host?.getAttribute('wire-id');
  return id && window.Livewire ? window.Livewire.find(id) : null;
}

document.addEventListener('dragover', (e) => {
  const root = rootOf(e.target);
  if (!root) return;
  e.preventDefault();
  root.querySelector('[data-dropzone]')?.setAttribute('data-active', 'true');
});

document.addEventListener('dragleave', (e) => {
  const root = rootOf(e.target);
  const zone = root?.querySelector('[data-dropzone]');
  if (!zone) return;
  if (e.relatedTarget && zone.contains(e.relatedTarget)) return;
  zone.removeAttribute('data-active');
});

document.addEventListener('drop', (e) => {
  const root = rootOf(e.target);
  if (!root) return;
  e.preventDefault();
  const zone = root.querySelector('[data-dropzone]');
  zone?.removeAttribute('data-active');
  const input = root.querySelector('[data-upload-input]');
  if (!input || input.disabled) return;

  const files = rejectOversized(root, e.dataTransfer?.files ?? []);
  if (files.length === 0) return;

  const $wire = wireFrom(root);
  if (!$wire) return;

  const property = root.dataset.editorMedia === 'music' ? 'musicFile' : 'galleryPhotos';
  setProgress(root, 0, true);

  const onProgress = (ev) => setProgress(root, ev.detail?.progress ?? 0, true);
  const done = () => setProgress(root, 100, false);

  if (property === 'musicFile') {
    $wire.upload(property, files[0], done, () => { done(); toastError('Unggahan gagal. Coba lagi.'); }, onProgress);
  } else {
    $wire.uploadMultiple(property, files, done, () => { done(); toastError('Unggahan gagal. Coba lagi.'); }, onProgress);
  }
});

// Progress for native file picker (wire:model already uploads; we only mirror the bar).
document.addEventListener('livewire-upload-start', (e) => {
  const root = rootOf(e.target);
  if (root) setProgress(root, 0, true);
});
document.addEventListener('livewire-upload-progress', (e) => {
  const root = rootOf(e.target);
  if (root) setProgress(root, e.detail?.progress ?? 0, true);
});
['finish', 'error', 'cancel'].forEach((name) => {
  document.addEventListener(`livewire-upload-${name}`, (e) => {
    const root = rootOf(e.target);
    if (root) setProgress(root, name === 'finish' ? 100 : 0, false);
  });
});

// Gallery reorder by dragging cards.
let dragId = null;

document.addEventListener('dragstart', (e) => {
  const item = e.target.closest?.('[data-gallery-id]');
  if (!item) return;
  dragId = item.dataset.galleryId;
  e.dataTransfer?.setData('text/plain', dragId);
  e.dataTransfer.effectAllowed = 'move';
  item.classList.add('opacity-60');
});

document.addEventListener('dragend', (e) => {
  e.target.closest?.('[data-gallery-id]')?.classList.remove('opacity-60');
  dragId = null;
});

document.addEventListener('dragover', (e) => {
  if (!dragId) return;
  const item = e.target.closest?.('[data-gallery-id]');
  if (!item) return;
  e.preventDefault();
});

document.addEventListener('drop', (e) => {
  const target = e.target.closest?.('[data-gallery-id]');
  const list = target?.closest('[data-gallery-list]');
  if (!target || !list || !dragId) return;
  e.preventDefault();
  e.stopPropagation();

  const ids = [...list.querySelectorAll('[data-gallery-id]')].map((el) => el.dataset.galleryId);
  const from = ids.indexOf(String(dragId));
  const to = ids.indexOf(target.dataset.galleryId);
  if (from < 0 || to < 0 || from === to) return;

  ids.splice(to, 0, ids.splice(from, 1)[0]);
  wireFrom(list)?.call('reorderGallery', ids.map(Number));
  dragId = null;
});
