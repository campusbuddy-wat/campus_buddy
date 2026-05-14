#!/bin/bash
# ============================================================
# Campus Buddy — Production Startup Script for Render
# ============================================================

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

# 2. Fix Permissions — create ALL required directories
echo "🔧 Setting permissions..."
mkdir -p /var/www/storage/framework/{sessions,views,cache/data}
mkdir -p /var/www/storage/app/public
mkdir -p /var/www/storage/logs
mkdir -p /var/www/bootstrap/cache

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# 3. Create storage symlink
echo "🔗 Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

# 4. Clear all Laravel caches (safely)
echo "🧹 Clearing caches..."
php artisan config:clear  2>/dev/null || true
php artisan route:clear   2>/dev/null || true
php artisan view:clear    2>/dev/null || true
php artisan cache:clear   2>/dev/null || true

# 5. Cache config BEFORE migrations so DB settings are loaded
echo "📦 Caching configuration..."
php artisan config:cache

# 6. Run migrations — try normal first, fall back to fresh if DB is in partial state
echo "🗃️ Running database migrations..."
if php artisan migrate --force 2>&1; then
    echo "✅ Migrations successful."
else
    echo "⚠️  Normal migrate failed (DB may be in partial state). Trying fresh migration..."
    if php artisan migrate:fresh --force 2>&1; then
        echo "✅ Fresh migrations successful."
    else
        echo "❌ ERROR: All migration attempts failed. Check DATABASE_URL."
        # Don't exit — keep container alive so you can read the logs
    fi
fi

# 7. Cache routes and views AFTER everything is set up
echo "🗺️  Caching routes and views..."
php artisan route:cache  2>/dev/null || true
php artisan view:cache   2>/dev/null || true

echo "🌐 Starting Nginx & PHP-FPM via Supervisor..."
echo "----------------------------------------------------"

# Start Supervisor (keeps container alive)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
