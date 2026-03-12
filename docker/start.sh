#!/bin/bash
set -e

# Extract APP_KEY from build-time .env before removing it
if [ -z "$APP_KEY" ] && [ -f /var/www/html/.env ]; then
    export APP_KEY=$(grep '^APP_KEY=' /var/www/html/.env | cut -d= -f2-)
fi
rm -f /var/www/html/.env

# Map Railway MySQL vars to Laravel's expected DB_* vars
export DB_CONNECTION=mysql
export DB_HOST="${DB_HOST:-$MYSQLHOST}"
export DB_PORT="${DB_PORT:-$MYSQLPORT}"
export DB_DATABASE="${DB_DATABASE:-$MYSQLDATABASE}"
export DB_USERNAME="${DB_USERNAME:-$MYSQLUSER}"
export DB_PASSWORD="${DB_PASSWORD:-$MYSQLPASSWORD}"

# Map Railway Redis vars to Laravel's expected REDIS_* vars
export REDIS_HOST="${REDIS_HOST:-$REDISHOST}"
export REDIS_PORT="${REDIS_PORT:-$REDISPORT}"
export REDIS_PASSWORD="${REDIS_PASSWORD:-$REDISPASSWORD}"

# Use Redis for session/cache/queue now that it's available
export SESSION_DRIVER="${SESSION_DRIVER:-redis}"
export CACHE_STORE="${CACHE_STORE:-redis}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-redis}"

# Suppress "not a git repo" warnings from Sentry
export SENTRY_RELEASE="${SENTRY_RELEASE:-unknown}"

# Railway injects PORT — tell Apache to listen on it
if [ -n "$PORT" ]; then
    sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
    sed -i "s/:80/:$PORT/" /etc/apache2/sites-available/*.conf
fi

# Cache config with runtime env vars
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Skip auto-migrations — existing DB has all tables from CakePHP era.
# Run migrations manually via `railway run php artisan migrate` when needed.
# php artisan migrate --force

# Ensure only mpm_prefork is loaded (Railway may inject mpm_event at runtime)
a2dismod mpm_event mpm_worker 2>/dev/null || true
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
a2enmod mpm_prefork 2>/dev/null || true

# Start Apache
apache2-foreground
