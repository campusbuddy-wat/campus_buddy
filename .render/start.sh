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
fi

# 2. Fix Permissions
echo "🔧 Setting permissions..."
mkdir -p /var/www/storage/framework/{sessions,views,cache/data}
mkdir -p /var/www/storage/app/public
mkdir -p /var/www/storage/logs
mkdir -p /var/www/bootstrap/cache

# Ensure www-data owns everything before running artisan commands
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# 3. Create storage symlink
echo "🔗 Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

# 4. Clear/Cache
echo "🧹 Cleaning and optimizing..."
php artisan config:clear
php artisan config:cache

# 5. Run migrations
echo "🗃️ Running database migrations..."
# Using the non-pooled URL is CRITICAL for this step to succeed.
if php artisan migrate --force 2>&1; then
    echo "✅ Migrations successful."
    echo "🌱 Filling database with data..."
    php artisan db:seed --force
    echo "📊 EVENT COUNT: $(php artisan tinker --execute='echo \App\Models\Event::count();')"
    echo "✅ Seeding successful."
else
    echo "⚠️ Normal migrate failed. Trying fresh migration..."
    if php artisan migrate:fresh --force --seed 2>&1; then
        echo "📊 EVENT COUNT: $(php artisan tinker --execute='echo \App\Models\Event::count();')"
        echo "✅ Fresh migrations and seeding successful."
    else
        echo "❌ Fresh migration failed as well."
    fi
fi

# 6. Final Cache & Assets
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

echo "🎨 Publishing Filament assets..."
php artisan filament:assets 2>/dev/null || true
php artisan icons:cache 2>/dev/null || true

echo "🌐 Starting Nginx & PHP-FPM..."
echo "----------------------------------------------------"

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
