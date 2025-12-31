#!/bin/sh

# Only fix permissions if running as root
if [ "$(id -u)" = "0" ]; then
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

    # Drop privileges and execute command
    exec gosu www-data "$@"
fi

# If not root, just execute the command
exec "$@"
