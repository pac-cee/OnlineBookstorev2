#!/usr/bin/env bash
set -e

# 1. If /var/lib/mysql/mysql doesn't exist, this is the first run → initialize + import schema
if [ ! -d "/var/lib/mysql/mysql" ]; then
  echo "Initializing MySQL data directory..."
  mysqld --initialize-insecure --user=mysql --datadir=/var/lib/mysql

  echo "Starting temporary MySQL server to load setup.sql..."
  mysqld_safe --datadir=/var/lib/mysql &

  # Wait for the MySQL socket to appear (i.e., server is ready)
  until mysqladmin ping --silent; do
    echo "Waiting for MySQL to start..."
    sleep 1
  done

  echo "Running schema script (setup.sql)..."
  mysql < /docker-entrypoint-initdb.d/init.sql

  # Shutdown the temporary server once schema is loaded
  mysqladmin shutdown
fi

# 2. Now start the permanent MySQL server in the background
echo "Starting MySQL server..."
mysqld_safe --datadir=/var/lib/mysql &

# 3. Finally, launch Apache (PHP) in the foreground
exec apache2-foreground
