#!/bin/bash
set -e

# Wait for services to be ready
echo "Waiting for services to be ready..."
sleep 5

# Create necessary directories with proper permissions
echo "Creating necessary directories..."
mkdir -p bootstrap/cache storage/framework/{sessions,views,cache}
chmod -R 775 bootstrap/cache storage

# Install dependencies if vendor folder doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts --no-security-blocking
fi

# Generate application key if not set (only if .env exists)
if [ -f ".env" ] && ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "Generating application key..."
    php artisan key:generate --ansi || true
fi

# Wait for database to be ready
echo "Waiting for database connection..."
for i in {1..30}; do
    php artisan db:show --quiet 2>/dev/null && break || sleep 1
done

# Run database migrations (migrate will automatically skip already run migrations)
echo "Running database migrations..."
php artisan migrate --force 2>&1 || {
    # Ignore errors about existing tables - migrations may have partially run
    echo "Continuing despite migration warnings..."
}

# Execute the main command
exec "$@"
