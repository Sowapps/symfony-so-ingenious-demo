#!/bin/bash
# Start the Symfony server and Docker containers for the project

function show_help {
    echo "Usage: app_update.sh [-h|--help]"
    echo ""
    echo "Options:"
    echo "  -h, --help   Show this help message"
}

function print_error() {
  echo "Error: $1" >&2
  exit 1
}

# Check if the argument -h/--help is passed
if [[ "$1" == "-h" || "$1" == "--help" ]]; then
    show_help
    exit 0
fi

# Update composer
composer update;

# Update ImportMap (NPM dependencies)
php bin/console importmap:update

echo "Application updated"

