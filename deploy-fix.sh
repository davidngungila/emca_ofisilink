#!/bin/bash
# Fix git merge conflict and diverging branches on server
# Run this script on your cPanel server

# Get the project directory (adjust this path to match your server)
PROJECT_DIR="/home/$(whoami)/public_html"
cd "$PROJECT_DIR" || exit 1

echo "=== Git Deployment Fix Script ==="
echo "Current directory: $(pwd)"
echo ""

# Check git status
echo "1. Checking git status..."
git status

# Fetch latest changes
echo ""
echo "2. Fetching latest changes from remote..."
git fetch origin

# Check if there are local uncommitted changes
if [ -n "$(git status -s)" ]; then
    echo ""
    echo "3. Stashing local uncommitted changes..."
    git stash save "Server changes before merge - $(date)"
fi

# Check if branches have diverged
LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/main)
BASE=$(git merge-base HEAD origin/main)

if [ "$LOCAL" = "$REMOTE" ]; then
    echo ""
    echo "Branches are already in sync. No action needed."
    exit 0
fi

if [ "$LOCAL" = "$BASE" ]; then
    echo ""
    echo "3. Fast-forward merge possible..."
    git pull origin main
elif [ "$REMOTE" = "$BASE" ]; then
    echo ""
    echo "3. Local branch is ahead. Pushing local changes..."
    git push origin main
else
    echo ""
    echo "3. Branches have diverged. Performing merge..."
    echo "   Local commits: $(git log --oneline $BASE..HEAD | wc -l)"
    echo "   Remote commits: $(git log --oneline $BASE..origin/main | wc -l)"
    
    # Merge with no-ff to preserve history
    git merge --no-ff origin/main -m "Merge remote changes from origin/main"
    
    if [ $? -ne 0 ]; then
        echo ""
        echo "ERROR: Merge conflict detected!"
        echo "Please resolve conflicts manually and then run:"
        echo "  git add ."
        echo "  git commit -m 'Resolve merge conflicts'"
        echo "  git push origin main"
        exit 1
    fi
fi

# If there were stashed changes, try to apply them
if [ -n "$(git stash list)" ]; then
    echo ""
    echo "4. Attempting to apply stashed changes..."
    git stash pop || {
        echo "Warning: Could not apply stashed changes automatically."
        echo "Review with: git stash list"
        echo "Apply manually with: git stash pop"
    }
fi

echo ""
echo "=== Deployment fix completed! ==="
echo "Current status:"
git status

