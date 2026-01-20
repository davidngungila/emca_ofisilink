#!/bin/bash
# Safe update script for cPanel that handles uncommitted changes
# This script will stash local changes, update, and optionally restore them

set -e

echo "=========================================="
echo "cPanel Safe Git Update Script"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if there are uncommitted changes
if ! git diff-index --quiet HEAD -- && [ -n "$(git status --porcelain)" ]; then
    echo -e "${YELLOW}⚠️  Uncommitted changes detected${NC}"
    echo ""
    echo "Changes found:"
    git status --short
    echo ""
    echo "Options:"
    echo "1. Stash changes, update, then restore (recommended)"
    echo "2. Discard all local changes and update"
    echo "3. Cancel and commit changes manually"
    echo ""
    read -p "Choose option (1/2/3): " choice
    
    case $choice in
        1)
            echo ""
            echo -e "${YELLOW}📦 Stashing local changes...${NC}"
            git stash push -m "Auto-stash before update $(date +%Y-%m-%d_%H:%M:%S)"
            STASHED=true
            echo -e "${GREEN}✓ Changes stashed${NC}"
            ;;
        2)
            echo ""
            echo -e "${YELLOW}🗑️  Discarding local changes...${NC}"
            read -p "Are you sure? This will permanently delete uncommitted changes (yes/no): " confirm
            if [ "$confirm" != "yes" ]; then
                echo -e "${RED}Operation cancelled${NC}"
                exit 1
            fi
            git reset --hard HEAD
            git clean -fd
            STASHED=false
            echo -e "${GREEN}✓ Local changes discarded${NC}"
            ;;
        3)
            echo -e "${YELLOW}Operation cancelled. Please commit your changes first.${NC}"
            exit 0
            ;;
        *)
            echo -e "${RED}Invalid option. Operation cancelled.${NC}"
            exit 1
            ;;
    esac
else
    STASHED=false
    echo -e "${GREEN}✓ No uncommitted changes detected${NC}"
fi

echo ""
echo -e "${YELLOW}📥 Fetching latest changes from remote...${NC}"
git fetch origin

# Check current branch
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
echo -e "${GREEN}✓ Current branch: $CURRENT_BRANCH${NC}"

# Check if branches have diverged
LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/main)
BASE=$(git merge-base HEAD origin/main 2>/dev/null || echo "")

if [ "$LOCAL" = "$REMOTE" ]; then
    echo -e "\n${GREEN}✓ Already up to date with origin/main${NC}"
    if [ "$STASHED" = true ]; then
        echo -e "${YELLOW}📦 Restoring stashed changes...${NC}"
        git stash pop || true
        echo -e "${GREEN}✓ Changes restored${NC}"
    fi
    exit 0
fi

# Try to update
echo ""
echo -e "${YELLOW}🔄 Updating code...${NC}"

# Strategy 1: Try fast-forward first
if [ "$LOCAL" = "$BASE" ]; then
    echo -e "${GREEN}✓ Local branch is behind, fast-forwarding...${NC}"
    git merge --ff-only origin/main
    echo -e "${GREEN}✓ Update completed successfully!${NC}"
else
    # Strategy 2: Try rebase
    echo -e "${YELLOW}⚠️  Branches have diverged. Attempting rebase...${NC}"
    if git pull --rebase origin main 2>/dev/null; then
        echo -e "${GREEN}✓ Successfully rebased changes${NC}"
        echo -e "${GREEN}✓ Update completed successfully!${NC}"
    else
        # Strategy 3: Try merge
        echo -e "${YELLOW}⚠️  Rebase failed, trying merge...${NC}"
        if git pull --no-ff origin main 2>/dev/null; then
            echo -e "${GREEN}✓ Successfully merged changes${NC}"
            echo -e "${GREEN}✓ Update completed successfully!${NC}"
        else
            echo -e "${RED}❌ Automatic update failed. Manual intervention required.${NC}"
            echo ""
            echo "You may need to resolve conflicts manually."
            if [ "$STASHED" = true ]; then
                echo -e "${YELLOW}Your stashed changes are still available.${NC}"
                echo "To restore: git stash pop"
            fi
            exit 1
        fi
    fi
fi

# Restore stashed changes if they were stashed
if [ "$STASHED" = true ]; then
    echo ""
    echo -e "${YELLOW}📦 Restoring stashed changes...${NC}"
    if git stash pop; then
        echo -e "${GREEN}✓ Changes restored successfully${NC}"
    else
        echo -e "${YELLOW}⚠️  Some conflicts occurred while restoring changes${NC}"
        echo "Please resolve conflicts manually:"
        echo "  git status"
        echo "  # Resolve conflicts, then:"
        echo "  git add ."
        echo "  git stash drop"
    fi
fi

echo ""
echo -e "${GREEN}✅ Update process completed!${NC}"
echo ""



