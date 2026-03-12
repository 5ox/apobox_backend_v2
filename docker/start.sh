#!/bin/bash
set -e

# Extract APP_KEY from build-time .env before removing it
if [ -z "$APP_KEY" ] && [ -f /var/www/html/.env ]; then
    export APP_KEY=$(grep '^APP_KEY=' /var/www/html/.env | cut -d= -f2-)
fi
rm -f /var/www/html/.env

# Ensure APP_KEY has the required base64: prefix
if [ -n "$APP_KEY" ] && [[ "$APP_KEY" != base64:* ]]; then
    echo "WARNING: APP_KEY missing base64: prefix — adding it"
    export APP_KEY="base64:${APP_KEY}"
fi

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

# Default to file-based session/cache (safe fallback); set to redis in Railway env if Redis works
export SESSION_DRIVER="${SESSION_DRIVER:-file}"
export CACHE_STORE="${CACHE_STORE:-file}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"

# Test Redis connectivity — fall back to file if unreachable
if [ "$SESSION_DRIVER" = "redis" ] || [ "$CACHE_STORE" = "redis" ] || [ "$QUEUE_CONNECTION" = "redis" ]; then
    REDIS_OK=$(php -r "
        try {
            \$r = new Redis();
            \$r->connect('${REDIS_HOST:-127.0.0.1}', ${REDIS_PORT:-6379}, 3);
            \$pw = '${REDIS_PASSWORD}';
            if (\$pw !== '') \$r->auth(\$pw);
            \$r->ping();
            echo 'ok';
        } catch (Throwable \$e) {
            echo 'fail';
        }
    " 2>/dev/null)
    if [ "$REDIS_OK" != "ok" ]; then
        echo "WARNING: Redis unreachable at ${REDIS_HOST:-127.0.0.1}:${REDIS_PORT:-6379}, falling back to file-based drivers"
        export SESSION_DRIVER=file
        export CACHE_STORE=file
        export QUEUE_CONNECTION=sync
    else
        echo "Redis connected at ${REDIS_HOST}:${REDIS_PORT}"
    fi
fi

# Force logging to file so /health can read errors (stderr is invisible to us)
export LOG_CHANNEL=daily
export LOG_STACK=daily

# TEMPORARY: Show errors in browser for debugging (remove once stable)
export APP_DEBUG=true

# Suppress "not a git repo" warnings from Sentry
export SENTRY_RELEASE="${SENTRY_RELEASE:-unknown}"

# Railway injects PORT — tell Apache to listen on it
if [ -n "$PORT" ]; then
    sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
    sed -i "s/:80/:$PORT/" /etc/apache2/sites-available/*.conf
fi

# Clear stale debug files from previous deploys
rm -f /var/www/html/storage/logs/last_error.json /var/www/html/storage/logs/laravel*.log

# Cache config with runtime env vars
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Seed migrations table with legacy tables (idempotent), then run new migrations
php artisan migrate:seed-existing
php artisan migrate --force

# One-time: rebuild search index to fix legacy column names (remove after first deploy)
php artisan app:rebuild-search-index

# Ensure only mpm_prefork is loaded (Railway may inject mpm_event at runtime)
a2dismod mpm_event mpm_worker 2>/dev/null || true
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
a2enmod mpm_prefork 2>/dev/null || true

# Start Apache
apache2-foreground
