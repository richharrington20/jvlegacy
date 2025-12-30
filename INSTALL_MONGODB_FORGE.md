# Install MongoDB Extension on Forge Server

## Quick Fix - Update Deployment Script

The easiest solution is to add MongoDB extension installation to your deployment script.

### Step 1: Update Forge Deployment Script

In your Forge dashboard:

1. Go to your site → **"Deployment Script"**
2. **Add this at the very top** (before `composer install`):

```bash
# Install MongoDB extension if not present
if ! php -m | grep -q mongodb; then
    echo "Installing MongoDB extension..."
    sudo pecl install mongodb <<< "" 2>&1 | tee /tmp/mongodb_install.log
    if [ $? -eq 0 ]; then
        echo "extension=mongodb.so" | sudo tee /etc/php/8.4/cli/conf.d/20-mongodb.ini
        echo "extension=mongodb.so" | sudo tee /etc/php/8.4/fpm/conf.d/20-mongodb.ini
        sudo service php8.4-fpm restart
        echo "MongoDB extension installed successfully"
    else
        echo "MongoDB extension installation failed. Check /tmp/mongodb_install.log"
        exit 1
    fi
fi
```

3. **Save** the deployment script
4. **Deploy** - this will install the extension automatically

### Step 2: Verify Installation

After deployment, SSH to your server and verify:

```bash
php -m | grep mongodb
```

Should output: `mongodb`

## Alternative: Manual Installation via SSH

If the deployment script approach doesn't work:

### Step 1: SSH to Your Forge Server

```bash
# Get SSH details from Forge dashboard
ssh forge@your-server-ip
```

### Step 2: Install MongoDB Extension

```bash
# Install via PECL
sudo pecl install mongodb

# When prompted, just press Enter for default options
```

### Step 3: Enable Extension

```bash
# For CLI
echo "extension=mongodb.so" | sudo tee /etc/php/8.4/cli/conf.d/20-mongodb.ini

# For PHP-FPM (web requests)
echo "extension=mongodb.so" | sudo tee /etc/php/8.4/fpm/conf.d/20-mongodb.ini
```

### Step 4: Restart PHP-FPM

```bash
sudo service php8.4-fpm restart
```

### Step 5: Verify

```bash
php -m | grep mongodb
```

## Complete Deployment Script (Updated)

Here's a complete deployment script with MongoDB installation:

```bash
cd /home/forge/jvlegacy-eqqugdgf.on-forge.com

# Install MongoDB extension if not present
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

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Skip migrations (MongoDB doesn't use SQL migrations)
echo "Skipping migrations - MongoDB connection"

# Clear and cache config
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Troubleshooting

### If PECL Install Fails

You may need to install dependencies:

```bash
sudo apt-get update
sudo apt-get install -y php8.4-dev pkg-config libssl-dev
sudo pecl install mongodb
```

### If Extension Still Not Found

Check PHP configuration:

```bash
# Find PHP ini files
php --ini

# Check if extension file exists
find /usr -name "mongodb.so" 2>/dev/null

# If found, manually add to php.ini
# Edit: /etc/php/8.4/cli/php.ini
# Add: extension=/path/to/mongodb.so
```

### Test Connection After Installation

```bash
php artisan tinker
>>> DB::connection('legacy')->getMongoClient()->listDatabases();
```

## Next Steps

1. ✅ Update deployment script with MongoDB installation
2. ✅ Deploy (extension will install automatically)
3. ✅ Verify extension is loaded: `php -m | grep mongodb`
4. ✅ Test MongoDB connection in tinker
5. ✅ Update environment variables in Forge with MongoDB credentials


