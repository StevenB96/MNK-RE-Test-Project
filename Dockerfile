# --- Base PHP image with extensions ---
FROM php:8.1-fpm AS base

# Install system deps for PHP extensions and CLI tools
RUN apt-get update \
 && apt-get install -y \
      git \
      unzip \
      libzip-dev \
      zip \
      netcat-openbsd \
      default-mysql-client \
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

# --- Stage 3: Final PHP production image ---
FROM base AS php-fpm-prod

WORKDIR /var/www/html

# Copy PHP app & vendor
COPY --from=php-builder /app /var/www/html

# Copy built assets
COPY --from=node-builder /app/public/js public/js
COPY --from=node-builder /app/public/css public/css

# Fix permissions on Laravel storage & cache
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

# Runtime Laravel bootstrap logic (migrations, seeding, etc.)
CMD /bin/sh -c "\
  set -xe; \
  echo 'Waiting for MySQL at $DB_HOST:$DB_PORT...'; \
  until nc -z -v -w30 \"$DB_HOST\" \"$DB_PORT\"; do \
    echo 'Waiting for MySQL...'; \
    sleep 1; \
  done; \
  echo 'MySQL is available at $DB_HOST:$DB_PORT.'; \
  echo 'Creating database $DB_DATABASE if not exists...'; \
  mysql -h \"$DB_HOST\" -P \"$DB_PORT\" -u \"$DB_USERNAME\" -p\"$DB_PASSWORD\" -e \"CREATE DATABASE IF NOT EXISTS \\\`$DB_DATABASE\\\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\" || { echo 'Failed to create database.'; exit 1; }; \
  php artisan config:clear || echo 'Warning: config:clear failed'; \
  php artisan config:cache; \
  php artisan route:cache; \
  php artisan view:cache; \
  php artisan migrate --force || { echo 'Error: Migrations failed.'; exit 1; }; \
  php artisan db:seed --force || { echo 'Error: Seeding failed.'; exit 1; }; \
  exec php-fpm \
"

# --- Stage 4: Nginx image ---
FROM nginx:alpine AS production

# Copy Nginx config
COPY ./nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy app from php-fpm-prod stage
COPY --from=php-fpm-prod /var/www/html /var/www/html

# Set permissions
RUN chown -R nginx:nginx /var/www/html

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
