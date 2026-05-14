#!/bin/bash
# ============================================================
# Campus Buddy — Debugging Startup Script
# ============================================================

# Don't use set -e yet so we can capture errors
echo "----------------------------------------------------"
echo "🚀 STARTUP BEGUN: $(date)"
echo "----------------------------------------------------"

# 1. Check if APP_KEY is set
if [ -z "$APP_KEY" ]; then
    echo "❌ ERROR: APP_KEY is not set in Render environment variables!"
else
    echo "✅ APP_KEY is detected."
fi

# 2. Fix Permissions
echo "🔧 Setting permissions..."
mkdir -p /var/www/storage/framework/{sessions,views,cache}
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# 3. Try to connect to the database / Run Migrations
echo "🗃️ Attempting database migrations..."
if php artisan migrate --force; then
    echo "✅ Migrations successful."
else
    echo "❌ ERROR: Migrations failed. Check your DATABASE_URL and Neon settings."
    # We continue anyway so the container stays alive long enough for us to read this log
fi

# 4. Clear/Cache config
echo "📦 Optimizing Laravel..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🌐 Starting Nginx & PHP-FPM via Supervisor..."
echo "----------------------------------------------------"

# Start Supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
