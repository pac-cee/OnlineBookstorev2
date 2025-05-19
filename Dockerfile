FROM php:8.1-apache

# Install necessary packages
RUN apt-get update \
 && apt-get install -y \
      default-mysql-server \
      dos2unix \
      unzip \
 && docker-php-ext-install mysqli pdo pdo_mysql \
 && a2enmod rewrite headers \
 && echo 'ServerName localhost' >> /etc/apache2/apache2.conf \
 && rm -rf /var/lib/apt/lists/*

# Copy application and SQL
COPY . /var/www/html
WORKDIR /var/www/html

# Normalize entrypoint, make executable
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN dos2unix /usr/local/bin/docker-entrypoint.sh \
 && chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy SQL setup for init
COPY setup.sql /docker-entrypoint-initdb.d/init.sql

# Ensure permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]