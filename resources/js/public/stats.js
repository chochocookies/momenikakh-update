// PUB-06: tells the server a person saw the invitation ("view") and opened its cover ("open"). A beacon, not a cookie.
// Only the invitation's own path counts: the template preview lives under /preview/..., so it never reports.
const SLUG_PATH = /^\/([a-z0-9]+(?:-[a-z0-9]+)*)\/?$/;

export function reportSeen(event, pathname = window.location.pathname) {
  const match = SLUG_PATH.exec(pathname);
  if (!match || match[1] === 'preview') return false;
  const url = `/${match[1]}/seen`;
  const body = new URLSearchParams({ event });
  if (navigator.sendBeacon) return navigator.sendBeacon(url, body);
  fetch(url, { method: 'POST', body, keepalive: true }).catch(() => {});
  return true;
}
