#!/bin/bash
set -e

# Configure git safe directory for www-data user
if [ "$(id -u)" = "0" ]; then
    # Running as root - configure git and fix permissions
    git config --system --add safe.directory /var/www

    # Ensure composer cache directory exists and has proper permissions
    mkdir -p /var/www/.composer/cache
    chown -R www-data:www-data /var/www/.composer 2>/dev/null || true

    # Create storage and bootstrap cache directories if they don't exist
    mkdir -p /var/www/storage /var/www/bootstrap/cache
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

    # Execute the main command as www-data
    exec gosu www-data "$@"
else
    # Already running as www-data
    git config --global --add safe.directory /var/www 2>/dev/null || true
    exec "$@"
fi
