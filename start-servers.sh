#!/bin/bash

# Exit on error
set -e

echo "Starting multiple Laravel instances for load balancing..."

# Define the ports
PORTS=(8001 8002 8003)

# Kill any existing PHP processes on these ports
for PORT in "${PORTS[@]}"; do
    PID=$(lsof -t -i:$PORT 2>/dev/null || true)
    if [ ! -z "$PID" ]; then
        echo "Killing process on port $PORT"
        kill $PID
    fi
done

# Clear all caches
php artisan optimize:clear

# Start Laravel instances
for PORT in "${PORTS[@]}"; do
    echo "Starting Laravel instance on port $PORT"
    PHP_CLI_SERVER_WORKERS=5 php artisan serve --port=$PORT --no-reload &
    
    # Wait a bit to ensure the server starts
    sleep 2
    
    # Check if the server is running
    if curl -s http://localhost:$PORT/health > /dev/null; then
        echo "✅ Server started successfully on port $PORT"
    else
        echo "❌ Failed to start server on port $PORT"
    fi
done

# Start Nginx load balancer
echo "Starting Nginx load balancer..."
sudo nginx -c $(pwd)/nginx.load-balancer.conf

echo "Load balanced setup is running!"
echo "Access the application at http://localhost"
echo "Monitor the logs at:"
echo "- Load balancer: /var/log/nginx/lb_access.log"
echo "- Application: storage/logs/" 