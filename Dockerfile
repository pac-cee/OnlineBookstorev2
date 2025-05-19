FROM php:8.1-apache
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite

# Install Composer & your PHP deps
COPY composer.json composer.lock /var/www/html/
RUN apt-get update \
 && apt-get install -y unzip \
 && php -r "copy('https://getcomposer.org/installer','composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && cd /var/www/html \
 && composer install --no-dev --optimize-autoloader

# Copy rest of app
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
CMD ["apache2-foreground"]
