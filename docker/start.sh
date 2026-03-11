#!/bin/bash
set -e

# Railway injects PORT — tell Apache to listen on it
if [ -n "$PORT" ]; then
    sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
    sed -i "s/:80/:$PORT/" /etc/apache2/sites-available/*.conf
fi

# Cache config with runtime env vars
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (--force required for production)
php artisan migrate --force

# Start Apache
apache2-foreground
