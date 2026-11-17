# Scripts Directory

This directory contains utility scripts for repository maintenance and verification.

## Branch Protection Verification

### Purpose

Verify that GitHub branch protection rules are correctly configured according to Requirements 1.2 and 1.3.

### Scripts

- **verify-branch-protection.sh** - Bash script (Linux/macOS/Git Bash)
- **verify-branch-protection.ps1** - PowerShell script (Windows)

### Prerequisites

- GitHub CLI (`gh`) installed and authenticated
- Internet connection

### Installation

Install GitHub CLI:

**Windows:**
```powershell
winget install GitHub.cli
```

**macOS:**
```bash
brew install gh
```

**Linux:**
```bash
# See: https://github.com/cli/cli/blob/trunk/docs/install_linux.md
```

**Authenticate:**
```bash
gh auth login
```

### Usage

**Windows (PowerShell):**
```powershell
.\scripts\verify-branch-protection.ps1
```

**Linux/macOS/Git Bash:**
```bash
chmod +x scripts/verify-branch-protection.sh
./scripts/verify-branch-protection.sh
```

### What It Checks

The verification script checks:

1. ✓ Branch protection is enabled on `main`
2. ✓ At least 1 pull request review is required (Requirement 1.2)
3. ✓ Direct pushes to `main` are blocked (Requirement 1.3)
4. ⚠️  Status checks configuration (expected after Task 3.4)

### Expected Output

```
======================================
Branch Protection Verification
======================================

Repository: chochocookies/momenikakh-update
Branch: main

✓ GitHub CLI is installed and authenticated

Fetching branch protection rules...

✓ Branch protection is enabled

Checking pull request review requirements...
✓ Requires 1 approval(s) before merge (Requirement 1.2)

Checking push restrictions...
✓ No users allowed to push directly (Requirement 1.3)

Checking status check requirements...
⚠️  No required status checks configured yet
   This is expected before Task 3.4 (CI workflow creation)

======================================
Summary
======================================

✓ Requirement 1.2: PASSED - Pull request review required
✓ Requirement 1.3: PASSED - Direct pushes blocked
```

### Manual Verification

If GitHub CLI is not available, verify manually:

1. Visit: https://github.com/chochocookies/momenikakh-update/settings/branches
2. Locate the `main` branch rule
3. Verify:
   - "Require a pull request before merging" is enabled
   - "Required number of approvals before merging" is set to 1 or more
   - "Restrict who can push to matching branches" is enabled with no users/teams listed

### Troubleshooting

**Error: "gh: command not found"**
- Solution: Install GitHub CLI (see Installation section)

**Error: "GitHub CLI is not authenticated"**
- Solution: Run `gh auth login` and follow the prompts

**Error: "No branch protection configured"**
- Solution: Follow the setup instructions in `BRANCH_PROTECTION.md`

## Related Documentation

- `BRANCH_PROTECTION.md` - Quick setup guide
- `docs/branch-protection-setup.md` - Comprehensive configuration documentation
