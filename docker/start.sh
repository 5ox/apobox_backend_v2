#!/bin/bash
set -e

# Remove build-time .env so only Railway's injected env vars are used
rm -f /var/www/html/.env

# Map Railway MySQL vars to Laravel's expected DB_* vars
export DB_CONNECTION=mysql
export DB_HOST="${DB_HOST:-$MYSQLHOST}"
export DB_PORT="${DB_PORT:-$MYSQLPORT}"
export DB_DATABASE="${DB_DATABASE:-$MYSQLDATABASE}"
export DB_USERNAME="${DB_USERNAME:-$MYSQLUSER}"
export DB_PASSWORD="${DB_PASSWORD:-$MYSQLPASSWORD}"

# Railway injects PORT — tell Apache to listen on it
if [ -n "$PORT" ]; then
    sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
    sed -i "s/:80/:$PORT/" /etc/apache2/sites-available/*.conf
fi

# Cache config with runtime env vars
php artisan config:cache
php artisan route:cache
php artisan view:cache

# TEMPORARY: fake-mark all existing migrations, then run normally
# TODO: revert to just `php artisan migrate --force` after first deploy
php artisan migrate --fake --force
php artisan migrate --force

# Start Apache
apache2-foreground
