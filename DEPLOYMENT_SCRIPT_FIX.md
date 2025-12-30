# Fix Deployment Script - MongoDB Extension Order

## The Problem

The MongoDB extension installation code is at the **END** of your deployment script, but Composer runs at the **BEGINNING**. Composer checks for the extension during installation, so it fails before the extension is installed.

## The Solution

**Move the MongoDB extension installation to the VERY TOP** of your deployment script, before `$FORGE_COMPOSER install`.

## Updated Deployment Script

Replace your entire deployment script with this:

```bash
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
```

## Key Changes

1. ✅ **MongoDB extension installation moved to TOP** - before `$CREATE_RELEASE()`
2. ✅ **Migrations commented out** - MongoDB doesn't use SQL migrations
3. ✅ **Extension check happens first** - before Composer runs

## Alternative: Install Extension Manually First

If PECL installation is slow or problematic, you can install it manually via SSH first, then the deployment script will just verify it exists:

```bash
# SSH to server
ssh forge@your-server

# Install once
sudo pecl install mongodb
echo "extension=mongodb.so" | sudo tee /etc/php/8.4/cli/conf.d/20-mongodb.ini
echo "extension=mongodb.so" | sudo tee /etc/php/8.4/fpm/conf.d/20-mongodb.ini
sudo service php8.4-fpm restart

# Verify
php -m | grep mongodb
```

Then your deployment script can just check if it exists (the `if ! php -m | grep -q mongodb` check will pass).

## If PECL Install Fails

You may need to install dependencies first. Add this before `pecl install`:

```bash
# Install build dependencies
sudo apt-get update
sudo apt-get install -y php8.4-dev pkg-config libssl-dev
```

## Complete Script with Dependencies

If PECL fails, use this enhanced version:

```bash
# Install MongoDB extension FIRST (before composer install)
if ! php -m | grep -q mongodb; then
    echo "Installing MongoDB extension..."
    
    # Install dependencies if needed
    if ! command -v pecl &> /dev/null || ! php -m | grep -q "pdo"; then
        sudo apt-get update
        sudo apt-get install -y php8.4-dev pkg-config libssl-dev
    fi
    
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

# Rest of deployment script...
```


