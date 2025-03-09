#!/bin/bash

# Exit on error
set -e

echo "Starting server setup..."

# Update system
apt-get update
apt-get upgrade -y

# Install required packages
apt-get install -y \
    nginx \
    mysql-server \
    redis-server \
    supervisor \
    certbot \
    python3-certbot-nginx \
    zip \
    unzip \
    git \
    curl \
    software-properties-common

# Install PHP and extensions
add-apt-repository ppa:ondrej/php -y
apt-get update
apt-get install -y \
    php8.1-fpm \
    php8.1-cli \
    php8.1-mysql \
    php8.1-pgsql \
    php8.1-sqlite3 \
    php8.1-gd \
    php8.1-curl \
    php8.1-imap \
    php8.1-mbstring \
    php8.1-xml \
    php8.1-zip \
    php8.1-bcmath \
    php8.1-soap \
    php8.1-intl \
    php8.1-readline \
    php8.1-ldap \
    php8.1-msgpack \
    php8.1-igbinary \
    php8.1-redis \
    php8.1-swoole \
    php8.1-memcached

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Configure PHP
echo "Configuring PHP..."
sed -i 's/memory_limit = .*/memory_limit = 512M/' /etc/php/8.1/fpm/php.ini
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 64M/' /etc/php/8.1/fpm/php.ini
sed -i 's/post_max_size = .*/post_max_size = 64M/' /etc/php/8.1/fpm/php.ini
sed -i 's/max_execution_time = .*/max_execution_time = 180/' /etc/php/8.1/fpm/php.ini

# Configure MySQL
echo "Configuring MySQL..."
mysql_secure_installation

# Configure Redis
echo "Configuring Redis..."
sed -i 's/# maxmemory <bytes>/maxmemory 512mb/' /etc/redis/redis.conf
sed -i 's/# maxmemory-policy noeviction/maxmemory-policy allkeys-lru/' /etc/redis/redis.conf

# Configure Nginx
echo "Configuring Nginx..."
rm /etc/nginx/sites-enabled/default
cp nginx.conf /etc/nginx/sites-available/kenyotechcafm
ln -s /etc/nginx/sites-available/kenyotechcafm /etc/nginx/sites-enabled/

# Configure Supervisor
echo "Configuring Supervisor..."
cp supervisor.conf /etc/supervisor/conf.d/kenyotechcafm.conf

# Create application directory
mkdir -p /var/www/kenyotechcafm
chown -R www-data:www-data /var/www/kenyotechcafm

# Set up SSL with Let's Encrypt
echo "Setting up SSL..."
certbot --nginx -d your-domain.com --non-interactive --agree-tos --email admin@your-domain.com

# Start services
systemctl restart php8.1-fpm
systemctl restart nginx
systemctl restart redis-server
systemctl restart supervisor

# Enable services on boot
systemctl enable nginx
systemctl enable php8.1-fpm
systemctl enable mysql
systemctl enable redis-server
systemctl enable supervisor

echo "Server setup completed!" 