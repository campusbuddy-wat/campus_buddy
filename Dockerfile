# ============================================================
# Campus Buddy — Production Dockerfile for Render
# ============================================================
# Uses PHP 8.2 + Nginx + Node.js (for Vite build) + Tesseract OCR
# ============================================================

FROM php:8.2-cli AS base

# ----------------------------------------------------------
# 1. Install system dependencies
# ----------------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    nginx \
    supervisor \
    tesseract-ocr \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ----------------------------------------------------------
# 2. Install Composer
# ----------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ----------------------------------------------------------
# 3. Install Node.js 20 (for Vite asset build)
# ----------------------------------------------------------
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ----------------------------------------------------------
# 4. Set working directory
# ----------------------------------------------------------
WORKDIR /var/www

# ----------------------------------------------------------
# 5. Copy project files
# ----------------------------------------------------------
COPY . .

# ----------------------------------------------------------
# 6. Install PHP dependencies (production)
# ----------------------------------------------------------
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ----------------------------------------------------------
# 7. Install NPM dependencies & build Vite assets
# ----------------------------------------------------------
RUN npm ci && npm run build && rm -rf node_modules

# ----------------------------------------------------------
# 8. Set correct permissions
# ----------------------------------------------------------
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# ----------------------------------------------------------
# 9. Copy Nginx & Supervisor configs
# ----------------------------------------------------------
COPY .render/nginx.conf /etc/nginx/sites-available/default
COPY .render/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ----------------------------------------------------------
# 10. Expose port (Render uses $PORT, default 80)
# ----------------------------------------------------------
EXPOSE 80

# ----------------------------------------------------------
# 11. Start Supervisor (manages Nginx + PHP together)
# ----------------------------------------------------------
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
