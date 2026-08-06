#!/bin/bash
# ============================================
# UPDATE.SH - One-Click Update Script
# ESIMKO - Elektronik Sistem Informasi dan Manajemen Koperasi
# ============================================

echo ""
echo "============================================"
echo "  ESIMKO - System Update"
echo "============================================"
echo ""

# Check if in correct directory
if [ ! -f "docker-compose.dev.yml" ] && [ ! -f "docker-compose.prod.yml" ]; then
    echo "ERROR: docker-compose files not found!"
    echo "Please run this script from the project directory."
    exit 1
fi

# Step 1: Backup database
echo "[1/6] Backing up database..."
BACKUP_DIR="backups"
mkdir -p $BACKUP_DIR
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/esimko_$TIMESTAMP.sql"

docker exec esimko-db mysqldump -u root -proot_password_123 esimko > "$BACKUP_FILE" 2>/dev/null
if [ -s "$BACKUP_FILE" ]; then
    echo "✓ Database backed up to: $BACKUP_FILE"
    cp "$BACKUP_FILE" "esimko_latest_backup.sql"
else
    echo "⚠ Backup may have failed (database might not be running)"
fi

# Step 2: Pull latest code
echo ""
echo "[2/6] Pulling latest code from GitHub..."
git pull origin main
if [ $? -ne 0 ]; then
    echo "ERROR: Git pull failed!"
    echo "Try: git stash && git pull origin main && git stash pop"
    exit 1
fi
echo "✓ Code updated"

# Step 3: Check for migration changes
echo ""
echo "[3/6] Checking for database migrations..."
MIGRATION_CHANGED=$(git diff HEAD~1 --name-only 2>/dev/null | grep "database/migrations")
if [ -n "$MIGRATION_CHANGED" ]; then
    echo "⚡ Migration changes detected - will run after rebuild"
else
    echo "✓ No migration changes detected"
fi

# Step 4: Stop containers
echo ""
echo "[4/6] Stopping containers..."
docker-compose -f docker-compose.dev.yml down 2>/dev/null
docker-compose -f docker-compose.prod.yml down 2>/dev/null
echo "✓ Containers stopped"

# Step 5: Rebuild and start
echo ""
echo "[5/6] Rebuilding (this may take 2-5 minutes)..."
if [ -f "docker-compose.prod.yml" ]; then
    docker-compose -f docker-compose.prod.yml up -d --build
else
    docker-compose -f docker-compose.dev.yml up -d --build
fi

if [ $? -ne 0 ]; then
    echo "ERROR: Build failed!"
    echo ""
    echo "To restore database from backup:"
    echo "  docker exec -i esimko-db mysql -u root -proot_password_123 esimko < $BACKUP_FILE"
    exit 1
fi
echo "✓ Build completed"

# Wait for services
sleep 10

# Step 6: Laravel sync
echo ""
echo "[6/6] Running Laravel optimizations..."

# Grant MySQL privileges
docker exec esimko-db mysql -u root -proot_password_123 -e "CREATE USER IF NOT EXISTS 'esimko'@'%' IDENTIFIED BY 'esimko_password_123'; GRANT ALL PRIVILEGES ON esimko.* TO 'esimko'@'%'; FLUSH PRIVILEGES;" 2>/dev/null

# Run migrations
docker exec esimko-app php artisan migrate --force 2>&1
echo "✓ Migrations applied"

# Clear caches
docker exec esimko-app php artisan config:clear 2>/dev/null
docker exec esimko-app php artisan cache:clear 2>/dev/null
docker exec esimko-app php artisan view:clear 2>/dev/null
# storage:link fails silently if public/storage is a real dir (git-tracked files).
# ponytail: force-clean then link so new uploads (storage/app/public/*) serve via /storage/*.
docker exec esimko-app sh -c 'rm -rf /var/www/html/public/storage && php /var/www/html/artisan storage:link' 2>&1
echo "✓ Caches cleared"

# For production, cache config
if [ -f "docker-compose.prod.yml" ]; then
    sleep 2
    docker exec esimko-app touch /var/www/html/.env 2>/dev/null
    sleep 1
    docker exec esimko-app php artisan config:cache 2>/dev/null
    docker exec esimko-app php artisan route:cache 2>/dev/null
    echo "✓ Production cache built"
fi

# Cleanup old backups (keep last 5)
echo ""
echo "Cleaning up old backups (keeping last 5)..."
ls -t $BACKUP_DIR/esimko_*.sql 2>/dev/null | tail -n +6 | xargs -r rm
echo "✓ Cleanup completed"

# Done
echo ""
echo "============================================"
echo "  UPDATE COMPLETE!"
echo "============================================"
echo ""
echo "  Application: http://localhost:8080"
echo "  Backup file: $BACKUP_FILE"
echo ""
echo "  To restore if needed:"
echo "  docker exec -i esimko-db mysql -u root -proot_password_123 esimko < $BACKUP_FILE"
echo ""
