# Fix Git Merge Conflict and Diverging Branches on cPanel Server

This guide helps resolve common git deployment issues on cPanel servers.

## Error 1: Diverging Branches (Fast-forward not possible)

This error occurs when the server branch and remote branch have diverged (different commits).

## Solution Options:

### Option 1: Via cPanel File Manager
1. Log into cPanel
2. Go to File Manager
3. Navigate to your project directory
4. Open Terminal (if available) or use SSH

### Option 2: Via SSH (Recommended)
```bash
# Connect to your server via SSH
ssh your-username@your-server.com

# Navigate to your project
cd /home/username/public_html/your-project

# Check what changes exist
git status

# Option A: Commit the server changes first
git add app/Http/Controllers/EmployeeController.php
git commit -m "Server-side EmployeeController changes"
git pull origin main

# Option B: Stash the changes and pull
git stash
git pull origin main
git stash pop  # This may cause conflicts that need manual resolution

# Option C: Discard server changes (if not needed)
git checkout -- app/Http/Controllers/EmployeeController.php
git pull origin main
```

### Option 3: Force Pull (Use with caution)
```bash
# This will overwrite server changes
git fetch origin
git reset --hard origin/main
```

## Error 2: Diverging Branches (Fast-forward not possible)

When branches have diverged, you need to merge or rebase:

### Option A: Merge (Recommended for server deployments)
```bash
# Fetch latest changes
git fetch origin

# Merge remote changes (creates a merge commit)
git merge --no-ff origin/main -m "Merge remote changes"

# If conflicts occur, resolve them:
git add .
git commit -m "Resolve merge conflicts"
git push origin main
```

### Option B: Rebase (Cleaner history, but rewrites commits)
```bash
# Fetch latest changes
git fetch origin

# Rebase local commits on top of remote
git rebase origin/main

# If conflicts occur, resolve them:
git add .
git rebase --continue

# Force push (only if you're sure no one else is working on this branch)
git push origin main --force
```

### Option C: Reset to Remote (Discard server changes - Use with caution)
```bash
# WARNING: This will discard all local server changes!
git fetch origin
git reset --hard origin/main
```

## Recommended Approach for Diverging Branches:

**For Production Servers (Safest):**
```bash
# 1. Fetch latest changes
git fetch origin

# 2. Stash any uncommitted changes
git stash

# 3. Merge with no-ff to preserve history
git merge --no-ff origin/main -m "Merge remote changes from deployment"

# 4. If conflicts occur, resolve them manually
# 5. Push the merge commit
git push origin main
```

**Quick Fix Script:**
Use the provided `deploy-fix.sh` script:
```bash
chmod +x deploy-fix.sh
./deploy-fix.sh
```

## General Recommended Approach:

Since your local repository is clean and up-to-date, the safest approach is:

1. **Backup the server's changes** (in case it has important modifications)
2. **Use merge strategy** to combine changes:
   ```bash
   git fetch origin
   git merge --no-ff origin/main -m "Merge remote changes"
   git push origin main
   ```

This will ensure the server has all the latest code while preserving any server-specific changes.

