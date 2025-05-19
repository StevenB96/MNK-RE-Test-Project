# --- Base PHP image with extensions ---
FROM php:8.1-fpm AS base

# Install system deps for PHP extensions
RUN apt-get update \
 && apt-get install -y git unzip libzip-dev zip \
 && docker-php-ext-install pdo pdo_mysql zip \
 && rm -rf /var/lib/apt/lists/*

# --- Stage 1: Install PHP dependencies & run Composer scripts ---
FROM base AS php-builder

WORKDIR /app

# Copy only composer files first (for better caching)
COPY composer.json composer.lock ./

# Install Composer itself
RUN curl -sS https://getcomposer.org/installer | php \
 && mv composer.phar /usr/local/bin/composer

# Copy the rest of the application
COPY . .

# Ensure all storage/bootstrap dirs exist (including cache/data)
RUN mkdir -p \
      storage/framework/cache/data \
      storage/framework/sessions \
      storage/framework/views \
      storage/logs \
      bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Install PHP dependencies (runs post-autoload scripts like package:discover)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# --- Stage 2: Compile frontend assets ---
FROM node:18 AS node-builder

WORKDIR /app

# Copy only package files first (for better caching)
COPY package.json package-lock.json ./

# Install Node deps
RUN npm ci

# Copy and build
COPY . .
RUN npm run production

# --- Stage 3: Final production image ---
FROM base AS production

WORKDIR /var/www/html

# Copy PHP app & vendor
COPY --from=php-builder /app /var/www/html

# Copy built assets
COPY --from=node-builder /app/public/js  public/js
COPY --from=node-builder /app/public/css public/css

# Fix permissions on Laravel storage & cache
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
