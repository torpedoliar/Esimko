#!/bin/bash

# Esimko Deployment Script for Ubuntu 22.04 (Simulation)
# Run this script on your fresh Ubuntu 22.04 VM as root or with sudo.
# Usage: sudo ./deploy-esimko.sh

set -e

echo "Starting Esimko Deployment Simulation Setup..."

# 1. Update System
echo "Updating system packages..."
apt-get update && apt-get upgrade -y

# 2. Install Prerequisites
echo "Installing software common properties..."
apt-get install -y software-properties-common curl git unzip zip

# 3. Add PHP 7.4 PPA (Required for Ubuntu 22.04)
if ! grep -q "ondrej/php" /etc/apt/sources.list /etc/apt/sources.list.d/*; then
    echo "Adding PHP 7.4 PPA..."
    add-apt-repository ppa:ondrej/php -y
    apt-get update
fi

# 4. Install Nginx, MySQL, PHP 7.4 and Extensions
echo "Installing Nginx, MySQL, and PHP 7.4..."
apt-get install -y nginx mysql-server \
    php7.4 php7.4-fpm php7.4-mysql php7.4-mbstring php7.4-xml php7.4-curl \
    php7.4-gd php7.4-json php7.4-zip php7.4-bcmath php7.4-intl php7.4-ldap

# 5. Install Node.js 16.x
echo "Installing Node.js 16.x..."
curl -fsSL https://deb.nodesource.com/setup_16.x | bash -
apt-get install -y nodejs

# 6. Install Composer
if ! [ -x "$(command -v composer)" ]; then
    echo "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# 7. Configure MySQL
echo "Configuring Database..."
# Note: In a real script, avoid hardcoding passwords. For simulation, this matches your Dockerfile props.
DB_NAME="esimko"
DB_USER="esimko"
DB_PASS="esimko"
sudo mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
sudo mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# 8. Setup Application
APP_DIR="/var/www/html/esimko"
# Assuming we are running this inside the VM and need to clone the repo or if this script is INSIDE the project folder
# For simulation, let's assume the user copies the project to $APP_DIR manually or we are in the root of the project.

echo "IMPORTANT: This script assumes you will copy your project files to $APP_DIR"
echo "If you are running this script FROM the project folder on the VM, we will copy current files."

if [ -f "composer.json" ]; then
    echo "Detected project files in current directory. Copying to $APP_DIR..."
    mkdir -p $APP_DIR
    cp -R . $APP_DIR
else
    echo "Project files not found in current directory."
    echo "Please manually copy your Laravel project to $APP_DIR after this script finishes."
    mkdir -p $APP_DIR
fi

# Fix Permissions primarily
echo "Setting permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

# 9. Configure Nginx
echo "Configuring Nginx..."
# Generate Self-Signed SSL if not exists
if [ ! -f /etc/ssl/certs/nginx-selfsigned.crt ]; then
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/nginx-selfsigned.key \
    -out /etc/ssl/certs/nginx-selfsigned.crt \
    -subj "/C=ID/ST=Jakarta/L=Jakarta/O=Esimko/OU=IT/CN=localhost"
fi

# Create Nginx Config
cat > /etc/nginx/sites-available/esimko <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name _; 
    root $APP_DIR/public;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }

    # SSL Configuration (Optional for local but good for simulation)
    listen 443 ssl;
    ssl_certificate /etc/ssl/certs/nginx-selfsigned.crt;
    ssl_certificate_key /etc/ssl/private/nginx-selfsigned.key;
}
EOF

# Enable Site
ln -sf /etc/nginx/sites-available/esimko /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl restart nginx

# 10. Install Project Dependencies (if files exist)
if [ -f "$APP_DIR/composer.json" ]; then
    echo "Installing project dependencies..."
    cd $APP_DIR
    
    # Copy .env
    if [ ! -f .env ]; then
        cp .env.example .env
        # Update DB credentials in .env
        sed -i "s/DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" .env
        sed -i "s/DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" .env
        sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" .env
    fi
    
    # PHP Dependencies
    sudo -u www-data composer install --no-interaction --optimize-autoloader --no-dev

    # Node Dependencies
    # Note: Running npm install as root is not recommended, but for simplicity in this script:
    # We will try to run as the current user if possible, or skip if difficult.
    # We'll skip npm install if running as root to avoid issues, or advise user to run it.
    echo "Running npm install & build as www-data..."
    # Allowing root for this simulation script to ensure it runs
    npm install --unsafe-perm
    npm run prod
    
    # Key Generate & Migrate
    php artisan key:generate
    php artisan storage:link
    php artisan config:cache
    
    echo "Running migrations..."
    php artisan migrate --force
    
    # Import backup if exists and DB is empty
    if [ -f "esimko_backup.sql" ]; then
        echo "Found backup file. You can manually import it with:"
        echo "mysql -u ${DB_USER} -p${DB_PASS} ${DB_NAME} < esimko_backup.sql"
    fi
fi

echo "Deployment Setup Complete!"
echo "Access your application at http://<VM_IP_ADDRESS>"
echo "If you haven't copied your project files yet, do so now to $APP_DIR"
