#!/usr/bin/env bash
set -e

# 1. If /var/lib/mysql/mysql doesn't exist, run one-time initialization & schema import
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

  echo "Creating dedicated DB user 'appuser' for both localhost and any host..."
  # a) Create and grant appuser@'localhost'
  mysql -e "CREATE USER IF NOT EXISTS 'appuser'@'localhost' IDENTIFIED BY 'Euqificap12.';"
  mysql -e "GRANT ALL PRIVILEGES ON bookapp.* TO 'appuser'@'localhost';"

  # b) Create and grant appuser@'%'
  mysql -e "CREATE USER IF NOT EXISTS 'appuser'@'%' IDENTIFIED BY 'Euqificap12.';"
  mysql -e "GRANT ALL PRIVILEGES ON bookapp.* TO 'appuser'@'%';"

  mysql -e "FLUSH PRIVILEGES;"

  # Shutdown the temporary server, schema + user are now in place
  mysqladmin shutdown
fi

# 2. Start the permanent MySQL server in the background
echo "Starting MySQL server..."
mysqld_safe --datadir=/var/lib/mysql &

# 3. Finally, launch Apache/PHP in the foreground
exec apache2-foreground
