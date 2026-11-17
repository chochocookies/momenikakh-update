# Branch Protection & CI Pipeline

This document describes the CI pipeline checks and the branch protection rules that govern merging into `main`.

---

## CI Workflow

Defined in `.github/workflows/ci.yml`, the pipeline runs automatically on every pull request (`opened`, `synchronize`, `reopened`).

### Checks

| Check name | Tool | Timeout | Passes when |
|---|---|---|---|
| **Linter (Laravel Pint)** | `./vendor/bin/pint --test` | 5 min | Exit code 0 — no style violations |
| **Static Analyzer (Larastan)** | `./vendor/bin/phpstan analyse` | 10 min | Exit code 0 — no type errors above configured level |
| **Tests (Pest)** | `./vendor/bin/pest` | 15 min | Exit code 0 — all tests pass |
| **Build (Frontend Assets)** | `npm run build` | 10 min | Exit code 0 — Vite bundle compiled without errors |

Each check runs as an independent GitHub Actions job on `ubuntu-latest`. The Tests job also spins up `mysql:8.4` and `redis:7` service containers.

---

## Branch Protection Rules for `main`

Configure these settings at **GitHub → Settings → Branches → Add rule → `main`**:

| Setting | Value |
|---|---|
| Require a pull request before merging | ✅ Enabled |
| Required number of approvals | **1** |
| Dismiss stale reviews when new commits are pushed | Recommended |
| Require status checks to pass before merging | ✅ Enabled |
| Required status checks | `Linter (Laravel Pint)` |
| | `Static Analyzer (Larastan)` |
| | `Tests (Pest)` |
| | `Build (Frontend Assets)` |
| Require branches to be up to date before merging | Recommended |
| Do not allow bypassing the above settings | ✅ Enabled |
| Allow force pushes | ❌ Disabled |
| Allow deletions | ❌ Disabled |

> **Note:** The status check names in the "Required status checks" field must match the `name:` field of each job in `ci.yml` exactly.

---

## How Failed Checks Block Merging

1. A developer opens or updates a pull request targeting `main`.
2. GitHub triggers the CI pipeline. Each job reports its result back as a commit status check.
3. If **any** job exits with a non-zero status code, its check is marked **failed** (red ✗) in the pull request's "Checks" section.
4. Because branch protection requires all four status checks to pass, the **Merge** button is disabled and displays:  
   *"Required status checks have not passed."*
5. The developer must fix the failing code, push a new commit to the branch, and wait for all checks to turn green before the pull request can be merged.
6. Even when all checks pass, at least one reviewer must approve the pull request before merging is permitted.

---

## Expected Pull Request Status Display

After a CI run completes, the pull request timeline shows a summary entry and a "Show all checks" link that expands to:

```
✅  Linter (Laravel Pint)         — passing
✅  Static Analyzer (Larastan)    — passing
✅  Tests (Pest)                  — passing
✅  Build (Frontend Assets)       — passing
```

A failure example:

```
❌  Linter (Laravel Pint)         — failing  (style violations found)
✅  Static Analyzer (Larastan)    — passing
✅  Tests (Pest)                  — passing
✅  Build (Frontend Assets)       — passing

Merging is blocked until all required checks pass.
```

---

## Maintaining This Setup

- **Adding a new required check:** Add the job to `ci.yml`, then add its `name:` value to the required status checks list in GitHub branch protection settings.
- **Changing analysis level:** Edit `phpstan.neon`. The inline comment in that file documents the current level and rationale (Requirement 3.10).
- **Workflow file location:** `.github/workflows/ci.yml` — any `.yml`/`.yaml` file under `.github/workflows/` is picked up automatically by GitHub Actions.
