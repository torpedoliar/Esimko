# Use the official Ubuntu base image
FROM ubuntu:20.04

# Set non-interactive mode for package installation
ENV DEBIAN_FRONTEND=noninteractive

# Update and install essential packages
RUN apt-get update && apt-get install -y \
    nginx \
    mysql-server \
    openssh-server \
    php7.4 \
    php7.4-fpm \
    php7.4-mysql \
    php7.4-mbstring \
    php7.4-xml \
    php7.4-curl \
    php7.4-gd \
    php7.4-json \
    php7.4-zip \
    php7.4-ssh2 \
    php7.4-bcmath \
    php7.4-redis \
    php7.4-imagick \
    php7.4-intl \
    php7.4-ldap \
    php7.4-apcu \
    php7.4-xmlrpc \
    php7.4-dev \
    openssl \
    wget \
    unzip \
    zip \
    curl \
    && apt-get clean

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure PHP-FPM
RUN sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php/7.4/fpm/php.ini

# Generate a self-signed SSL certificate
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/nginx-selfsigned.key -out /etc/ssl/certs/nginx-selfsigned.crt -subj "/C=US/ST=State/L=City/O=Organization/OU=IT Department/CN=localhost"

# Create sites-available and sites-enabled directories if they don't exist
RUN mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled

# Create working directory - using /var/www/html to match Laravel config
RUN mkdir -p /var/www/html

# Set working directory
WORKDIR /var/www/html

# Copy Nginx config first
COPY nginx-default /etc/nginx/sites-available/default

# Remove the existing symbolic link if it exists
RUN rm -f /etc/nginx/sites-enabled/default

# Create a symbolic link to enable the site
RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# Configure SSH
RUN mkdir -p /var/run/sshd
RUN echo "root:password" | chpasswd
RUN sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config

# Configure MySQL untuk accept local connections
RUN mkdir -p /var/run/mysqld && chown mysql:mysql /var/run/mysqld

# Expose ports for Nginx (HTTP and HTTPS), SSH
EXPOSE 80
EXPOSE 443
EXPOSE 22

# Create startup script
COPY <<'EOF' /start.sh
#!/bin/bash
echo "Starting MySQL..."
service mysql start
sleep 5

# Setup database jika belum ada
echo "Setting up database..."
mysql -e "CREATE DATABASE IF NOT EXISTS esimko CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'esimko'@'localhost' IDENTIFIED BY 'esimko';" 2>/dev/null || true
mysql -e "GRANT ALL PRIVILEGES ON esimko.* TO 'esimko'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Check if database is empty and import if sql file exists
TABLE_COUNT=$(mysql -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='esimko';")
if [ "$TABLE_COUNT" -eq "0" ] && [ -f "/var/www/html/esimko_backup.sql" ]; then
    echo "Importing database..."
    mysql esimko < /var/www/html/esimko_backup.sql
    echo "Database imported successfully!"
fi

# Create storage directories
echo "Setting up storage directories..."
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Set permissions
chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/bootstrap/cache

echo "Starting services..."
service nginx start
service ssh start
service php7.4-fpm start

echo "All services started. Application ready at http://localhost:8080"
# Keep container running
tail -f /dev/null
EOF

RUN chmod +x /start.sh

CMD ["/start.sh"]
