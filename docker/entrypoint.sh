#!/bin/sh

# Wait for the database to be available
echo "Waiting for MySQL at $DB_HOST:$DB_PORT..."
until nc -z -v -w30 $DB_HOST $DB_PORT; do
  echo "Waiting for MySQL..."
  sleep 1
done

# Create the database if it doesn't exist
echo "Creating database $DB_DATABASE if not exists..."
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Clear config cache to avoid stale DB config
echo "Clearing config cache..."
php artisan config:clear

# Laravel setup commands
echo "Running Laravel setup..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Run seeders
php artisan db:seed --force

# Execute the CMD from Dockerfile (e.g. php-fpm)
exec "$@"
