#!/bin/bash
# Stop the Symfony server and Docker containers for the project

function show_help {
    echo "Usage: app_stop.sh [-h|--help]"
    echo ""
    echo "Options:"
    echo "  -h, --help   Show this help message"
}

# Check if the argument -h/--help is passed
if [[ "$1" == "-h" || "$1" == "--help" ]]; then
    show_help
    exit 0
fi

# Stop Symfony server
echo "Stopping Symfony server..."
symfony server:stop
sleep 1

# Check if Symfony server has stopped
if symfony server:status | grep -q "Not Running"; then
    echo "Symfony server has stopped successfully."
else
    echo "Symfony server did not stop properly. Attempting to kill the process..."
    if [ -f symfony_server.pid ]; then
        TASK_PID=$(cat symfony_server.pid)
        kill $TASK_PID
        sleep 1
        rm symfony_server.pid
        if symfony server:status | grep -q "Not Running"; then
            echo "Symfony server was killed successfully."
        else
            echo "Failed to stop Symfony server."
        fi
    else
        echo "Symfony server PID file not found. Unable to kill the process."
    fi
fi

# Stop Docker services
echo "Stopping Docker services..."
docker-compose -f docker/docker-compose.yml down

echo "All local services have been stopped."

