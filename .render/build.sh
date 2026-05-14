#!/bin/bash
# ============================================================
# Campus Buddy — Render Build Script
# ============================================================
# This script runs AFTER Docker builds the image.
# It handles Laravel-specific setup commands.
# ============================================================

set -e

echo "🔧 Running Laravel production setup..."

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Clear and cache configuration for production
echo "📦 Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "🗃️ Running database migrations..."
php artisan migrate --force

# Link storage for file uploads
echo "🔗 Linking storage..."
php artisan storage:link || true

echo "✅ Build complete! Campus Buddy is ready."
