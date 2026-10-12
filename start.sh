#!/bin/sh
set -eu

cd "$(dirname "$0")"

export APP_ENV="${APP_ENV:-prod}"
export APP_DEBUG="${APP_DEBUG:-0}"
export PORT="${PORT:-10000}"
export DEFAULT_URI="${DEFAULT_URI:-${RENDER_EXTERNAL_URL:-http://localhost}}"
export APP_SECRET="${APP_SECRET:-$(php -r 'echo bin2hex(random_bytes(32));')}"

mkdir -p var
php bin/init-db.php
php bin/console cache:clear --no-debug
chown -R www-data:www-data var

printf 'Listen %s\n' "$PORT" > /etc/apache2/ports.conf
exec apache2-foreground
