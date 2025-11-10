#!/bin/bash
# Start the Symfony server and Docker containers for the project with DEV environment

function show_help {
    echo "Usage: app_start_dev.sh [-h|--help]"
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

# TODO If you start prod, it will build it in public and this override dev watch, so we should remove this folder when starting dev ? But maybe we could configure it instead...

# Start Docker Compose services
docker-compose -f docker/docker-compose.yml up -d || print_error "Unable to start docker containers, ensure Docker Desktop is running."

php bin/console sass:build --watch &

# Run Symfony server and store PID

# With no concurrency (one request in process)
symfony server:start

# With concurrency (not working ?)
#php -S localhost:8000 -t public &
#echo $! > symfony_server.pid

echo "Symfony server started with PID $(cat symfony_server.pid)"

#tail -f var/log/dev.log

