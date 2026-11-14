---
paths:
  - "tests/**"
---

# Tests

- Every fix ships with a test that fails before the fix and passes after. Never skip, delete or loosen a test to get a green run. Never assert the buggy behavior.
- Prefer feature tests through the HTTP stack. Cover the pattern that matches the change: round trip of every field, route order, template rendering, ownership/IDOR, throttling, billing callback, soft delete (catalog in `.claude/skills/testing-validation/SKILL.md`).
- The stock Breeze login tests post no `captcha` field and probably fail; handle the captcha in tests explicitly.
- Use `Storage::fake('public')`, `Mail::fake()`, factories. No network calls to CDNs, Unsplash or Duitku. Sandbox payment tests only on staging.
- Use anonymized data; never real names, emails, phone numbers or account numbers.
- Prefer MySQL with strict mode for tests that touch constraints.
