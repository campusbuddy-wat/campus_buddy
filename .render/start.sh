#!/bin/bash
# ============================================================
# Campus Buddy — Container Startup Script
# ============================================================

set -e

echo "🚀 Starting Campus Buddy..."

# Ensure storage directory exists and has correct permissions
mkdir -p /var/www/storage/framework/{sessions,views,cache}
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Run Laravel optimizations
echo "📦 Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations (IMPORTANT)
echo "🗃️ Running database migrations..."
php artisan migrate --force

# Start Supervisor (Nginx + PHP-FPM)
echo "🌐 Starting web server..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
