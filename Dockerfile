# Dockerfile for PHP + Apache
FROM php:8.5-apache

# Install system dependencies
RUN apt-get update \
    && apt-get install -y \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        zip \
        unzip \
        git \
        libzip-dev \
        mariadb-client \
        nano \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Set Apache DocumentRoot to /var/www/html/public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
# Allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/html>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copy custom Apache configs (Alias for icons)
COPY docker/icons-alias.conf /etc/apache2/conf-available/icons-alias.conf
RUN a2enconf icons-alias.conf || true

# Expose port 80
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]