#!/bin/bash

# GitHub Webhook Auto-Deployment Script
# This script is called automatically when code is pushed to the repository

set -e  # Exit on error

echo "🚀 Starting automatic deployment from webhook..."
echo "Timestamp: $(date)"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get the project directory (where this script is located)
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: artisan file not found. Are you in the Laravel root directory?${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Laravel directory detected: $PROJECT_DIR${NC}"

# Step 1: Fetch latest changes
echo -e "\n${YELLOW}📥 Fetching latest changes from GitHub...${NC}"
git fetch origin

# Step 2: Pull with merge (handles diverging branches)
echo -e "\n${YELLOW}🔄 Pulling latest code...${NC}"
if ! git pull --no-ff origin main 2>/dev/null; then
    echo -e "${YELLOW}⚠️  Merge failed, trying rebase...${NC}"
    git pull --rebase origin main || {
        echo -e "${RED}❌ Failed to pull changes. Please resolve conflicts manually.${NC}"
        exit 1
    }
fi

echo -e "${GREEN}✓ Code updated successfully${NC}"

# Step 3: Install/Update dependencies
echo -e "\n${YELLOW}📦 Updating Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

echo -e "${GREEN}✓ Dependencies updated${NC}"

# Step 4: Clear caches
echo -e "\n${YELLOW}🧹 Clearing caches...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo -e "${GREEN}✓ Caches cleared${NC}"

# Step 5: Rebuild caches
echo -e "\n${YELLOW}⚡ Rebuilding caches...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "${GREEN}✓ Caches rebuilt${NC}"

# Step 6: Run migrations (OPTIONAL - uncomment if you want auto-migrations)
# echo -e "\n${YELLOW}🗄️  Running database migrations...${NC}"
# php artisan migrate --force
# echo -e "${GREEN}✓ Migrations completed${NC}"

# Step 7: Set permissions
echo -e "\n${YELLOW}📁 Setting permissions...${NC}"
chmod -R 755 storage bootstrap/cache 2>/dev/null || true
chmod -R 755 public 2>/dev/null || true

echo -e "${GREEN}✓ Permissions set${NC}"

# Step 8: Restart queue workers if using queues (OPTIONAL)
# echo -e "\n${YELLOW}🔄 Restarting queue workers...${NC}"
# php artisan queue:restart
# echo -e "${GREEN}✓ Queue workers restarted${NC}"

echo -e "\n${GREEN}✅ Deployment completed successfully at $(date)!${NC}"



