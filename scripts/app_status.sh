#!/bin/bash
# Display the status of the Symfony server and Docker containers

function show_help {
    echo "Usage: app_status.sh [-h|--help]"
    echo ""
    echo "Options:"
    echo "  -h, --help   Show this help message"
}

# Check if the argument -h/--help is passed
if [[ "$1" == "-h" || "$1" == "--help" ]]; then
    show_help
    exit 0
fi

# Display Symfony server status
echo "Symfony server status:"
symfony server:status

# Display Symfony server PID
if [ -f symfony_server.pid ]; then
    echo "Symfony server PID: $(cat symfony_server.pid)"
else
    echo "Symfony server PID not found."
fi

# Display Docker containers status
echo ""
echo "Docker containers status:"
docker ps -a

