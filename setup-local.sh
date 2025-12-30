#!/bin/bash

# Laravel Local Development Server Setup Script
# This script sets up the local development environment

set -e

echo "🚀 Setting up Laravel local development server..."
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "✅ .env file created"
    else
        echo "❌ .env.example not found. Please create .env manually."
        exit 1
    fi
else
    echo "✅ .env file already exists"
fi

# Install PHP dependencies
echo ""
echo "📦 Installing PHP dependencies (Composer)..."
if command -v composer &> /dev/null; then
    composer install --no-interaction
    echo "✅ Composer dependencies installed"
else
    echo "❌ Composer not found. Please install Composer first."
    exit 1
fi

# Generate application key if not set
echo ""
echo "🔑 Generating application key..."
php artisan key:generate --ansi || echo "⚠️  Key generation skipped (may already be set)"

# Install Node dependencies
echo ""
echo "📦 Installing Node dependencies..."
if command -v npm &> /dev/null; then
    npm install
    echo "✅ Node dependencies installed"
else
    echo "❌ npm not found. Please install Node.js first."
    exit 1
fi

# Create storage links
echo ""
echo "🔗 Creating storage symlinks..."
php artisan storage:link || echo "⚠️  Storage link may already exist"

# Clear and cache config
echo ""
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo ""
echo "✅ Setup complete!"
echo ""
echo "📋 Next steps:"
echo "1. Update your .env file with database credentials:"
echo "   - DB_LEGACY_HOST=127.0.0.1"
echo "   - DB_LEGACY_PORT=3306"
echo "   - DB_LEGACY_DATABASE=your_database_name"
echo "   - DB_LEGACY_USERNAME=your_username"
echo "   - DB_LEGACY_PASSWORD=your_password"
echo ""
echo "2. Run migrations (if needed):"
echo "   php artisan migrate"
echo ""
echo "3. Start the development server:"
echo "   php artisan serve"
echo ""
echo "   Or use the dev script (includes Vite, queue, and logs):"
echo "   composer run dev"
echo ""
echo "4. In another terminal, start Vite (if not using composer run dev):"
echo "   npm run dev"
echo ""


