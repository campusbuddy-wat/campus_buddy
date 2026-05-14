#!/bin/bash
# ============================================================
# Campus Buddy — Production Startup Script for Render
# ============================================================
set -e

echo "----------------------------------------------------"
echo "🚀 STARTUP BEGUN: $(date)"
echo "----------------------------------------------------"

# 1. Check if APP_KEY is set
if [ -z "$APP_KEY" ]; then
    echo "❌ ERROR: APP_KEY is not set in Render environment variables!"
    exit 1
else
    echo "✅ APP_KEY is detected."
fi

# 2. Fix Permissions
echo "🔧 Setting permissions..."
mkdir -p /var/www/storage/framework/{sessions,views,cache}
mkdir -p /var/www/storage/app/public
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# 3. Create storage symlink (public/storage -> storage/app/public)
echo "🔗 Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

# 4. Clear all caches first
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 5. Cache config BEFORE migrations so DB settings are correct
echo "📦 Caching configuration..."
php artisan config:cache

# 6. Run Migrations
echo "🗃️ Attempting database migrations..."
if php artisan migrate --force 2>&1; then
    echo "✅ Migrations successful."
else
    echo "❌ ERROR: Migrations failed. Check your DATABASE_URL and Neon settings."
    # Continue so Nginx still starts and we can read logs
fi

# 7. Cache routes and views AFTER config is ready
php artisan route:cache
php artisan view:cache

echo "🌐 Starting Nginx & PHP-FPM via Supervisor..."
echo "----------------------------------------------------"

# Start Supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
