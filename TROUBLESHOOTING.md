# Troubleshooting Local Server Setup

## Issue: Server Exits Immediately

If `composer run dev` exits right away, try these steps:

### Step 1: Verify Dependencies Are Installed

```bash
# Check if vendor directory exists
ls -la vendor/

# If missing, install dependencies
composer install
```

### Step 2: Setup Environment File

```bash
# Create .env if missing
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 3: Test Components Individually

Instead of running everything together, test each component:

**Terminal 1 - Test Laravel Server:**
```bash
php artisan serve
```
If this works, you should see: "Laravel development server started: http://127.0.0.1:8000"

**Terminal 2 - Test Vite:**
```bash
npm run dev
```
If this works, you should see Vite starting on a port (usually 5173)

**Terminal 3 - Test Queue (optional):**
```bash
php artisan queue:work
```

### Step 4: Check for Specific Errors

If a component fails, check:

**Database Connection:**
```bash
php artisan tinker
>>> DB::connection('legacy')->getPdo();
```
This will show database connection errors.

**Configuration Issues:**
```bash
php artisan config:clear
php artisan cache:clear
```

**Missing Storage Link:**
```bash
php artisan storage:link
```

### Step 5: Check Logs

```bash
# View Laravel logs
tail -f storage/logs/laravel.log
```

### Step 6: Run Simple Server First

Before using `composer run dev`, try the simple server:

```bash
# Terminal 1
php artisan serve

# Terminal 2 (in a new terminal)
npm run dev
```

Then visit: http://localhost:8000

### Common Issues

**Issue: "Class not found" errors**
- Solution: Run `composer dump-autoload`

**Issue: "No application encryption key"**
- Solution: Run `php artisan key:generate`

**Issue: "SQLSTATE[HY000] [2002] Connection refused"**
- Solution: Check database credentials in `.env` and ensure MySQL is running

**Issue: "Permission denied" on storage**
- Solution: `chmod -R 775 storage bootstrap/cache`

**Issue: Port already in use**
- Solution: `php artisan serve --port=8001`

### Minimal Setup (If Full Dev Fails)

If `composer run dev` keeps failing, use this minimal setup:

```bash
# Terminal 1 - Laravel only
php artisan serve

# Terminal 2 - Vite only  
npm run dev
```

This gives you the core functionality without queue workers and log viewers.


