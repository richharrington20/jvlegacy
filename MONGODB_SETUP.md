# MongoDB Setup Guide

## Overview

The application has been migrated from MySQL to MongoDB on DigitalOcean. This guide explains the setup and configuration.

## Installation Steps

### 1. Install MongoDB Package

Run this command to install the MongoDB package for Laravel:

```bash
composer require mongodb/laravel-mongodb
```

### 2. Update Environment Variables

Add these to your `.env` file (on Forge, update via the Forge dashboard):

```env
# MongoDB Legacy Database Connection
DB_LEGACY_DSN=mongodb+srv://doadmin:09u46Y7c53MbQ1Fe@db-mongodb-lon1-92389-63170cb8.mongo.ondigitalocean.com/admin?tls=true&authSource=admin
DB_LEGACY_HOST=db-mongodb-lon1-92389-63170cb8.mongo.ondigitalocean.com
DB_LEGACY_PORT=27017
DB_LEGACY_DATABASE=admin
DB_LEGACY_USERNAME=doadmin
DB_LEGACY_PASSWORD=09u46Y7c53MbQ1Fe
DB_LEGACY_AUTHENTICATION_DATABASE=admin
```

**For Forge Deployment:**
- Go to your site's Environment tab
- Add/update these variables
- Make sure to set `DB_LEGACY_PASSWORD` as a secret/hidden variable

### 3. Update Composer Lock File

After adding the package, update the lock file:

```bash
composer update mongodb/laravel-mongodb
```

Then commit and push:

```bash
git add composer.json composer.lock
git commit -m "Add MongoDB support for legacy database"
git push
```

### 4. Publish MongoDB Configuration (Optional)

```bash
php artisan vendor:publish --provider="MongoDB\Laravel\MongoDBServiceProvider"
```

## Important Notes

### Migrations

**MongoDB is a NoSQL database**, which means:
- Traditional SQL migrations won't work the same way
- Schema changes are handled differently
- You may need to disable migrations for the legacy connection or convert them

### Disable Migrations for Legacy Connection

If migrations are failing, you can:

1. **Skip migrations on deployment** - Update your Forge deployment script to skip migrations
2. **Convert to MongoDB operations** - Rewrite migrations to use MongoDB operations
3. **Use a separate connection for new tables** - Keep new Laravel tables in a separate database

### Update Deployment Script

In Forge, you might want to update the deployment script to skip migrations or handle them differently:

```bash
# Instead of: php artisan migrate
# Use: php artisan migrate --database=mysql  # for new tables only
# Or skip migrations entirely if using MongoDB for everything
```

## Testing the Connection

Test the MongoDB connection:

```bash
php artisan tinker
```

Then in tinker:
```php
DB::connection('legacy')->getMongoClient()->listDatabases();
```

Or test with a simple query:
```php
// This will vary based on your models
DB::connection('legacy')->collection('your_collection')->first();
```

## Model Updates

If you have models using the legacy connection, you may need to update them:

```php
use MongoDB\Laravel\Eloquent\Model;

class YourModel extends Model
{
    protected $connection = 'legacy';
    protected $collection = 'your_collection_name';
}
```

## Troubleshooting

### Connection Errors

1. **Check DSN format** - MongoDB connection strings use a specific format
2. **Verify credentials** - Make sure username/password are correct
3. **Check network access** - Ensure your server IP is whitelisted in DigitalOcean
4. **TLS/SSL** - MongoDB Atlas/DigitalOcean requires TLS, make sure it's enabled

### Migration Errors

If migrations fail:
- MongoDB doesn't use SQL, so ALTER TABLE statements won't work
- Consider disabling migrations for the legacy connection
- Or convert migrations to MongoDB operations

### Package Installation Issues

If `composer require mongodb/laravel-mongodb` fails:
- Make sure PHP MongoDB extension is installed: `pecl install mongodb`
- Or use a package that doesn't require the extension (less common)

## Security Notes

- Never commit `.env` files with passwords
- Use Forge's environment variable management
- Rotate passwords regularly
- Use MongoDB's IP whitelisting feature


