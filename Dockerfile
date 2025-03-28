FROM php:8.2-apache

# Install system dependencies and MySQL driver
RUN apt-get update && \
    apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_mysql zip \
    && a2enmod rewrite

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy application files
COPY ./src/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Health check
HEALTHCHECK --interval=5s --timeout=3s \
    CMD curl -f http://localhost/ || exit 1
