FROM php:8.3-fpm

# Set labels
LABEL maintainer="SteelFlow MRP"
LABEL version="2.0"

# Install system dependencies and PHP extensions in a single layer
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Configure git to trust /var/www directory (fixes ownership issues with mounted volumes)
RUN git config --global --add safe.directory /var/www

# Switch to non-root user
USER www-data
