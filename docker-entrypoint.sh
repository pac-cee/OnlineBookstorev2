#!/usr/bin/env bash
set -e

# 1. Initialize MySQL if first run
if [ ! -d "/var/lib/mysql/mysql" ]; then
  echo "Initializing MySQL data directory..."
  mysqld --initialize-insecure --user=mysql --datadir=/var/lib/mysql
  
  echo "Starting temporary MySQL server..."
  mysqld_safe --datadir=/var/lib/mysql &

  # Wait for MySQL socket
  until mysqladmin ping --silent; do
    echo "Waiting for MySQL to start..."
    sleep 1
  done

  echo "Running schema script..."
  mysql < /docker-entrypoint-initdb.d/init.sql

  # Shutdown temporary server
  mysqladmin shutdown
fi

# 2. Start permanent MySQL server in background
echo "Starting MySQL server..."
mysqld_safe --datadir=/var/lib/mysql &

# 3. Start Apache in foreground
exec apache2-foreground