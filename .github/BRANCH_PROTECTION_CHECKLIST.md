# Branch Protection Configuration Checklist

Use this checklist to verify branch protection is correctly configured.

## Pre-Configuration

- [ ] Read `BRANCH_PROTECTION.md` (quick guide)
- [ ] Read `docs/branch-protection-setup.md` (detailed guide)
- [ ] Have write access to repository

## Configuration Steps

### 1. Navigate to Settings

- [ ] Go to https://github.com/chochocookies/momenikakh-update/settings/branches
- [ ] Click "Add rule" (or edit existing rule if present)

### 2. Basic Settings

- [ ] Branch name pattern: `main`

### 3. Pull Request Reviews (Requirement 1.2)

- [ ] ✓ Require a pull request before merging
- [ ] Required number of approvals: **1**
- [ ] ✓ Dismiss stale pull request approvals when new commits are pushed (optional)

### 4. Push Restrictions (Requirement 1.3)

- [ ] ✓ Restrict who can push to matching branches
- [ ] Verify: No users or teams in "Allowed to push" list
- [ ] If any users/teams listed: Remove them

### 5. Additional Settings

- [ ] ✓ Require conversation resolution before merging (recommended)
- [ ] ✗ Allow force pushes (ensure UNCHECKED)
- [ ] ✗ Allow deletions (ensure UNCHECKED)
- [ ] ✓ Do not allow bypassing the above settings (recommended)

### 6. Status Checks (Task 3.5 - After CI Workflow)

> Skip this section for now. Return after Task 3.4 is complete.

- [ ] ✓ Require status checks to pass before merging
- [ ] ✓ Require branches to be up to date before merging
- [ ] Add required checks:
  - [ ] Linter (Pint)
  - [ ] Static Analysis (Larastan)
  - [ ] Tests (Pest)
  - [ ] Build (npm)

### 7. Save Configuration

- [ ] Click "Create" or "Save changes"
- [ ] Verify green success message appears

## Post-Configuration Verification

### Method 1: Visual Verification

- [ ] Go to https://github.com/chochocookies/momenikakh-update/settings/branches
- [ ] Confirm rule exists for `main` branch
- [ ] Confirm settings match configuration above

### Method 2: Script Verification (Requires GitHub CLI)

**If `gh` is installed:**

```powershell
# Windows
.\scripts\verify-branch-protection.ps1

# Linux/macOS/Git Bash
./scripts/verify-branch-protection.sh
```

**Expected results:**
- [ ] ✓ Requirement 1.2: PASSED - Pull request review required
- [ ] ✓ Requirement 1.3: PASSED - Direct pushes blocked

**If `gh` is not installed:**
- [ ] Skip to Method 3

### Method 3: Test Direct Push (Most Reliable)

```bash
git checkout main
git commit --allow-empty -m "Test: Branch protection check"
git push origin main
```

**Expected result:**
- [ ] Push is rejected
- [ ] Error message mentions "protected branch"
- [ ] Error code similar to: `GH006: Protected branch update failed`

**If push succeeds:**
- ❌ Configuration failed - review steps above
- Return to Configuration Steps section

### Method 4: Test Pull Request (Complete Verification)

1. Create test branch:
   ```bash
   git checkout -b test/branch-protection
   git commit --allow-empty -m "Test PR"
   git push origin test/branch-protection
   ```

2. Create pull request:
   - [ ] Go to repository on GitHub
   - [ ] Click "Pull requests" → "New pull request"
   - [ ] Select `test/branch-protection` → `main`
   - [ ] Click "Create pull request"

3. Verify restrictions:
   - [ ] "Merge pull request" button is disabled
   - [ ] Message shows "Review required"
   - [ ] Cannot merge without approval

4. Test approval:
   - [ ] Get approval from another team member (or self if admin)
   - [ ] "Merge pull request" button becomes enabled
   - [ ] Can merge successfully

5. Cleanup:
   - [ ] Merge or close test PR
   - [ ] Delete test branch

## Troubleshooting

### Issue: Cannot save rule
- **Check:** Do you have admin access to repository?
- **Solution:** Contact repository owner for permissions

### Issue: "Merge" button still enabled without review
- **Check:** Is "Do not allow bypassing" enabled?
- **Check:** Are you an admin with bypass permissions?
- **Solution:** Enable enforcement or test with non-admin account

### Issue: Cannot find status checks
- **Cause:** CI workflow hasn't run yet
- **Solution:** This is expected. Return after Task 3.4

### Issue: Direct push still works
- **Check:** Did you push to `origin main` not local `main`?
- **Check:** Is rule enabled on correct repository?
- **Solution:** Verify GitHub settings show rule

## Completion Criteria

Task 1.2 is complete when:

- [x] All configuration steps completed
- [x] At least one verification method passes
- [x] Documentation reviewed
- [x] Team informed of new workflow

## Next Steps

After Task 1.2 completion:

1. **Continue to Task 2.1:** Install Laravel 13 with PHP 8.3+ configuration
2. **Return after Task 3.4:** Configure required status checks (section 6 above)
3. **Complete Task 3.5:** Verify CI pipeline integration

## Notes

- Configuration persists until manually changed
- New team members should be informed of branch protection
- Status checks are added later (Task 3.5)
- This is a one-time setup for the repository

## References

- Quick Guide: `BRANCH_PROTECTION.md`
- Detailed Guide: `docs/branch-protection-setup.md`
- Task Summary: `docs/TASK-1.2-SUMMARY.md`
- Script Docs: `scripts/README.md`

---

**Last Updated:** 2025-01-24  
**Task:** 1.2 Configure GitHub branch protection rules  
**Requirements:** 1.2, 1.3
