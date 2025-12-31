#!/bin/bash
set -e

# Configure git safe directory for www-data user
if [ "$(id -u)" = "0" ]; then
    # Running as root - fix permissions
    # Ensure directories exist
    mkdir -p /var/www/.composer/cache 2>/dev/null || true
    mkdir -p /var/www/storage 2>/dev/null || true
    mkdir -p /var/www/bootstrap/cache 2>/dev/null || true

    # Fix ownership if directories exist
    [ -d /var/www/.composer ] && chown -R www-data:www-data /var/www/.composer 2>/dev/null || true
    [ -d /var/www/storage ] && chown -R www-data:www-data /var/www/storage 2>/dev/null || true
    [ -d /var/www/bootstrap/cache ] && chown -R www-data:www-data /var/www/bootstrap/cache 2>/dev/null || true

    # Execute the main command as www-data
    exec gosu www-data "$@"
else
    # Already running as www-data
    exec "$@"
fi
