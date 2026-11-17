# Branch Protection Setup - Quick Guide

**Action Required:** Configure GitHub branch protection rules manually

## Quick Setup Instructions

Since GitHub CLI is not available, please configure branch protection manually:

1. **Go to:** https://github.com/chochocookies/momenikakh-update/settings/branches

2. **Click:** "Add rule" button

3. **Configure:**
   - Branch name pattern: `main`
   - ☑ Require a pull request before merging
     - Required approvals: **1**
   - ☑ Require status checks to pass before merging
   - ☑ Restrict who can push to matching branches
     - Leave empty (no users/teams)

4. **Save:** Click "Create" button

## Verification

Test that direct pushes are blocked:

```bash
git checkout main
git commit --allow-empty -m "Test: Protection check"
git push origin main
```

Expected: Push should be rejected with protection error.

## Full Documentation

See `docs/branch-protection-setup.md` for complete configuration details and requirements traceability.

## Requirements

- ✓ **Requirement 1.2:** Minimum 1 pull request review required
- ✓ **Requirement 1.3:** Direct pushes to main rejected

## Note

Status checks will be added in Task 3.5 after the CI workflow is created (Task 3.4).

---

**Status:** ⚠️ MANUAL ACTION REQUIRED
**Task:** 1.2 Configure GitHub branch protection rules
**Milestone:** FND-Fondasi-M0
