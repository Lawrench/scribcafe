FROM php:8.2-apache

# Install necessary PHP extensions and enable them
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-enable mysqli pdo pdo_mysql

# Enable Apache mod_rewrite for URL rewriting support
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html
