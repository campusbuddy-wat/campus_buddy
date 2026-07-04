#!/bin/bash
# ============================================================
# Campus Buddy — Production Startup Script for Render
# ============================================================

echo "----------------------------------------------------"
echo "🚀 STARTUP BEGUN: $(date)"
echo "----------------------------------------------------"

# ============================================================
# 1. Write the .env file from environment variables
#    All values read from Render dashboard environment vars.
#    DB defaults to Neon PostgreSQL.
# ============================================================
echo "📝 Writing production .env file..."
cat > /var/www/.env << ENVEOF
APP_NAME="${APP_NAME:-Campus Buddy}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY:-base64:KcZbz+dxSpTuczQmRwpgUJh6eu84m3cDW0/PJl2PfgE=}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-https://campus-buddy.onrender.com}"
APP_TIMEZONE="${APP_TIMEZONE:-Asia/Dhaka}"

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL="${LOG_LEVEL:-error}"

# ==================== DATABASE (Neon PostgreSQL) ====================
DB_CONNECTION="${DB_CONNECTION:-pgsql}"
DB_HOST="${DB_HOST:-ep-broad-feather-aqxvds3v-pooler.c-8.us-east-1.aws.neon.tech}"
DB_PORT="${DB_PORT:-5432}"
DB_DATABASE="${DB_DATABASE:-neondb}"
DB_USERNAME="${DB_USERNAME:-neondb_owner}"
DB_PASSWORD="${DB_PASSWORD}"
DB_SSLMODE="${DB_SSLMODE:-require}"

SESSION_DRIVER="${SESSION_DRIVER:-database}"
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"
CACHE_STORE="${CACHE_STORE:-file}"

MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@campusbuddy.app"
MAIL_FROM_NAME="Campus Buddy"

VITE_APP_NAME="Campus Buddy"

# ==================== GROQ AI ====================
GROQ_API_KEY="${GROQ_API_KEY}"
GROQ_MODEL="${GROQ_MODEL:-llama-3.3-70b-versatile}"
GROQ_MAX_TOKENS="${GROQ_MAX_TOKENS:-1536}"
GROQ_TEMPERATURE="${GROQ_TEMPERATURE:-0.7}"

# ==================== CLOUDINARY ====================
CLOUDINARY_URL="${CLOUDINARY_URL}"

# ==================== Python RAG Microservice ====================
VISITOR_AI_URL="${VISITOR_AI_URL}"
ENVEOF

echo "✅ .env written. DB_HOST=${DB_HOST:-ep-broad-feather-aqxvds3v-pooler.c-8.us-east-1.aws.neon.tech}"

# ============================================================
# 2. Fix Permissions
# ============================================================
echo "🔧 Setting permissions..."
mkdir -p /var/www/storage/framework/{sessions,views,cache/data}
mkdir -p /var/www/storage/app/public
mkdir -p /var/www/storage/logs
mkdir -p /var/www/bootstrap/cache

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# ============================================================
# 3. Create storage symlink
# ============================================================
echo "🔗 Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

# ============================================================
# 4. Clear and cache config
# ============================================================
echo "🧹 Cleaning and optimizing..."
php artisan config:clear
php artisan config:cache

# ============================================================
# 5. Run migrations
# ============================================================
echo "🗃️ Running database migrations..."
if php artisan migrate --force 2>&1; then
    echo "✅ Migrations successful."
else
    echo "⚠️ Normal migrate failed. Check DB credentials in Render env vars."
fi

# ============================================================
# 6. Publish assets
# ============================================================
echo "🎨 Publishing assets..."
php artisan filament:assets --force 2>/dev/null || true
php artisan livewire:publish --assets --force 2>/dev/null || true
php artisan icons:cache 2>/dev/null || true

echo "🌐 Starting Nginx & PHP-FPM..."
echo "----------------------------------------------------"

# Configure Nginx to listen on the port provided by Render
sed -i "s/listen 80 default_server;/listen ${PORT:-80} default_server;/g" /etc/nginx/sites-available/default
sed -i "s/listen \[::\]:80 default_server;/listen [::]:${PORT:-80} default_server;/g" /etc/nginx/sites-available/default

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
