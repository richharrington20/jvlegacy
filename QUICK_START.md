# Quick Start Guide - Local Development

## Step 1: Install Composer

Composer is not currently installed. Choose one method:

### Method A: Homebrew (Easiest)
```bash
brew install composer
```

### Method B: Manual Installation
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
php -r "unlink('composer-setup.php');"
```

Verify:
```bash
composer --version
```

## Step 2: Install Dependencies

```bash
cd /Users/richcopestake/Documents/Rise/jvlegacy

# Install PHP packages
composer install

# Install Node packages
npm install
```

## Step 3: Setup Environment

```bash
# Create .env file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create storage link
php artisan storage:link
```

## Step 4: Configure Database

Edit `.env` file and add your database credentials:

```env
DB_LEGACY_HOST=127.0.0.1
DB_LEGACY_PORT=3306
DB_LEGACY_DATABASE=your_database_name
DB_LEGACY_USERNAME=your_username
DB_LEGACY_PASSWORD=your_password
```

## Step 5: Start Development Server

### Option 1: Full Development Environment (Recommended)
```bash
composer run dev
```

This runs:
- Laravel server (http://localhost:8000)
- Vite dev server (for assets)
- Queue worker
- Log viewer

### Option 2: Simple Server
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

## Access the Application

- **Main App:** http://localhost:8000
- **Investor Login:** http://localhost:8000/investor/login
- **Admin Panel:** http://localhost:8000/admin

## Troubleshooting

### "composer: command not found"
- Install Composer (see Step 1 above)

### "php: command not found"
- Install PHP: `brew install php`

### "npm: command not found"
- Install Node.js: `brew install node`

### Database Connection Errors
- Make sure MySQL/MariaDB is running
- Verify credentials in `.env`
- Test connection: `php artisan tinker` then `DB::connection('legacy')->getPdo();`

### Port 8000 Already in Use
```bash
php artisan serve --port=8001
```


