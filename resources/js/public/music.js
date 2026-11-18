// Background music for the public invitation (MED-05).
// Browsers (iOS Safari especially) only start audio from a user gesture, so play() is called synchronously
// inside the click that opens the cover. Nothing plays before that tap. A visible toggle is always available.

const LABEL = { play: 'Putar musik', pause: 'Jeda musik', error: 'Musik tidak tersedia' };
const instances = new WeakMap(); // initMusic() is idempotent: the auto-init and an explicit call must not double-bind

export function initMusic(root = document) {
  const audio = root.querySelector('audio[data-music]');
  const toggle = root.querySelector('[data-music-toggle]');
  if (!audio || !toggle) return null;
  if (instances.has(audio)) return instances.get(audio);

  let wantsPlay = false;   // the visitor asked for music (tap on the cover or on the toggle)
  let resumeOnShow = false;

  const setUi = (state) => {
    toggle.setAttribute('aria-pressed', String(state === 'playing'));
    toggle.setAttribute('aria-label', state === 'error' ? LABEL.error : state === 'playing' ? LABEL.pause : LABEL.play);
    toggle.dataset.state = state;
    toggle.disabled = state === 'error';
  };

  const play = () => {
    wantsPlay = true;
    let result;
    try {
      result = audio.play(); // must stay synchronous inside the gesture handler
    } catch {
      wantsPlay = false;
      setUi('paused');
      return null;
    }
    if (result && typeof result.then === 'function') {
      result.then(() => setUi('playing')).catch(() => { wantsPlay = false; setUi('paused'); });
    }
    return result;
  };

  const pause = () => {
    wantsPlay = false;
    resumeOnShow = false;
    audio.pause();
    setUi('paused');
  };

  // The cover's open button starts the music (unless the invitation disables autostart).
  root.querySelectorAll('[data-open-invitation]').forEach((button) => {
    button.addEventListener('click', () => { if (audio.dataset.autostart !== 'false') play(); }, { once: true });
  });
  toggle.addEventListener('click', () => (audio.paused ? play() : pause()));

  audio.addEventListener('playing', () => setUi('playing'));
  audio.addEventListener('error', () => { wantsPlay = false; setUi('error'); });

  // Do not play in a hidden tab or after the phone is locked; resume (best effort) when the visitor returns.
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      if (!audio.paused) { resumeOnShow = true; audio.pause(); }
    } else if (resumeOnShow && wantsPlay) {
      resumeOnShow = false;
      const resumed = audio.play();
      if (resumed && typeof resumed.catch === 'function') resumed.catch(() => setUi('paused'));
    }
  });

  setUi('paused');

  const api = { play, pause, audio, toggle };
  instances.set(audio, api);

  return api;
}

if (typeof document !== 'undefined') {
  document.addEventListener('DOMContentLoaded', () => initMusic());
}
