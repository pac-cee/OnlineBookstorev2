# Deployment Guide: OnlineBookstore Web Application

---

## 1. Prerequisites
- Web server (Apache/Nginx) with PHP 7.4+
- MySQL 5.7+ database
- Composer (optional, for dependencies)
- Git (for version control)

## 2. Environment Variables & Configuration
- Copy `config.sample.php` to `config.php` and set DB credentials, secret keys, etc.
- Set up environment variables for sensitive data (DB credentials, API keys).

## 3. Database Setup
- Import `setup.sql` into your MySQL database:
  ```sh
  mysql -u <user> -p <database> < setup.sql
  ```
- Ensure the DB user has appropriate privileges.

## 4. File Structure
- `/books/` and `/audio/` directories must be writable by the web server for uploads.
- Place cover images in `/covers/`.

## 5. Installing Dependencies
- (Optional) Run `composer install` if using Composer for PHP dependencies.
- (Optional) Run `npm install` if using any Node.js tooling.

## 6. Running the Application
- Deploy files to the web root (e.g., `/var/www/html/OnlineBookstore`).
- Access the app via your browser at `http://localhost/OnlineBookstore`.

## 7. Security & Permissions
- Set correct file permissions (e.g., `chmod 755` for directories, `chmod 644` for files).
- Restrict direct access to sensitive files (e.g., `.env`, `config.php`).
- Use HTTPS in production.

## 8. Post-Deployment
- Test registration, login, book upload, and ordering.
- Monitor logs for errors.
- Set up regular database and file backups.

## 9. Troubleshooting
- Check server logs for PHP or DB errors.
- Verify DB connection settings in `config.php`.
- Ensure all directories are writable as needed.

---

For further help, see [README.md](README.md) or contact the maintainer.
