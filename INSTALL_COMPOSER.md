# Installing Composer on macOS

Composer is not currently installed on your system. Here are the easiest ways to install it:

## Option 1: Install via Homebrew (Recommended)

If you have Homebrew installed:

```bash
brew install composer
```

Then verify installation:
```bash
composer --version
```

## Option 2: Install Composer Globally (Manual)

1. Download the installer:
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
```

2. Verify the installer (optional but recommended):
```bash
php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
```

3. Run the installer:
```bash
php composer-setup.php
```

4. Move to a global location:
```bash
sudo mv composer.phar /usr/local/bin/composer
```

5. Clean up:
```bash
php -r "unlink('composer-setup.php');"
```

6. Verify installation:
```bash
composer --version
```

## Option 3: Install via Homebrew (if you don't have it)

First install Homebrew:
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

Then install Composer:
```bash
brew install composer
```

## After Installation

Once Composer is installed, you can proceed with the local setup:

```bash
cd /Users/richcopestake/Documents/Rise/jvlegacy

# Install PHP dependencies
composer install

# Generate application key
php artisan key:generate

# Install Node dependencies
npm install

# Start the development server
composer run dev
```

## Troubleshooting

### If PHP is not found:
Install PHP via Homebrew:
```bash
brew install php
```

### If you get permission errors:
You may need to add Composer to your PATH. Add this to your `~/.zshrc`:
```bash
export PATH="$HOME/.composer/vendor/bin:$PATH"
```

Then reload:
```bash
source ~/.zshrc
```

### Check PHP version:
```bash
php --version
```

You need PHP 8.2 or higher for this project.


