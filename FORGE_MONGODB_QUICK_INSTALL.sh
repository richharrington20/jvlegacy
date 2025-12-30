#!/bin/bash
# Quick MongoDB Extension Installation Script for Forge
# Run this via SSH on your Forge server

set -e

echo "🔍 Checking if MongoDB extension is already installed..."
if php -m | grep -q mongodb; then
    echo "✅ MongoDB extension is already installed!"
    exit 0
fi

echo "📦 Installing MongoDB extension via PECL..."
sudo pecl install mongodb <<< ""

if [ $? -ne 0 ]; then
    echo "❌ PECL installation failed. Installing dependencies..."
    sudo apt-get update
    sudo apt-get install -y php8.4-dev pkg-config libssl-dev build-essential
    sudo pecl install mongodb <<< ""
fi

# Find the extension file
MONGODB_SO=$(find /usr -name "mongodb.so" 2>/dev/null | head -1)

if [ -z "$MONGODB_SO" ]; then
    echo "❌ Could not find mongodb.so file"
    exit 1
fi

echo "✅ Found MongoDB extension at: $MONGODB_SO"

# Add to CLI
echo "📝 Adding to CLI php.ini..."
echo "extension=mongodb.so" | sudo tee /etc/php/8.4/cli/conf.d/20-mongodb.ini

# Add to PHP-FPM
echo "📝 Adding to PHP-FPM php.ini..."
echo "extension=mongodb.so" | sudo tee /etc/php/8.4/fpm/conf.d/20-mongodb.ini

# Restart PHP-FPM
echo "🔄 Restarting PHP-FPM..."
sudo service php8.4-fpm restart || sudo systemctl restart php8.4-fpm

# Verify
echo "✅ Verifying installation..."
if php -m | grep -q mongodb; then
    echo "✅ MongoDB extension successfully installed and loaded!"
    echo ""
    echo "Test in Laravel:"
    echo "  php artisan tinker"
    echo "  >>> extension_loaded('mongodb')"
    echo "  >>> DB::connection('legacy')->getMongoClient()->listDatabases();"
else
    echo "❌ Extension installed but not loading. Check php.ini files."
    echo "Run: php --ini"
    exit 1
fi


