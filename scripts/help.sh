#!/bin/bash
# Script to list all available scripts in the project with descriptions and usage

# TODO Unify sh/php help

# Set locale to avoid grep locale issues
export LC_ALL=C.utf8

echo "Available scripts:"

# List all shell scripts with their descriptions
for script in $(ls scripts/*.sh); do
    description=$(head -n 2 $script | grep -oP "(?<=# ).*")
    echo -e "\nCommand: ./scripts/$(basename $script)"
    echo "Description: $description"
done

# List all batch scripts with their descriptions
for script in $(ls scripts/*.bat); do
    description=$(head -n 2 $script | grep -oP "(?<=:: ).*")
    echo -e "\nCommand: ./scripts/$(basename $script)"
    echo "Description: $description"
done

echo "
Command: php bin/console make:entity
Create entity

Command: php bin/console doctrine:database:create
Create database

Command: php bin/console make:migration
Create new migration file in migrations/ folder

Command: php bin/console doctrine:migrations:migrate
Apply all missing migrations

Command: php bin/console app:user:create --weak
Create a new user
"

