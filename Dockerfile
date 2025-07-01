# Use official PHP image with PostgreSQL PDO extension
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# Set permissions (optional, for development)
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

# Copy custom Apache config for .htaccess support (optional)
COPY ./apache.conf /etc/apache2/sites-available/000-default.conf

# Start Apache in foreground
CMD ["apache2-foreground"]