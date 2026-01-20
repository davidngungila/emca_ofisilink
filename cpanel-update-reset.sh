#!/bin/bash
# Script to reset local branch to match remote (discards local changes)
# Use this when you want to match the remote exactly
# WARNING: This will discard any local commits/changes

set -e

echo "=========================================="
echo "cPanel Git Reset Script"
echo "=========================================="
echo ""
echo "⚠️  WARNING: This will discard local changes and match remote exactly"
echo ""

read -p "Are you sure you want to continue? (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
    echo "Operation cancelled."
    exit 1
fi

echo ""
echo "📥 Fetching latest changes from remote..."
git fetch origin

echo ""
echo "🔄 Resetting local branch to match origin/main..."
git reset --hard origin/main

echo ""
echo "✅ Local branch now matches origin/main exactly"
echo ""



