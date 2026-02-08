#!/bin/sh
set -e

cd /var/www

if [ ! -f vendor/autoload.php ]; then
    echo "vendor/ not found. Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

mkdir -p bootstrap/cache \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs
chmod -R 777 bootstrap/cache storage || true

# Ensure public storage symlink exists for uploaded files (avatars, etc.)
php artisan storage:link --no-interaction >/dev/null 2>&1 || true

exec "$@"
