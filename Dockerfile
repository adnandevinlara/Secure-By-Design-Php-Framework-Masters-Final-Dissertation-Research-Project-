# Use the official PHP 8 image with Apache
FROM php:8.2-apache

# Enable Apache mod_rewrite for our Router
RUN a2enmod rewrite

# Install the PDO MySQL extension for the ORM
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer inside the container
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory to our project root
WORKDIR /var/www/html