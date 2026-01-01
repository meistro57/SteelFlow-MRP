#!/bin/sh

# Only fix permissions if running as root
if [ "$(id -u)" = "0" ]; then
    # Check if .env file exists, if not copy from .env.example
    if [ ! -f /var/www/.env ] && [ -f /var/www/.env.example ]; then
        echo "Creating .env file from .env.example..."
        cp /var/www/.env.example /var/www/.env
    fi

    # Generate APP_KEY if it's empty or missing
    if [ -f /var/www/.env ]; then
        if ! grep -q "^APP_KEY=base64:" /var/www/.env; then
            echo "Generating application key..."
            APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
            sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" /var/www/.env
        fi
    fi

    # Create necessary directories if they don't exist
    for dir in /var/www/.composer/cache /var/www/storage /var/www/bootstrap/cache; do
        if [ ! -d "$dir" ]; then
            mkdir -p "$dir" 2>/dev/null || true
        fi
    done

    # Fix ownership of specific directories (non-recursive for speed)
    chown -R www-data:www-data /var/www/.composer 2>/dev/null || true
    chown -R www-data:www-data /var/www/storage 2>/dev/null || true
    chown -R www-data:www-data /var/www/bootstrap/cache 2>/dev/null || true
    chown www-data:www-data /var/www/.env 2>/dev/null || true
fi

# Execute command as root (php-fpm will drop privileges for worker processes)
exec "$@"
