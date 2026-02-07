FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
	libmagickwand-dev \
	imagemagick \
    unzip \
	zip \
	libzip-dev \
	ca-certificates \
	&& pecl install apcu imagick \
    && docker-php-ext-enable apcu imagick \
    && update-ca-certificates

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath xml fileinfo zip

# Install GD extension
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install OPcache
RUN docker-php-ext-install opcache

# Install global Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/html/sushi

# Create system user
RUN groupadd -g 1000 www && \
    useradd -u 1000 -ms /bin/bash -g www www

# Change ownership
RUN chown -R www:www /var/html/sushi

# Switch to user
USER www

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]