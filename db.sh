#!/bin/bash

# Define Symfony console command paths
CONSOLE_PATH="bin/console"
DOCTRINE_MIGRATIONS_PATH="vendor/bin/doctrine-migrations"

# Remove previous migration files from the workspace
rm -rf ./src/Migrations/*

# Rebuild the database (drop, create schema, and load fixtures if needed)
php $CONSOLE_PATH doctrine:database:drop --force
php $CONSOLE_PATH doctrine:database:create
php $CONSOLE_PATH doctrine:schema:update --force
# Uncomment the line below if you want to load fixtures after rebuilding the schema
# php $CONSOLE_PATH doctrine:fixtures:load --no-interaction

# Generate a new migration
php $DOCTRINE_MIGRATIONS_PATH generate

# Run Doctrine migrations
php $DOCTRINE_MIGRATIONS_PATH migrate

# Remove previous migrations and their records from the database
php $CONSOLE_PATH doctrine:migrations:version --delete --all

echo "Remove previous migration files, rebuild, create migration, migrate, and delete previous migrations and migrated database completed successfully."
