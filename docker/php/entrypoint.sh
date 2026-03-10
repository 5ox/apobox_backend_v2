#!/bin/sh

if [ "${APP_ENV}" = "prod" ] || [ "${APP_ENV}" = "stage" ]; then
	sed -i 's|memcached:11211|'$CACHE_ENDPOINT'|' /usr/local/etc/php/conf.d/local.ini
fi

exec "$@"
