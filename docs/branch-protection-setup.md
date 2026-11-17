# GitHub Branch Protection Configuration

**Last Updated:** 2025-01-24

## Overview

This document describes the branch protection rules that must be configured on the GitHub repository to meet Requirements 1.2 and 1.3 of the FND-Fondasi-M0 milestone.

## Repository Information

- **Repository:** https://github.com/chochocookies/momenikakh-update
- **Protected Branch:** `main`

## Required Branch Protection Rules

### 1. Pull Request Reviews

- **Require pull request reviews before merging:** ✓ Enabled
- **Required number of approvals before merging:** 1
- **Dismiss stale pull request approvals when new commits are pushed:** ✓ Enabled (recommended)
- **Require review from Code Owners:** ✗ Not required (optional)

### 2. Status Checks

- **Require status checks to pass before merging:** ✓ Enabled
- **Require branches to be up to date before merging:** ✓ Enabled (recommended)
- **Required status checks:**
  - `Linter (Pint)` - from GitHub Actions CI workflow
  - `Static Analysis (Larastan)` - from GitHub Actions CI workflow
  - `Tests (Pest)` - from GitHub Actions CI workflow
  - `Build (npm)` - from GitHub Actions CI workflow

*Note: Status checks will become available after the first pull request runs the CI workflow (Task 3.4).*

### 3. Direct Push Protection

- **Restrict who can push to matching branches:** ✓ Enabled
- **Allowed actors:** No users or teams (effectively prevents direct pushes)

### 4. Additional Recommended Settings

- **Require conversation resolution before merging:** ✓ Enabled
- **Allow force pushes:** ✗ Disabled
- **Allow deletions:** ✗ Disabled

## Configuration Methods

### Method 1: GitHub Web Interface (Recommended)

1. Navigate to repository: https://github.com/chochocookies/momenikakh-update
2. Click **Settings** tab
3. Click **Branches** in the left sidebar
4. Under "Branch protection rules", click **Add rule** (or edit existing rule for `main`)
5. In "Branch name pattern", enter: `main`
6. Configure the following settings:

   **Protect matching branches:**
   - [x] Require a pull request before merging
     - Required number of approvals before merging: **1**
     - [x] Dismiss stale pull request approvals when new commits are pushed (optional but recommended)
   
   - [x] Require status checks to pass before merging
     - [x] Require branches to be up to date before merging (optional but recommended)
     - Search and select status checks: *Will be available after first CI run*
   
   - [x] Restrict who can push to matching branches
     - Do not add any users or teams (this prevents all direct pushes)
   
   - [x] Require conversation resolution before merging (optional but recommended)
   
   - [ ] Allow force pushes (ensure this is UNCHECKED)
   - [ ] Allow deletions (ensure this is UNCHECKED)

7. Click **Create** or **Save changes**

### Method 2: GitHub CLI

If GitHub CLI (`gh`) is installed and authenticated:

```bash
# Enable branch protection with required pull request reviews
gh api repos/chochocookies/momenikakh-update/branches/main/protection \
  --method PUT \
  --field required_status_checks='{"strict":true,"contexts":[]}' \
  --field enforce_admins=true \
  --field required_pull_request_reviews='{"required_approving_review_count":1,"dismiss_stale_reviews":true}' \
  --field restrictions=null

# Note: Status checks will need to be added after CI workflow is created
```

### Method 3: GitHub API

Using curl or similar HTTP client:

```bash
curl -X PUT \
  -H "Accept: application/vnd.github+json" \
  -H "Authorization: Bearer YOUR_GITHUB_TOKEN" \
  https://api.github.com/repos/chochocookies/momenikakh-update/branches/main/protection \
  -d '{
    "required_status_checks": {
      "strict": true,
      "contexts": []
    },
    "enforce_admins": true,
    "required_pull_request_reviews": {
      "required_approving_review_count": 1,
      "dismiss_stale_reviews": true
    },
    "restrictions": null
  }'
```

## Verification Steps

After configuration, verify the settings:

1. **Verify Pull Request Requirement:**
   - Try to push directly to `main` branch
   - Should be rejected with error message indicating branch is protected

2. **Verify Review Requirement:**
   - Create a test branch and pull request
   - Attempt to merge without review
   - Should be blocked until 1 approval is received

3. **Verify Status Check Requirement (after Task 3.4):**
   - Create a pull request that fails CI checks
   - Attempt to merge
   - Should be blocked until all checks pass

## Testing Direct Push Protection

To test that direct pushes to `main` are blocked:

```bash
# On a test branch or after making a change
git checkout main
git commit --allow-empty -m "Test: Direct push protection"
git push origin main

# Expected result: Error message indicating protected branch
# Example: "remote: error: GH006: Protected branch update failed for refs/heads/main."
```

## Status Check Configuration (After Task 3.4)

Once the GitHub Actions CI workflow is created (Task 3.4), return to the branch protection settings and add the following required status checks:

1. Go to repository **Settings** → **Branches** → Edit `main` rule
2. Under "Require status checks to pass before merging", search and select:
   - The Linter job name from the workflow
   - The Static Analysis job name from the workflow
   - The Tests job name from the workflow
   - The Build job name from the workflow

*Note: Exact check names will match the job names defined in `.github/workflows/ci.yml`*

## Troubleshooting

### Issue: Cannot find status checks to require

**Cause:** No pull requests have run the CI workflow yet.

**Solution:** Create and merge one pull request first. After the workflow runs, the status checks will appear in the branch protection settings.

### Issue: Admins can still push directly to main

**Cause:** "Do not allow bypassing the above settings" is not enabled.

**Solution:** In branch protection settings, check "Do not allow bypassing the above settings" (also called "Enforce admins").

### Issue: Status checks not blocking merge

**Cause:** Required status checks are not configured correctly.

**Solution:** Ensure the exact job names from the GitHub Actions workflow are added to the required status checks list.

## Requirements Traceability

This configuration satisfies:

- **Requirement 1.2:** Branch protection enabled on `main` requiring at least 1 pull request review
- **Requirement 1.3:** Direct pushes to `main` branch rejected

## Related Tasks

- **Task 1.1:** Create repository structure with Git configuration files (prerequisite)
- **Task 3.4:** Create GitHub Actions CI workflow (provides status checks)
- **Task 3.5:** Verify CI pipeline integration with branch protection (validation)

## Notes

- Branch protection rules only affect the remote repository on GitHub, not local operations
- Developers can still commit locally to `main`, but cannot push directly to remote `main`
- Repository administrators may have ability to bypass rules unless "Enforce admins" is enabled
- Consider enabling "Require linear history" to prevent merge commits (optional)
