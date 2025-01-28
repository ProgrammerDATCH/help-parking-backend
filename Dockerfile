# Use the official PHP image
FROM php:8.1-apache

# Install extensions (like mysqli for MySQL)
RUN docker-php-ext-install mysqli

# Copy the website files to the Apache web root
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html