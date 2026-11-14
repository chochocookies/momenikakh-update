---
paths:
  - "resources/views/**"
  - "resources/css/**"
  - "resources/js/**"
  - "tailwind.config.js"
  - "vite.config.js"
---

# Frontend and UI

- Mobile first: check 360, 768 and 1,280 px. Guests open invitations on phones, mostly from WhatsApp.
- All user-facing text is Indonesian. Validation messages come from `lang/id`.
- No new inline `<style>` blocks, no new `style=""` attributes, no new `:root` blocks. Use design tokens and Blade components once they exist (ADR-025 is only proposed; reuse the nearest existing pattern until then).
- UI text at least 14 px, touch targets at least 44 x 44 px, contrast at least 4.5 : 1. No emoji as functional icons. No new font family.
- Load Alpine once (through Vite). No Tailwind Play CDN and no unpinned CDN script.
- No business logic or database query in Blade.
- Do not build UI for behavior that does not work yet (section toggles before P1-01).
- Follow `.claude/skills/ui-ux-implementation/SKILL.md`; take baseline screenshots before visual changes.
