// Entry for an invitation page: the cover, countdown, copy buttons with toast, lightbox, reveal, music and the
// guest name from ?to=. Written with textContent / .value only, never innerHTML. A template adds its own theme.js.
import './js-flag';
import '../ui';
import '../ui-copy';
import '../public/music';
import { applyGuestName, readGuestName } from '../public/guest-name';
import { reportSeen } from '../public/stats';
import { initCover } from './cover';
import { initCountdown } from './countdown';
import { initGuestForms } from './guest-forms';
import { initLightbox } from './lightbox';
import { initReveal } from './reveal';
import { loadTheme } from './theme-loader';

function start() {
  applyGuestName();
  const name = readGuestName();
  if (name) document.querySelectorAll('input[data-guest-input]').forEach((input) => { input.value = name; });

  initCover();
  initCountdown();
  initLightbox();
  initReveal();
  initGuestForms();
  reportSeen('view');
  loadTheme().catch(() => { /* the invitation works without its optional effects */ });
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
else start();
