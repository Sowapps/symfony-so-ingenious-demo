#!/bin/bash
# Start the Symfony server and Docker containers for the project with PROD environment

function show_help {
    echo "Usage: app_start_prod.sh [-h|--help]"
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

# Start Docker Compose services
docker-compose -f docker/docker-compose.yml up -d || print_error "Unable to start docker containers, ensure Docker Desktop is running."

# Build assets
php bin/console sass:build
php bin/console asset-map:compile

# Run Symfony server and store PID
APP_ENV=prod symfony server:start
#symfony server:start > /dev/null 2>&1 &
echo $! > symfony_server.pid

echo "Symfony server started with PID $(cat symfony_server.pid)"

