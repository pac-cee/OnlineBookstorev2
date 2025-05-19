#!/usr/bin/env bash
set -e

# 1. If /var/lib/mysql/mysql doesn't exist, this is the first run → initialize + import schema
if [ ! -d "/var/lib/mysql/mysql" ]; then
  echo "Initializing MySQL data directory..."
  mysqld --initialize-insecure --user=mysql --datadir=/var/lib/mysql

  echo "Starting temporary MySQL server to load setup.sql..."
  mysqld_safe --datadir=/var/lib/mysql &

  # Wait for MySQL to be ready
  until mysqladmin ping --silent; do
    echo "Waiting for MySQL to start..."
    sleep 1
  done

  echo "Running schema script (setup.sql)..."
  mysql < /docker-entrypoint-initdb.d/init.sql

  echo "Resetting root password plugin and granting privileges..."
  #  a) Force root@localhost to use native_password with an empty password
  mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';"
  #  b) Also allow root@'%' (remote) with empty password and all privileges on bookapp
  mysql -e "CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY '';"
  mysql -e "GRANT ALL PRIVILEGES ON bookapp.* TO 'root'@'%';"
  mysql -e "FLUSH PRIVILEGES;"

  # Shutdown the temporary server once schema and grants are done
  mysqladmin shutdown
fi

# 2. Start the permanent MySQL server in the background
echo "Starting MySQL server..."
mysqld_safe --datadir=/var/lib/mysql &

# 3. Finally, launch Apache/PHP in the foreground
exec apache2-foreground
