#!/bin/bash
# Quick fix for diverging branches on cPanel server
# Run this script on your live server via SSH or cPanel Terminal

echo "=== Fixing Diverging Branches ==="
echo ""

# Get current directory
CURRENT_DIR=$(pwd)
echo "Current directory: $CURRENT_DIR"
echo ""

# Check if we're in a git repository
if [ ! -d .git ]; then
    echo "ERROR: Not a git repository. Please navigate to your project directory first."
    exit 1
fi

# Show current status
echo "1. Current git status:"
git status --short
echo ""

# Fetch latest changes
echo "2. Fetching latest changes from remote..."
git fetch origin
echo ""

# Check if there are uncommitted changes
if [ -n "$(git status -s)" ]; then
    echo "3. Stashing uncommitted changes..."
    git stash save "Auto-stash before merge - $(date '+%Y-%m-%d %H:%M:%S')"
    echo "   Changes stashed successfully."
    echo ""
fi

# Perform merge with --no-ff
echo "4. Merging remote changes (this handles diverging branches)..."
git merge --no-ff origin/main -m "Merge remote changes - $(date '+%Y-%m-%d %H:%M:%S')"

# Check merge result
if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Merge completed successfully!"
    echo ""
    echo "5. Current status:"
    git status --short
    echo ""
    
    # Try to apply stashed changes if any
    if [ -n "$(git stash list)" ]; then
        echo "6. Attempting to apply stashed changes..."
        git stash pop
        if [ $? -eq 0 ]; then
            echo "   ✅ Stashed changes applied successfully."
        else
            echo "   ⚠️  Warning: Could not apply stashed changes automatically."
            echo "   Review with: git stash list"
            echo "   Apply manually with: git stash pop"
        fi
        echo ""
    fi
    
    echo "=== Fix completed successfully! ==="
    echo ""
    echo "If you need to push changes, run:"
    echo "  git push origin main"
    
else
    echo ""
    echo "❌ Merge conflict detected!"
    echo ""
    echo "Please resolve conflicts manually:"
    echo "  1. Check conflicted files: git status"
    echo "  2. Edit the files to resolve conflicts"
    echo "  3. Stage resolved files: git add ."
    echo "  4. Complete merge: git commit"
    echo "  5. Push: git push origin main"
    echo ""
    exit 1
fi

