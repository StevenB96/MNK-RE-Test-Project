#!/bin/sh

# Enable debugging: print commands and their arguments as they are executed
set -x

# Exit immediately if a command exits with a non-zero status
set -e

# Wait for the database to be available
echo "Waiting for MySQL at $DB_HOST:$DB_PORT..."
until nc -z -v -w30 "$DB_HOST" "$DB_PORT"; do
  echo "Waiting for MySQL..."
  sleep 1
done
echo "MySQL is available at $DB_HOST:$DB_PORT."

# Create the database if it doesn't exist
echo "Creating database '$DB_DATABASE' if not exists..."
# Show variables for debugging
echo "Connecting with host: $DB_HOST, port: $DB_PORT, user: $DB_USERNAME"
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if [ $? -ne 0 ]; then
  echo "Error: Failed to create database '$DB_DATABASE'."
  exit 1
fi

# Clear config cache to avoid stale DB config
echo "Clearing Laravel config cache..."
php artisan config:clear
if [ $? -ne 0 ]; then
  echo "Warning: Failed to clear config cache."
fi

# Laravel setup commands
echo "Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "Running migrations..."
php artisan migrate --force
if [ $? -ne 0 ]; then
  echo "Error: Migrations failed."
  exit 1
fi

# Run seeders
echo "Seeding database..."
php artisan db:seed --force
if [ $? -ne 0 ]; then
  echo "Error: Seeding failed."
  exit 1
fi

# Execute the CMD from Dockerfile (e.g., php-fpm)
echo "Executing command: $@"
exec "$@"