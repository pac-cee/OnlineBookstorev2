# Dockerfile (project root)

FROM php:8.1-apache

# 1. Install MySQL server, dos2unix, PHP extensions & Apache mods
RUN apt-get update \
 && apt-get install -y \
      default-mysql-server \
      dos2unix \
      unzip \
 && docker-php-ext-install mysqli pdo pdo_mysql \
 && a2enmod rewrite headers \
 && echo 'ServerName localhost' >> /etc/apache2/apache2.conf \
 && rm -rf /var/lib/apt/lists/*

# 2. Copy your application code and SQL schema
COPY . /var/www/html
WORKDIR /var/www/html

# 3. Copy and normalize the entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN dos2unix /usr/local/bin/docker-entrypoint.sh \
 && chmod +x /usr/local/bin/docker-entrypoint.sh

# 4. Place setup.sql into MySQL’s init directory
COPY setup.sql /docker-entrypoint-initdb.d/init.sql

# 5. Ensure the www-data user owns the app
RUN chown -R www-data:www-data /var/www/html

# 6. Expose port 80
EXPOSE 80

# 7. Use our custom entrypoint (runs MySQL init + starts both services)
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
