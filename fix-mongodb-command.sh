#!/bin/bash
# Fix MongoDB command registration

cd /Users/richcopestake/Documents/Rise/jvlegacy

echo "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Clear command cache if it exists
if [ -d "bootstrap/cache" ]; then
    rm -f bootstrap/cache/commands.php 2>/dev/null
    rm -f bootstrap/cache/config.php 2>/dev/null
fi

echo ""
echo "Verifying command exists..."
php artisan list | grep mongodb || echo "Command not found - checking file..."

echo ""
echo "Checking command file..."
if [ -f "app/Console/Commands/SetupMongoDB.php" ]; then
    echo "✅ Command file exists"
    echo ""
    echo "Try running: php artisan mongodb:setup"
else
    echo "❌ Command file not found!"
fi

