#!/bin/bash
# Install project dependencies and set up the environment

function show_help {
    echo "Usage: app_install.sh [--dev|--prod] [--interactive=0|1] [-h|--help]"
    echo ""
    echo "Options:"
    echo "  --dev              Install project for development"
    echo "  --prod             Install project for production"
    echo "  --interactive=0|1  Enable (1, default) or disable (0) interactive mode"
    echo "  -h, --help         Show this help message"
}

# Initialize variables
dev_mode=0
prod_mode=0
interactive=2 # 2 means not overridden

# Parse arguments
for arg in "$@"
do
    case $arg in
        --dev)
            dev_mode=1
            shift
            ;;
        --prod)
            prod_mode=1
            shift
            ;;
        --interactive=0)
            interactive=0
            shift
            ;;
        --interactive=1)
            interactive=1
            shift
            ;;
        -h|--help)
            show_help
            exit 0
            ;;
        *)
            echo "Unknown option: $arg"
            show_help
            exit 1
            ;;
    esac
done

# Check for conflicting options
if [[ $dev_mode -eq 1 && $prod_mode -eq 1 ]]; then
    echo "Error: --dev and --prod options cannot be used together."
    show_help
    exit 1
fi

# If prod and user didn't override interactive, force to 0
if [[ $interactive -eq 2 ]]; then
    if [[ $prod_mode -eq 1 ]]; then
        interactive=0
    else
        interactive=1
    fi
fi

# Variables
mode=""
modeText="DEV"
if [[ $prod_mode -eq 1 ]]; then
    mode="--no-dev"
    modeText="PROD"
fi
dbMigrateOptions=""
if [[ $interactive -eq 0 ]]; then
    dbMigrateOptions="--no-interaction"
fi

# Install dependencies
echo "Installing PHP dependencies ($modeText, interactive=$interactive)..."
composer install $mode

# Additional setup for development mode
if [[ $dev_mode -eq 1 ]]; then
    echo "Installing Symfony CA for TLS support..."
    symfony server:ca:install

    echo "Creating database..."
    php bin/console doctrine:database:create
fi

echo "Migrating database to latest version..."
php bin/console $dbMigrateOptions doctrine:migrations:migrate

echo "Installing asset dependencies with AssetMapper..."
php bin/console importmap:install

# Additional setup for production mode
if [[ $prod_mode -eq 1 ]]; then
    echo "Compiling assets for production..."
    php bin/console asset-map:compile
fi

echo "Setup completed successfully."
