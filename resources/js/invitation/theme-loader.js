// Loads the active template's optional theme.js as its own chunk. A template without theme.js loads nothing extra.
// Contract (.claude/docs/04-sistem-template.md): resources/templates/{slug}/theme.js exports  init({ root, reducedMotion }).
const themes = import.meta.glob('/resources/templates/*/theme.js');

export async function loadTheme(root = document) {
  const el = root.querySelector('.inv[data-theme]');
  if (!el) return null;
  const load = themes[`/resources/templates/${el.dataset.theme}/theme.js`];
  if (!load) return null;
  const module = await load();
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches || el.dataset.motion === 'none';
  module.init?.({ root: el, reducedMotion });
  return module;
}
