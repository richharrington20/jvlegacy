# Quick MongoDB Setup

## Run This Command

```bash
php artisan mongodb:setup
```

That's it! This will create all collections and indexes needed for the system.

## What It Does

- ✅ Creates all 16 required collections
- ✅ Sets up indexes for fast queries
- ✅ Configures unique constraints
- ✅ Safe to run multiple times

## Collections Created

1. accounts
2. people
3. companies
4. account_types
5. projects
6. project_log
7. project_investments
8. project_documents
9. account_documents
10. update_images
11. email_logs
12. account_shares
13. properties
14. support_tickets
15. investor_notifications
16. document_email_logs

## On Forge Server

SSH into your server and run:

```bash
cd /home/forge/jvlegacy-eqqugdgf.on-forge.com/current
php artisan mongodb:setup
```

## Verify It Worked

```bash
php artisan tinker
```

```php
DB::connection('legacy')->collection('accounts')->count();
// Should return: 0 (or number of accounts if data exists)
```

## Troubleshooting

**"Class 'MongoDB\Client' not found"**
- MongoDB extension not installed
- See `EDIT_PHP_INI_FORGE.md`

**"Connection refused"**
- Check MongoDB credentials in Forge environment variables
- Verify server IP is whitelisted in DigitalOcean

**Command runs but collections don't appear**
- MongoDB creates collections on first insert
- Collections will appear when you insert data
- The command ensures indexes are ready


