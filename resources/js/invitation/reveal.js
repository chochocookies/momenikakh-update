// Reveal on scroll. CSS only hides [data-reveal] when motion is allowed (see invitation-core.css), so with reduced
// motion this does nothing visible. Without IntersectionObserver everything is shown at once.
export function initReveal(root = document) {
  const targets = [...root.querySelectorAll('[data-reveal]')];
  if (targets.length === 0) return;
  if (!('IntersectionObserver' in window)) {
    targets.forEach((el) => el.classList.add('is-visible'));
    return;
  }
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('is-visible');
      observer.unobserve(entry.target);
    });
  }, { rootMargin: '0px 0px -10% 0px' });
  targets.forEach((el) => observer.observe(el));
}
