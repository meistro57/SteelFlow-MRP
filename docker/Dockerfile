FROM php:8.3-fpm

# Set labels
LABEL maintainer="SteelFlow MRP"
LABEL version="2.0"

# Install system dependencies and PHP extensions in a single layer
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    gosu \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Configure git to trust /var/www directory at system level
RUN git config --system --add safe.directory /var/www

# Set entrypoint to handle permissions
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Default command for php-fpm (run in foreground to prevent container exit)
CMD ["php-fpm", "-F"]
