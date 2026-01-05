FROM php:8.4-fpm

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
    libicu-dev \
    libcurl4-openssl-dev \
    libsodium-dev \
    zip \
    unzip \
    libzip-dev \
    gosu \
    gnupg \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        curl \
        dom \
        xml \
        xmlreader \
        xmlwriter \
        sodium \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js LTS (v22.x) and npm
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && npm install -g npm@latest

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy PHP configuration
COPY docker/php.ini $PHP_INI_DIR/conf.d/99-custom.ini

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
