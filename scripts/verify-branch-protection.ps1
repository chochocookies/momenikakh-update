# Branch Protection Verification Script (PowerShell)
# This script verifies that branch protection is correctly configured on GitHub
#
# Requirements:
#   - GitHub CLI (gh) installed and authenticated
#   - Internet connection
#
# Usage:
#   .\scripts\verify-branch-protection.ps1

$ErrorActionPreference = "Stop"

$REPO = "chochocookies/momenikakh-update"
$BRANCH = "main"

Write-Host "======================================"
Write-Host "Branch Protection Verification"
Write-Host "======================================"
Write-Host ""
Write-Host "Repository: $REPO"
Write-Host "Branch: $BRANCH"
Write-Host ""

# Check if gh CLI is available
$ghExists = Get-Command gh -ErrorAction SilentlyContinue
if (-not $ghExists) {
    Write-Host "❌ GitHub CLI (gh) is not installed" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install GitHub CLI:"
    Write-Host "  winget install GitHub.cli"
    Write-Host ""
    Write-Host "Or verify manually at:"
    Write-Host "https://github.com/$REPO/settings/branches"
    exit 1
}

# Check if authenticated
$authStatus = gh auth status 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ GitHub CLI is not authenticated" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please authenticate with:"
    Write-Host "  gh auth login"
    exit 1
}

Write-Host "✓ GitHub CLI is installed and authenticated" -ForegroundColor Green
Write-Host ""

# Fetch branch protection rules
Write-Host "Fetching branch protection rules..."
Write-Host ""

try {
    $protection = gh api "repos/$REPO/branches/$BRANCH/protection" 2>$null | ConvertFrom-Json
    Write-Host "✓ Branch protection is enabled" -ForegroundColor Green
    Write-Host ""
} catch {
    Write-Host "❌ FAILED: No branch protection configured on $BRANCH" -ForegroundColor Red
    Write-Host ""
    Write-Host "Action required: Configure branch protection manually"
    Write-Host "See: BRANCH_PROTECTION.md"
    exit 1
}

# Check required pull request reviews
Write-Host "Checking pull request review requirements..."
$requiredReviews = $protection.required_pull_request_reviews.required_approving_review_count

if ($requiredReviews -ge 1) {
    Write-Host "✓ Requires $requiredReviews approval(s) before merge (Requirement 1.2)" -ForegroundColor Green
    $req12Passed = $true
} else {
    Write-Host "❌ FAILED: No pull request reviews required (need at least 1)" -ForegroundColor Red
    Write-Host "   Current: $requiredReviews approvals required"
    $req12Passed = $false
}
Write-Host ""

# Check restrictions (no direct pushes)
Write-Host "Checking push restrictions..."
if ($null -eq $protection.restrictions) {
    Write-Host "⚠️  WARNING: No push restrictions configured" -ForegroundColor Yellow
    Write-Host "   Anyone with write access can push directly to $BRANCH"
    $req13Passed = $false
} elseif ($protection.restrictions.users.Count -eq 0) {
    Write-Host "✓ No users allowed to push directly (Requirement 1.3)" -ForegroundColor Green
    $req13Passed = $true
} else {
    Write-Host "⚠️  Some users may be allowed to push directly" -ForegroundColor Yellow
    $req13Passed = $false
}
Write-Host ""

# Check status checks (will be configured in Task 3.5)
Write-Host "Checking status check requirements..."
if ($null -ne $protection.required_status_checks) {
    $checks = $protection.required_status_checks.contexts
    if ($checks.Count -eq 0) {
        Write-Host "⚠️  No required status checks configured yet" -ForegroundColor Yellow
        Write-Host "   This is expected before Task 3.4 (CI workflow creation)"
    } else {
        Write-Host "✓ Status checks configured: $($checks -join ', ')" -ForegroundColor Green
    }
} else {
    Write-Host "⚠️  Status checks not required" -ForegroundColor Yellow
}
Write-Host ""

# Summary
Write-Host "======================================"
Write-Host "Summary"
Write-Host "======================================"
Write-Host ""

if ($req12Passed) {
    Write-Host "✓ Requirement 1.2: PASSED - Pull request review required" -ForegroundColor Green
} else {
    Write-Host "❌ Requirement 1.2: FAILED - Pull request review not configured" -ForegroundColor Red
}

if ($req13Passed) {
    Write-Host "✓ Requirement 1.3: PASSED - Direct pushes blocked" -ForegroundColor Green
} else {
    Write-Host "⚠️  Requirement 1.3: CHECK MANUALLY - Verify push restrictions" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Full protection details:"
$protection | ConvertTo-Json -Depth 10
Write-Host ""

Write-Host "For manual verification, visit:"
Write-Host "https://github.com/$REPO/settings/branch_protection_rules"
