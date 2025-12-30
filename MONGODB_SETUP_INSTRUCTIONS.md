# MongoDB Database Setup Instructions

## Overview

After migrating from MySQL to MongoDB, you need to set up the database structure. MongoDB doesn't use SQL migrations, so we've created an Artisan command to set up all collections and indexes.

## Step 1: Install MongoDB Extension (If Not Done)

Make sure the MongoDB PHP extension is installed on your server. See `EDIT_PHP_INI_FORGE.md` for instructions.

## Step 2: Verify MongoDB Connection

Test that you can connect to MongoDB:

```bash
php artisan tinker
```

Then:
```php
DB::connection('legacy')->getMongoClient()->listDatabases();
```

If this works, you're connected!

## Step 3: Run the Setup Command

Run the MongoDB setup command to create all collections and indexes:

```bash
php artisan mongodb:setup
```

This will:
- Create all required collections
- Set up indexes for efficient queries
- Verify the structure

## What Gets Created

The setup command creates the following collections:

### Core Collections
- **accounts** - User accounts (investors, admins, etc.)
- **people** - Person records (linked to accounts)
- **companies** - Company records (linked to accounts)
- **account_types** - Account type definitions

### Project Collections
- **projects** - Investment projects
- **project_log** - Project updates/announcements
- **project_investments** - Investment records
- **project_documents** - Project-related documents
- **properties** - Property details for projects

### Document Collections
- **account_documents** - Account-specific documents
- **update_images** - Images/files attached to updates

### Communication Collections
- **email_logs** - Email sending history
- **support_tickets** - Support ticket system
- **investor_notifications** - Investor notifications
- **document_email_logs** - Document email tracking

### Relationship Collections
- **account_shares** - Account sharing relationships

## Indexes Created

Each collection has appropriate indexes for:
- **Unique constraints** (e.g., email addresses, project_id)
- **Foreign key lookups** (e.g., account_id, project_id)
- **Query optimization** (e.g., status, deleted flags)
- **Sorting** (e.g., sent_on, created_at descending)

## Verification

After running the setup, verify collections exist:

```bash
php artisan tinker
```

```php
$db = DB::connection('legacy')->getMongoClient()->selectDatabase('admin');
$collections = $db->listCollections();
foreach ($collections as $collection) {
    echo $collection->getName() . "\n";
}
```

## Troubleshooting

### "Class 'MongoDB\Client' not found"
- MongoDB extension not installed
- Run: `pecl install mongodb` and add to php.ini

### "Connection refused"
- Check MongoDB credentials in `.env`
- Verify server IP is whitelisted in DigitalOcean
- Check network connectivity

### "Authentication failed"
- Verify username/password in `.env`
- Check `DB_LEGACY_AUTHENTICATION_DATABASE` is set correctly

### Collections Not Created
- MongoDB creates collections automatically on first insert
- The setup command ensures they exist and creates indexes
- If collections are missing, they'll be created when data is inserted

## Next Steps

After setup:
1. ✅ Verify collections exist
2. ✅ Test a simple query: `Account::first()`
3. ✅ Import existing data (if you have a backup)
4. ✅ Test the application functionality

## Re-running Setup

You can safely re-run the setup command:
- It will recreate indexes (drops and recreates)
- It won't delete existing data
- Safe to run multiple times

```bash
php artisan mongodb:setup
```

## Manual Collection Creation (If Needed)

If the command fails, you can manually create collections by inserting a document:

```php
DB::connection('legacy')->collection('accounts')->insertOne(['_id' => new \MongoDB\BSON\ObjectId()]);
```

But the setup command is recommended as it also creates all indexes.


