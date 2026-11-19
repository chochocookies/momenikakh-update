// Gallery lightbox on a native modal <dialog>: Escape closes, focus returns to the thumbnail, the focus trap lives in ui.js.
export function initLightbox(root = document) {
  const dialog = root.querySelector('dialog[data-lightbox]');
  if (!dialog) return null;

  const items = [...root.querySelectorAll('[data-lightbox-open]')];
  const image = dialog.querySelector('[data-lightbox-image]');
  const count = dialog.querySelector('[data-lightbox-count]');
  let index = 0;

  const show = (i) => {
    index = (i + items.length) % items.length;
    const { src, srcset, alt, width, height } = items[index].dataset;
    image.src = src;
    if (srcset) image.srcset = srcset; else image.removeAttribute('srcset');
    image.alt = alt ?? '';
    if (width) image.width = Number(width);
    if (height) image.height = Number(height);
    if (count) count.textContent = `Foto ${index + 1} dari ${items.length}`;
  };

  items.forEach((button, i) => button.addEventListener('click', () => { show(i); dialog.showModal(); }));
  dialog.querySelector('[data-lightbox-close]')?.addEventListener('click', () => dialog.close());
  dialog.querySelector('[data-lightbox-prev]')?.addEventListener('click', () => show(index - 1));
  dialog.querySelector('[data-lightbox-next]')?.addEventListener('click', () => show(index + 1));
  dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
  dialog.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') show(index - 1);
    if (e.key === 'ArrowRight') show(index + 1);
  });

  return { show, dialog };
}
