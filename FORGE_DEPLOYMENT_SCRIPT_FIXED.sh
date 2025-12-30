#!/bin/bash
# Fixed Forge Deployment Script with MongoDB Extension Installation

# Install MongoDB extension FIRST (before composer install)
if ! php -m | grep -q mongodb; then
    echo "Installing MongoDB extension..."
    sudo pecl install mongodb <<< "" 2>&1 | tee /tmp/mongodb_install.log
    if [ $? -eq 0 ]; then
        echo "extension=mongodb.so" | sudo tee /etc/php/8.4/cli/conf.d/20-mongodb.ini
        echo "extension=mongodb.so" | sudo tee /etc/php/8.4/fpm/conf.d/20-mongodb.ini
        sudo service php8.4-fpm restart
        echo "✅ MongoDB extension installed"
    else
        echo "❌ MongoDB extension installation failed"
        cat /tmp/mongodb_install.log
        exit 1
    fi
fi

# Now proceed with normal deployment
$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader
$FORGE_PHP artisan optimize
$FORGE_PHP artisan storage:link

# Skip migrations for MongoDB (or make them conditional)
# $FORGE_PHP artisan migrate --force
echo "Skipping migrations - MongoDB connection"

npm ci || npm install && npm run build

$ACTIVATE_RELEASE()

$RESTART_QUEUES()


