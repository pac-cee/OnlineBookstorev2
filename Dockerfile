FROM php:8.1-apache

# Install PHP extensions and enable mod_rewrite
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite

# Copy the entire app first
COPY . /var/www/html
WORKDIR /var/www/html

# Install Composer if composer.json is present, then install deps
RUN apt-get update \
    && apt-get install -y unzip \
    && php -r "copy('https://getcomposer.org/installer','composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader; fi \
    && rm composer-setup.php

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
