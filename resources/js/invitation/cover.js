import { reportOpened } from '../public/guest-name';
import { reportSeen } from '../public/stats';

// Cover (envelope): an overlay that the guest opens with a tap. While it is closed the page behind it is inert,
// so keyboard and screen reader users cannot wander into content they cannot see. Music starts from the same click
// (public/music.js listens on [data-open-invitation]); it must happen inside this user gesture.
export function initCover(root = document) {
  const cover = root.querySelector('[data-cover]');
  if (!cover) {
    document.documentElement.dataset.opened = '';
    return null;
  }

  const rest = [...(cover.parentElement?.children ?? [])].filter((el) => el !== cover);
  rest.forEach((el) => { el.inert = true; });

  const quiet = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches
    || cover.closest('[data-motion="none"]') !== null;

  const open = () => {
    if (cover.dataset.state === 'open') return;
    cover.dataset.state = 'open';
    document.documentElement.dataset.opened = '';
    rest.forEach((el) => { el.inert = false; });
    reportOpened();
    reportSeen('open');
    window.dispatchEvent(new CustomEvent('momenikah:open'));

    const finish = () => { cover.hidden = true; };
    if (quiet()) finish();
    else cover.addEventListener('transitionend', finish, { once: true });

    window.scrollTo(0, 0);
    document.getElementById('main')?.focus({ preventScroll: true });
  };

  root.querySelectorAll('[data-open-invitation]').forEach((button) => button.addEventListener('click', open, { once: true }));

  return { open, cover };
}
