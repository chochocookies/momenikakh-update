// Countdown to an absolute instant. The server sends an ISO 8601 time WITH the event's UTC offset, so every
// visitor counts to the same moment no matter which time zone their phone is set to.

/** @returns {{days:number,hours:number,minutes:number,seconds:number,done:boolean}} never negative */
export function countdownParts(targetMs, nowMs) {
  const diff = targetMs - nowMs;
  if (!Number.isFinite(diff) || diff <= 0) return { days: 0, hours: 0, minutes: 0, seconds: 0, done: true };
  const total = Math.floor(diff / 1000);
  return {
    days: Math.floor(total / 86400),
    hours: Math.floor((total % 86400) / 3600),
    minutes: Math.floor((total % 3600) / 60),
    seconds: total % 60,
    done: false,
  };
}

export function initCountdown(root = document) {
  root.querySelectorAll('[data-countdown]').forEach((el) => {
    const target = Date.parse(el.dataset.countdown);
    if (Number.isNaN(target)) return;
    const units = Object.fromEntries([...el.querySelectorAll('[data-unit]')].map((u) => [u.dataset.unit, u]));
    const done = el.parentElement?.querySelector('[data-countdown-done]');
    let timer = 0;

    const tick = () => {
      const p = countdownParts(target, Date.now());
      for (const [key, node] of Object.entries(units)) node.textContent = String(p[key] ?? 0);
      if (p.done) {
        clearInterval(timer);
        el.hidden = true;
        if (done) done.hidden = false;
      }
    };

    tick();
    timer = setInterval(tick, 1000);
  });
}
