#!/bin/bash

# Branch Protection Verification Script
# This script verifies that branch protection is correctly configured on GitHub
#
# Requirements:
#   - GitHub CLI (gh) installed and authenticated
#   - Internet connection
#
# Usage:
#   ./scripts/verify-branch-protection.sh

set -e

REPO="chochocookies/momenikakh-update"
BRANCH="main"

echo "======================================"
echo "Branch Protection Verification"
echo "======================================"
echo ""
echo "Repository: $REPO"
echo "Branch: $BRANCH"
echo ""

# Check if gh CLI is available
if ! command -v gh &> /dev/null; then
    echo "❌ GitHub CLI (gh) is not installed"
    echo ""
    echo "Please install GitHub CLI:"
    echo "  - Windows: winget install GitHub.cli"
    echo "  - macOS: brew install gh"
    echo "  - Linux: https://github.com/cli/cli/blob/trunk/docs/install_linux.md"
    echo ""
    echo "Or verify manually at:"
    echo "https://github.com/$REPO/settings/branches"
    exit 1
fi

# Check if authenticated
if ! gh auth status &> /dev/null; then
    echo "❌ GitHub CLI is not authenticated"
    echo ""
    echo "Please authenticate with:"
    echo "  gh auth login"
    exit 1
fi

echo "✓ GitHub CLI is installed and authenticated"
echo ""

# Fetch branch protection rules
echo "Fetching branch protection rules..."
echo ""

PROTECTION=$(gh api repos/$REPO/branches/$BRANCH/protection 2>/dev/null || echo "")

if [ -z "$PROTECTION" ]; then
    echo "❌ FAILED: No branch protection configured on $BRANCH"
    echo ""
    echo "Action required: Configure branch protection manually"
    echo "See: BRANCH_PROTECTION.md"
    exit 1
fi

echo "✓ Branch protection is enabled"
echo ""

# Check required pull request reviews
echo "Checking pull request review requirements..."
REQUIRED_REVIEWS=$(echo "$PROTECTION" | grep -o '"required_approving_review_count":[0-9]*' | cut -d':' -f2 || echo "0")

if [ "$REQUIRED_REVIEWS" -ge 1 ]; then
    echo "✓ Requires $REQUIRED_REVIEWS approval(s) before merge (Requirement 1.2)"
else
    echo "❌ FAILED: No pull request reviews required (need at least 1)"
    echo "   Current: $REQUIRED_REVIEWS approvals required"
fi
echo ""

# Check restrictions (no direct pushes)
echo "Checking push restrictions..."
RESTRICTIONS=$(echo "$PROTECTION" | grep -o '"restrictions"' || echo "")

if echo "$PROTECTION" | grep -q '"restrictions":null'; then
    echo "⚠️  WARNING: No push restrictions configured"
    echo "   Anyone with write access can push directly to $BRANCH"
elif echo "$PROTECTION" | grep -q '"users":\[\]'; then
    echo "✓ No users allowed to push directly (Requirement 1.3)"
else
    echo "⚠️  Some users may be allowed to push directly"
fi
echo ""

# Check status checks (will be configured in Task 3.5)
echo "Checking status check requirements..."
if echo "$PROTECTION" | grep -q '"required_status_checks"'; then
    CHECKS=$(echo "$PROTECTION" | grep -o '"contexts":\[[^\]]*\]' || echo "[]")
    if [ "$CHECKS" = "[]" ]; then
        echo "⚠️  No required status checks configured yet"
        echo "   This is expected before Task 3.4 (CI workflow creation)"
    else
        echo "✓ Status checks configured: $CHECKS"
    fi
else
    echo "⚠️  Status checks not required"
fi
echo ""

# Summary
echo "======================================"
echo "Summary"
echo "======================================"
echo ""

if [ "$REQUIRED_REVIEWS" -ge 1 ]; then
    echo "✓ Requirement 1.2: PASSED - Pull request review required"
else
    echo "❌ Requirement 1.2: FAILED - Pull request review not configured"
fi

if echo "$PROTECTION" | grep -q '"users":\[\]'; then
    echo "✓ Requirement 1.3: PASSED - Direct pushes blocked"
else
    echo "⚠️  Requirement 1.3: CHECK MANUALLY - Verify push restrictions"
fi

echo ""
echo "Full protection details:"
echo "$PROTECTION" | jq '.' 2>/dev/null || echo "$PROTECTION"
echo ""

echo "For manual verification, visit:"
echo "https://github.com/$REPO/settings/branch_protection_rules"
