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

# 2. Copy application code and SQL setup
COPY . /var/www/html
WORKDIR /var/www/html

# 3. Copy and normalize entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN dos2unix /usr/local/bin/docker-entrypoint.sh \
 && chmod +x /usr/local/bin/docker-entrypoint.sh

# 4. Place setup.sql into the MySQL init directory
COPY setup.sql /docker-entrypoint-initdb.d/init.sql

# 5. Ensure www-data owns the app (permissions)
RUN chown -R www-data:www-data /var/www/html

# 6. Expose port 80 (HTTP)
EXPOSE 80

# 7. Use our custom entrypoint:
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
#    Then run Apache in the foreground:
CMD ["apache2-foreground"]
