# Installing PHP MongoDB Extension

## The Problem

The `mongodb/laravel-mongodb` package requires the PHP MongoDB extension (`ext-mongodb`) which is not installed on your system.

## Solution Options

### Option 1: Install MongoDB Extension (Recommended for Full Development)

**On macOS with Homebrew:**

```bash
# Install MongoDB extension via PECL
pecl install mongodb

# If pecl is not found, install it first:
brew install autoconf
```

**Or via Homebrew PHP:**

```bash
# Check your PHP version
php -v

# Install extension for your PHP version (8.4 in your case)
brew install php@8.4-mongodb
# Or try:
pecl install mongodb
```

**Enable the extension:**

After installation, add this line to your PHP ini file:
```ini
extension=mongodb.so
```

Find your PHP ini file:
```bash
php --ini
```

Then edit the file (usually `/opt/homebrew/etc/php/8.4/php.ini`) and add:
```ini
extension=mongodb.so
```

**Verify installation:**
```bash
php -m | grep mongodb
```

### Option 2: Skip Extension Requirement (For Development Only)

If you just want to commit the changes and let Forge handle the MongoDB setup, you can temporarily ignore the requirement:

```bash
composer require mongodb/laravel-mongodb --ignore-platform-req=ext-mongodb
```

**Note:** This allows you to install the package locally, but MongoDB functionality won't work until the extension is installed. This is fine if you're just preparing code for deployment.

### Option 3: Use Alternative Package (Not Recommended)

There are other MongoDB packages, but `mongodb/laravel-mongodb` is the most maintained and recommended.

## For Forge Deployment

**Important:** Your Forge server will also need the MongoDB extension installed. You can:

1. **SSH into your Forge server** and install it:
   ```bash
   pecl install mongodb
   # Then add extension=mongodb.so to php.ini
   ```

2. **Or use Forge's server management** to install PHP extensions via the dashboard

3. **Or add to your deployment script:**
   ```bash
   # Check if extension exists, install if not
   php -m | grep -q mongodb || pecl install mongodb
   ```

## Quick Fix for Now

To proceed with development and deployment setup:

```bash
# Install package ignoring the extension requirement
composer require mongodb/laravel-mongodb --ignore-platform-req=ext-mongodb

# Update composer.lock
composer update --ignore-platform-req=ext-mongodb
```

This will let you commit and push the changes. The extension can be installed on Forge separately.

## Verify After Installation

Once the extension is installed:

```bash
php -m | grep mongodb
# Should output: mongodb
```

Then test in Laravel:
```bash
php artisan tinker
>>> DB::connection('legacy')->getMongoClient()->listDatabases();
```


