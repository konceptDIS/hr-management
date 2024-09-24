FROM php:7.4-fpm

ADD . /var/www/leave
WORKDIR /var/www/leave

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nano

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer   
RUN composer install

EXPOSE 9000
# Start Laravel server
CMD php artisan serve --host=0.0.0.0 --port=9000