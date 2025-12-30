# Fix: Missing vendor/autoload.php

## The Problem

You're getting this error:
```
Failed opening required '/Users/richcopestake/Documents/Rise/jvlegacy/vendor/autoload.php'
```

This means the `vendor` directory doesn't exist because Composer dependencies haven't been installed.

## The Solution

Run this command in your terminal:

```bash
cd /Users/richcopestake/Documents/Rise/jvlegacy
composer install
```

**This will take a few minutes** - Composer needs to download all PHP packages.

## What to Expect

When you run `composer install`, you should see:
- Progress bars showing package downloads
- Messages like "Installing dependencies from lock file"
- Eventually: "Generating optimized autoload files"
- Finally: "Package operations: X installs, 0 updates, 0 removals"

## After composer install completes:

1. **Verify vendor directory exists:**
   ```bash
   ls -la vendor/
   ```
   You should see the `vendor` directory with many subdirectories.

2. **Setup environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan storage:link
   ```

3. **Start the server:**
   ```bash
   php artisan serve
   ```

## If composer install fails:

### Check PHP version:
```bash
php --version
```
You need PHP 8.2 or higher.

### Check Composer:
```bash
composer --version
```

### Common Issues:

**Memory limit error:**
```bash
php -d memory_limit=-1 /opt/homebrew/bin/composer install
```

**Network/timeout issues:**
```bash
composer install --no-interaction --prefer-dist
```

**Clear Composer cache:**
```bash
composer clear-cache
composer install
```

## Quick Check Commands

Run these to verify your setup:

```bash
# Check if vendor exists
test -d vendor && echo "✅ vendor exists" || echo "❌ Run: composer install"

# Check if .env exists  
test -f .env && echo "✅ .env exists" || echo "❌ Run: cp .env.example .env"

# Check PHP version
php --version

# Check Composer
composer --version
```


