---
paths:
  - "app/Http/**"
  - "app/Policies/**"
  - "app/Services/**"
  - "app/Providers/**"
  - "routes/**"
  - "bootstrap/app.php"
  - "config/**"
---

# Security-sensitive code

Apply `AI_CONTEXT/references/SECURITY_GUIDELINES.md` rules SG-01 to SG-15. The essentials:

- Validate every request field (Form Request preferred). Whitelist nested input such as `gift_settings`. Never pass `$request->all()` to `create` or `update`.
- Authorize every state-changing action with a policy or by querying through the owner's relation. Never trust IDs or file paths from the request. Never delete a path taken from user input (P0-05).
- Payments: amounts and plans come from the server only. Keep merchant-code, signature (`hash_equals`) and amount checks and idempotency. The CSRF exemption stays limited to `billing/callback`. Never log signatures or credentials.
- Admin access has one check (`isAdmin()`); privilege columns (`role`, `is_admin`) are never mass assignable.
- Public endpoints need `throttle`; guest input only for `published` invitations.
- No `$e->getMessage()` in responses; log details with masked personal data.
- Route order matters: `GET /{slug}` must stay after `require auth.php` (P1-05). Do not add development routes or middleware.
- Never use `trustProxies('*')`. Do not touch `.env` or production settings.
