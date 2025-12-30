#!/bin/bash
# Push all changes to repository

cd /Users/richcopestake/Documents/Rise/jvlegacy

echo "Staging all changes..."
git add -A

echo "Committing changes..."
git commit -m "Add MongoDB support for legacy database connection

- Add mongodb/laravel-mongodb package to composer.json
- Update database config to use MongoDB connection
- Fix migration to skip SQL operations when using MongoDB
- Add comprehensive MongoDB setup documentation
- Add local development setup guides and scripts"

echo "Pushing to repository..."
git push origin main

echo "✅ Done!"


