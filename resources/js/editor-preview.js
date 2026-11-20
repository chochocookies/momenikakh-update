// DSH-04: the editor's live preview. After every save the editor sends "invitation-saved"; the iframe with the owner's draft
// reloads and comes back to the same scroll position, so a change is visible within a moment of leaving the field.
window.addEventListener('invitation-saved', () => {
  const frame = document.getElementById('draft-preview');
  const win = frame?.contentWindow;
  if (!frame || !win) return;
  let y = 0;
  try { y = win.scrollY; } catch { /* a navigated frame can refuse; start from the top */ }
  frame.addEventListener('load', () => { try { frame.contentWindow.scrollTo(0, y); } catch { /* same origin, but never fail the editor over it */ } }, { once: true });
  try { win.location.reload(); } catch { frame.src = frame.src; }
});
