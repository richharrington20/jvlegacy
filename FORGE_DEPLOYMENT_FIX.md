# Forge Deployment Fix - MongoDB Extension

## The Problem

The MongoDB PHP extension (`ext-mongodb`) is not installed on your Forge server, causing deployment to fail.

## Solution Options

### Option 1: Install MongoDB Extension on Forge Server (Recommended)

**SSH into your Forge server:**

```bash
# SSH to your server (get credentials from Forge)
ssh forge@your-server-ip

# Install MongoDB extension
sudo pecl install mongodb

# Enable the extension
echo "extension=mongodb.so" | sudo tee /etc/php/8.4/cli/conf.d/20-mongodb.ini
echo "extension=mongodb.so" | sudo tee /etc/php/8.4/fpm/conf.d/20-mongodb.ini

# Restart PHP-FPM
sudo service php8.4-fpm restart

# Verify installation
php -m | grep mongodb
```

### Option 2: Update Deployment Script (Quick Fix)

In your Forge dashboard:

1. Go to your site
2. Click **"Deployment Script"**
3. **Add this at the top** (before `composer install`):

```bash
# Install MongoDB extension if not present
if ! php -m | grep -q mongodb; then
    echo "Installing MongoDB extension..."
    sudo pecl install mongodb <<< ""
    echo "extension=mongodb.so" | sudo tee /etc/php/8.4/cli/conf.d/20-mongodb.ini
    echo "extension=mongodb.so" | sudo tee /etc/php/8.4/fpm/conf.d/20-mongodb.ini
    sudo service php8.4-fpm restart
fi
```

### Option 3: Use Composer Ignore Flag (Temporary)

Update your deployment script to ignore the platform requirement:

Find this line:
```bash
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
```

Replace with:
```bash
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --ignore-platform-req=ext-mongodb
```

**Warning:** This will allow installation but MongoDB won't work until the extension is installed.

## Recommended Approach

**Do both:**
1. Install the extension via SSH (Option 1) - this is the proper solution
2. Update deployment script to auto-install if missing (Option 2) - this prevents future issues

## Complete Deployment Script Example

Here's a complete deployment script that handles MongoDB:

```bash
cd /home/forge/jvlegacy-eqqugdgf.on-forge.com

# Install MongoDB extension if missing
if ! php -m | grep -q mongodb; then
    echo "Installing MongoDB extension..."
    sudo pecl install mongodb <<< "" || echo "MongoDB extension installation failed, continuing..."
    if [ -f /usr/lib/php/*/mongodb.so ]; then
        echo "extension=mongodb.so" | sudo tee /etc/php/8.4/cli/conf.d/20-mongodb.ini
        echo "extension=mongodb.so" | sudo tee /etc/php/8.4/fpm/conf.d/20-mongodb.ini
        sudo service php8.4-fpm restart
    fi
fi

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Run migrations (skip for MongoDB)
# php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Verify After Deployment

After successful deployment, test the connection:

```bash
# SSH to server
ssh forge@your-server

# Test MongoDB connection
php artisan tinker
>>> DB::connection('legacy')->getMongoClient()->listDatabases();
```

## Alternative: Use Forge's PHP Extension Manager

Some Forge setups have a PHP extension manager. Check your Forge dashboard for:
- **Server → PHP → Extensions** or similar
- Look for MongoDB in the list of available extensions


