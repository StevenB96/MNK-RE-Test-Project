#!/bin/sh

# Wait for the database to be available
echo "Waiting for MySQL at $DB_HOST:$DB_PORT..."
until nc -z -v -w30 $DB_HOST $DB_PORT; do
  echo "Waiting for MySQL..."
  sleep 1
done

# Laravel setup commands
echo "Running Laravel setup..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force || true

# Execute the CMD from Dockerfile (e.g. php-fpm)
exec "$@"
