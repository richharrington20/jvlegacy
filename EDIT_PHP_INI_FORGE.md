# Edit PHP.ini on Forge to Add MongoDB Extension

## Step 1: SSH into Your Forge Server

1. Go to your **Laravel Forge dashboard**
2. Click on your **server** (not the site)
3. Find the **SSH** section or **Server Details**
4. Copy the SSH command or credentials

Then run:
```bash
ssh forge@your-server-ip
```

Or if you have SSH keys set up:
```bash
ssh forge@your-server-hostname
```

## Step 2: Find PHP.ini Files

You need to edit **two** php.ini files:
- **CLI version** (for command line/artisan)
- **PHP-FPM version** (for web requests)

Find them:
```bash
# Find PHP CLI ini file
php --ini

# This will show something like:
# Configuration File (php.ini) Path: /etc/php/8.4/cli
# Loaded Configuration File: /etc/php/8.4/cli/php.ini
```

Common locations:
- CLI: `/etc/php/8.4/cli/php.ini`
- PHP-FPM: `/etc/php/8.4/fpm/php.ini`

## Step 3: Install MongoDB Extension (if not already installed)

First, check if the extension is installed:
```bash
php -m | grep mongodb
```

If it's not there, install it:
```bash
# Install MongoDB extension
sudo pecl install mongodb

# When prompted, just press Enter for default options
```

## Step 4: Find Where MongoDB Extension Was Installed

After installation, find the extension file:
```bash
# Find mongodb.so file
find /usr -name "mongodb.so" 2>/dev/null

# Common locations:
# /usr/lib/php/20220829/mongodb.so
# /usr/lib/php/20210902/mongodb.so
# /opt/homebrew/lib/php/pecl/20220829/mongodb.so
```

Note the full path - you'll need it.

## Step 5: Edit PHP.ini Files

### Option A: Add Extension Line (Recommended)

Edit both php.ini files:

```bash
# Edit CLI php.ini
sudo nano /etc/php/8.4/cli/php.ini

# Edit PHP-FPM php.ini
sudo nano /etc/php/8.4/fpm/php.ini
```

In each file:
1. Press `Ctrl+W` to search
2. Search for `extension=`
3. Find the extensions section (usually near the end)
4. Add this line:
   ```ini
   extension=mongodb.so
   ```
5. Save: `Ctrl+O`, then `Enter`
6. Exit: `Ctrl+X`

### Option B: Use Extension Directory (Alternative)

If you want to use the extension directory approach:

```bash
# Edit CLI php.ini
sudo nano /etc/php/8.4/cli/php.ini
```

Find the line:
```ini
;extension_dir = "ext"
```

Uncomment and set it (if needed):
```ini
extension_dir = "/usr/lib/php/20220829"
```

Then add:
```ini
extension=mongodb.so
```

### Option C: Create Separate .ini File (Easiest - Recommended)

Instead of editing php.ini directly, create a separate config file:

```bash
# For CLI
echo "extension=mongodb.so" | sudo tee /etc/php/8.4/cli/conf.d/20-mongodb.ini

# For PHP-FPM
echo "extension=mongodb.so" | sudo tee /etc/php/8.4/fpm/conf.d/20-mongodb.ini
```

This is the **cleanest approach** and easier to manage.

## Step 6: Restart PHP-FPM

After making changes, restart PHP-FPM:
```bash
sudo service php8.4-fpm restart
```

Or if that doesn't work:
```bash
sudo systemctl restart php8.4-fpm
```

## Step 7: Verify Installation

Test that the extension is loaded:

```bash
# Check CLI
php -m | grep mongodb

# Should output: mongodb

# Check PHP-FPM (via phpinfo)
php -r "phpinfo();" | grep mongodb
```

## Step 8: Test in Laravel

SSH into your server and test:
```bash
cd /home/forge/jvlegacy-eqqugdgf.on-forge.com/current

php artisan tinker
```

Then:
```php
>>> extension_loaded('mongodb')
// Should return: true

>>> DB::connection('legacy')->getMongoClient()->listDatabases();
// Should list your MongoDB databases
```

## Quick One-Liner (All Steps)

If you want to do it all at once:

```bash
# SSH to server first, then run:
sudo pecl install mongodb <<< ""
echo "extension=mongodb.so" | sudo tee /etc/php/8.4/cli/conf.d/20-mongodb.ini
echo "extension=mongodb.so" | sudo tee /etc/php/8.4/fpm/conf.d/20-mongodb.ini
sudo service php8.4-fpm restart
php -m | grep mongodb
```

## Troubleshooting

### If PECL Install Fails

You may need build tools:
```bash
sudo apt-get update
sudo apt-get install -y php8.4-dev pkg-config libssl-dev build-essential
sudo pecl install mongodb
```

### If Extension Not Found After Adding

1. **Check the path** - Make sure `mongodb.so` exists:
   ```bash
   find /usr -name "mongodb.so" 2>/dev/null
   ```

2. **Use full path** in php.ini:
   ```ini
   extension=/usr/lib/php/20220829/mongodb.so
   ```

3. **Check extension_dir**:
   ```bash
   php -i | grep extension_dir
   ```

### If Changes Don't Take Effect

1. **Restart PHP-FPM**:
   ```bash
   sudo service php8.4-fpm restart
   ```

2. **Clear Laravel config cache**:
   ```bash
   php artisan config:clear
   ```

3. **Check which php.ini is being used**:
   ```bash
   php --ini
   ```

## Alternative: Use Forge's PHP Extension Manager

Some Forge setups have a PHP extension manager in the dashboard:

1. Go to **Server** → **PHP** → **Extensions**
2. Look for MongoDB in the list
3. Click to install/enable

This is the easiest method if available.

## Verify Everything Works

After installation, test the full stack:

```bash
# 1. Check extension is loaded
php -m | grep mongodb

# 2. Test MongoDB connection
php artisan tinker
>>> DB::connection('legacy')->getMongoClient()->listDatabases();

# 3. Try a deployment
# Go to Forge dashboard and trigger a deployment
```

## Summary

**Easiest Method:**
```bash
ssh forge@your-server
sudo pecl install mongodb <<< ""
echo "extension=mongodb.so" | sudo tee /etc/php/8.4/cli/conf.d/20-mongodb.ini
echo "extension=mongodb.so" | sudo tee /etc/php/8.4/fpm/conf.d/20-mongodb.ini
sudo service php8.4-fpm restart
php -m | grep mongodb
```

This creates separate .ini files in the conf.d directory, which is cleaner than editing php.ini directly.


