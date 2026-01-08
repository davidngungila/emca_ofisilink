# Quick Fix: Diverging Branches Error on Live Server

## The Problem
When pulling on your live server, you get:
```
fatal: Not possible to fast-forward, aborting.
```

This happens when your server branch and remote branch have different commits.

## Quick Solution (Copy & Paste)

**Run these commands on your cPanel server via SSH or Terminal:**

```bash
# 1. Navigate to your project (adjust path as needed)
cd /home/your-username/public_html

# 2. Fetch latest changes
git fetch origin

# 3. Merge with --no-ff (handles diverging branches)
git merge --no-ff origin/main -m "Merge remote changes"

# 4. If successful, you're done! If conflicts occur, see below.
```

## If You Get Merge Conflicts

```bash
# 1. See which files have conflicts
git status

# 2. Edit the conflicted files and resolve conflicts
# (Look for <<<<<<<, =======, >>>>>>> markers)

# 3. After resolving, stage the files
git add .

# 4. Complete the merge
git commit -m "Resolve merge conflicts"

# 5. Done!
```

## Using the Automated Script

1. Upload `fix-diverging-branches.sh` to your server
2. Make it executable:
   ```bash
   chmod +x fix-diverging-branches.sh
   ```
3. Run it:
   ```bash
   ./fix-diverging-branches.sh
   ```

## Alternative: Reset to Remote (⚠️ Use with Caution)

**WARNING: This will discard all local server changes!**

Only use this if you're sure you don't need any server-specific changes:

```bash
git fetch origin
git reset --hard origin/main
```

## Why This Happens

- Server has commits that aren't in the remote
- Remote has commits that aren't on the server
- Both branches have diverged from a common ancestor

The `--no-ff` merge creates a merge commit that combines both histories safely.

