# Database Migrations - SQL Files

These SQL files can be run directly on your MySQL database to create the necessary tables and columns.

## Running the Migrations

You can run these SQL files directly on your database using one of these methods:

### Option 1: Using MySQL Command Line
```bash
mysql -u your_username -p jvsys < 001_create_account_documents.sql
mysql -u your_username -p jvsys < 002_create_project_documents.sql
mysql -u your_username -p jvsys < 003_create_update_images.sql
mysql -u your_username -p jvsys < 004_add_rich_content_to_projects.sql
```

### Option 2: Using phpMyAdmin or Database Tool
1. Open phpMyAdmin or your database management tool
2. Select the `jvsys` database
3. Go to the SQL tab
4. Copy and paste the contents of each SQL file one at a time
5. Execute each SQL statement

### Option 3: Run All at Once
```bash
cat *.sql | mysql -u your_username -p jvsys
```

## Migration Order

Run the migrations in this order:
1. `001_create_account_documents.sql`
2. `002_create_project_documents.sql`
3. `003_create_update_images.sql`
4. `004_add_rich_content_to_projects.sql`

## What These Migrations Do

1. **account_documents**: Creates table for storing documents associated with accounts
2. **project_documents**: Creates table for storing documents associated with projects
3. **update_images**: Creates table for storing multiple images per project update
4. **projects table updates**: Adds rich content fields (map, designs, drawings, etc.) and visibility toggles

## Notes

- The `IF NOT EXISTS` clauses ensure the migrations are safe to run multiple times
- All tables use the `legacy` connection (jvsys database)
- Foreign key constraints are not added to maintain compatibility with existing structure

