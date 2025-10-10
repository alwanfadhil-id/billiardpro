# Deployment Guide: BilliardPro to Ubuntu 22.04 VPS

This guide provides step-by-step instructions for deploying your Laravel BilliardPro application to an Ubuntu 22.04 VPS server.

## Prerequisites

- Ubuntu 22.04 server with sudo access
- Domain name pointing to your server IP
- Basic knowledge of Linux command line

## 1. System Updates and Security

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install essential packages
sudo apt install -y curl wget git unzip build-essential
```

## 2. Install and Configure LEMP Stack

### 2.1 Install PHP 8.2 and Extensions

```bash
# Add PHP repository
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.2 and required extensions
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-zip 
sudo apt install -y php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml 
sudo apt install -y php8.2-bcmath php8.2-json php8.2-redis php8.2-soap
sudo apt install -y php8.2-intl php8.2-readline php8.2-pdo php8.2-sqlite3
```

### 2.2 Install MySQL Database

```bash
# Install MySQL
sudo apt install -y mysql-server

# Secure MySQL installation
sudo mysql_secure_installation
```

For the secure installation, use these recommended options:
- Set root password: Yes
- Remove anonymous users: Yes
- Disallow root login remotely: Yes
- Remove test database: Yes
- Reload privilege tables: Yes

### 2.3 Install Nginx Web Server

```bash
# Install Nginx
sudo apt install -y nginx

# Start and enable Nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Check status (should show active)
sudo systemctl status nginx
```

## 3. Configure Database

```bash
# Login to MySQL as root
sudo mysql -u root -p

# Create database and user for your application
CREATE DATABASE billiardpro;
CREATE USER 'billiard_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON billiardpro.* TO 'billiard_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 4. Deploy Application Code

### 4.1 Clone or Upload Application

```bash
# Navigate to web root directory
cd /var/www

# Option 1: Clone from Git repository
sudo git clone https://github.com/yourusername/billiardpro.git html

# Option 2: Upload files manually via SFTP
# Then rename the directory to 'html'
sudo mv /path/to/uploaded/files html
```

### 4.2 Set Proper Permissions

```bash
# Change ownership to www-data
sudo chown -R www-data:www-data /var/www/html

# Set proper permissions
sudo chmod -R 755 /var/www/html
sudo chmod -R 775 /var/www/html/storage
sudo chmod -R 775 /var/www/html/bootstrap/cache
```

### 4.3 Install PHP Dependencies

```bash
# Navigate to application directory
cd /var/www/html

# Install Composer (if not already installed)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install PHP dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader
```

### 4.4 Configure Environment

```bash
# Copy example environment file
sudo -u www-data cp .env.example .env

# Generate application key
sudo -u www-data php artisan key:generate

# Edit environment file with your settings
sudo nano .env
```

Update your .env file with appropriate settings:

```env
APP_NAME=BilliardPro
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=billiardpro
DB_USERNAME=billiard_user
DB_PASSWORD=your_secure_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 4.5 Run Migrations and Seeders

```bash
# Run database migrations
sudo -u www-data php artisan migrate --force

# (Optional) Run seeders if you have initial data
sudo -u www-data php artisan db:seed --force
```

## 5. Configure Nginx Virtual Host

### 5.1 Create Nginx Server Block

```bash
# Create server block configuration
sudo nano /etc/nginx/sites-available/billiardpro
```

Add the following configuration (replace `yourdomain.com` with your actual domain):

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/html/public;
    index index.php index.html index.htm;

    # Handle Laravel pretty URLs
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Logging
    access_log /var/log/nginx/billiardpro_access.log;
    error_log /var/log/nginx/billiardpro_error.log;
}
```

### 5.2 Enable the Site

```bash
# Create symbolic link to enable the site
sudo ln -s /etc/nginx/sites-available/billiardpro /etc/nginx/sites-enabled/

# Remove default site to avoid conflicts
sudo rm /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

## 6. Setup SSL with Let's Encrypt

### 6.1 Install Certbot

```bash
# Install Certbot and Nginx plugin
sudo apt install -y certbot python3-certbot-nginx
```

### 6.2 Obtain SSL Certificate

```bash
# Obtain certificate (replace with your domain)
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Follow the prompts:
# - Enter your email address
# - Accept terms of service
# - Choose whether to share email with EFF (optional)
# - Choose option 2 to redirect all HTTP traffic to HTTPS
```

### 6.3 Test SSL Renewal

```bash
# Test automatic certificate renewal
sudo certbot renew --dry-run
```

## 7. Configure PHP Settings

### 7.1 Adjust PHP Configuration

```bash
# Edit PHP-FPM pool configuration
sudo nano /etc/php/8.2/fpm/pool.d/www.conf

# Find and modify these lines:
# user = www-data
# group = www-data
# listen.owner = www-data
# listen.group = www-data
# listen.mode = 0660
```

### 7.2 Adjust PHP INI Settings

```bash
# Edit PHP configuration
sudo nano /etc/php/8.2/fpm/php.ini

# Recommended settings for Laravel:
memory_limit = 512M
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 600
max_input_vars = 3000
```

### 7.3 Restart PHP-FPM

```bash
# Restart PHP-FPM service
sudo systemctl restart php8.2-fpm

# Restart Nginx
sudo systemctl restart nginx
```

## 8. Setup Automated Database Backup

### 8.1 Schedule Backup Command

The BilliardPro application includes a backup command. Create a cron job to run it nightly:

```bash
# Edit crontab for root user
sudo crontab -e
```

Add the following line to run database backup daily at 2 AM:

```bash
# Daily database backup at 2:00 AM
0 2 * * * cd /var/www/html && php artisan backup:database >> /var/log/billiardpro-backup.log 2>&1
```

### 8.2 Create Log Rotation

```bash
sudo nano /etc/logrotate.d/billiardpro
```

Add:

```
/var/log/billiardpro-backup.log {
    daily
    missingok
    rotate 52
    compress
    notifempty
    create 644 root root
}
```

## 9. Setup Queue Worker (Optional)

If using queue functionality, set up a queue worker:

```bash
# Edit crontab to schedule the queue worker
sudo crontab -e
```

Add:

```bash
# Laravel scheduler (required for queue:work to restart if it stops)
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

## 10. Final Security and Performance

### 10.1 Install Redis (for caching and queues)

```bash
# Install Redis server
sudo apt install -y redis-server

# Start and enable Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server
```

### 10.2 Optimize Laravel

```bash
# Navigate to application directory
cd /var/www/html

# Cache configuration, routes, and views
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# For development, if you need to clear cache later:
# php artisan config:clear
# php artisan route:clear
# php artisan view:clear
```

### 10.3 Set Up Fail2Ban (Optional Security Enhancement)

```bash
# Install fail2ban
sudo apt install -y fail2ban

# Create local configuration
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local

# Edit the local configuration
sudo nano /etc/fail2ban/jail.local
```

Ensure the sshd section is enabled:

```
[sshd]
enabled = true
```

Then restart fail2ban:

```bash
sudo systemctl restart fail2ban
```

## 11. Maintenance and Monitoring

### 11.1 Application Maintenance

```bash
# To update the application in the future:
cd /var/www/html

# Pull latest code (if using Git)
sudo -u www-data git pull origin main

# Update dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader

# Run migrations if any new ones exist
sudo -u www-data php artisan migrate

# Clear and re-cache configurations
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### 11.2 Monitor Application

```bash
# Check Nginx status
sudo systemctl status nginx

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check MySQL status
sudo systemctl status mysql

# Monitor logs
sudo tail -f /var/log/nginx/billiardpro_error.log
sudo tail -f /var/log/nginx/billiardpro_access.log
```

## 12. Troubleshooting

### Common Issues:

1. **Permission Error**:
   ```bash
   sudo chown -R www-data:www-data /var/www/html
   sudo chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
   ```

2. **500 Internal Server Error**:
   - Check `/var/log/nginx/billiardpro_error.log`
   - Ensure `.env` file exists and has correct permissions
   - Verify that `storage` and `bootstrap/cache` directories are writable

3. **Database Connection Error**:
   - Verify database credentials in `.env` file
   - Check MySQL service status: `sudo systemctl status mysql`

4. **SSL Certificate Renewal**:
   - Test renewal: `sudo certbot renew --dry-run`
   - Check cron job: `sudo crontab -l`

## 13. SSL Certificate Auto-Renewal

Let's Encrypt certificates are valid for 90 days. The certbot package sets up a systemd timer to automatically renew certificates, but you can verify it works:

```bash
# Check if the timer is active
sudo systemctl status certbot.timer

# List all timers
sudo systemctl list-timers | grep certbot
```

Your BilliardPro application should now be successfully deployed on your Ubuntu 22.04 VPS with Nginx, PHP 8.2, MySQL, SSL certificate, and automated backup configured. The application is secure, optimized, and ready for production use.