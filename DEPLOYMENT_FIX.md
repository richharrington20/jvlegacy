# MongoDB Migration Fix

## The Problem

Your migrations use SQL operations (ALTER TABLE, CREATE TABLE) which don't work with MongoDB (NoSQL database). The migration `2025_01_20_000001_add_rich_content_fields_to_projects_table.php` is trying to run SQL ALTER TABLE statements on a MongoDB connection.

## Solutions

### Option 1: Skip Migrations for Legacy Connection (Recommended for Now)

Update your **Forge deployment script** to skip migrations that target the legacy connection:

```bash
# In Forge, go to your site → Deployment Script
# Replace the migration line with:

# Skip migrations if using MongoDB for legacy connection
if [ "$DB_LEGACY_DRIVER" != "mongodb" ]; then
    php artisan migrate --force
else
    echo "Skipping migrations - MongoDB connection detected"
    # Only run migrations for non-legacy connections
    php artisan migrate --database=mysql --force 2>/dev/null || echo "No MySQL migrations to run"
fi
```

### Option 2: Make Migrations MongoDB-Aware

Update migrations to check the driver type. Here's an example for the failing migration:

```php
public function up(): void
{
    $connection = DB::connection('legacy');
    $driver = config("database.connections.legacy.driver");
    
    if ($driver === 'mongodb') {
        // MongoDB doesn't need schema changes - fields are added automatically
        // Just ensure the collection exists
        $connection->getMongoClient()
            ->selectDatabase(config('database.connections.legacy.database'))
            ->createCollection('projects');
        return;
    }
    
    // SQL operations for MySQL
    Schema::connection('legacy')->table('projects', function (Blueprint $table) {
        // ... your existing migration code
    });
}
```

### Option 3: Disable Migrations Entirely (Quick Fix)

In Forge deployment script, comment out or remove:

```bash
# php artisan migrate --force
```

**Warning:** This means new tables won't be created automatically. You'll need to create them manually or via MongoDB operations.

## Immediate Fix for Deployment

**Update your Forge environment variables:**

1. Go to your site in Forge
2. Click "Environment"
3. Add/update these variables:

```env
DB_LEGACY_DSN=mongodb+srv://doadmin:09u46Y7c53MbQ1Fe@db-mongodb-lon1-92389-63170cb8.mongo.ondigitalocean.com/admin?tls=true&authSource=admin
DB_LEGACY_HOST=db-mongodb-lon1-92389-63170cb8.mongo.ondigitalocean.com
DB_LEGACY_DATABASE=admin
DB_LEGACY_USERNAME=doadmin
DB_LEGACY_PASSWORD=09u46Y7c53MbQ1Fe
DB_LEGACY_AUTHENTICATION_DATABASE=admin
```

4. **Update Deployment Script** - Add this at the top:

```bash
# Skip migrations for MongoDB legacy connection
php artisan migrate --force --pretend 2>&1 | grep -q "legacy" && echo "Skipping legacy migrations (MongoDB)" || php artisan migrate --force
```

Or simpler - just skip migrations for now:

```bash
# php artisan migrate --force  # Commented out until migrations are MongoDB-compatible
```

## Next Steps

1. **Install MongoDB package locally:**
   ```bash
   composer require mongodb/laravel-mongodb
   composer update
   ```

2. **Commit the changes:**
   ```bash
   git add composer.json config/database.php
   git commit -m "Add MongoDB support for legacy database connection"
   git push
   ```

3. **Update Forge environment variables** (as shown above)

4. **Update Forge deployment script** to skip migrations or make them MongoDB-aware

5. **Test the connection** after deployment

## Long-term Solution

Consider one of these approaches:

1. **Hybrid approach:** Keep new Laravel tables in MySQL, use MongoDB only for legacy data
2. **Convert migrations:** Rewrite all migrations to use MongoDB operations
3. **Migration wrapper:** Create a base migration class that handles both SQL and MongoDB


