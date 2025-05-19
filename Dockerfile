# Use the official PHP image with Apache
FROM php:8.1-apache

# Install PHP extensions for MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache rewrite module (if you use .htaccess)
RUN a2enmod rewrite

# Copy all your app code into the Apache web root
COPY . /var/www/html/

# Fix permissions so Apache can serve and write files
RUN chown -R www-data:www-data /var/www/html

# Expose container port 80 to the outside
EXPOSE 80

# Run Apache in the foreground
CMD ["apache2-foreground"]
