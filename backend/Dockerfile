FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Change current user to www
USER www-data

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Change back to root
USER root

# Copy php.ini
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Expose port 80 and start Apache
EXPOSE 80
CMD ["apache2-foreground"]
