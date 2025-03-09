#!/bin/bash

# Exit on error
set -e

echo "Starting deployment process..."

# Pull latest changes
echo "Pulling latest changes from git..."
git pull origin main

# Install/update Composer dependencies
echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# Install/update NPM dependencies and build assets
echo "Installing NPM dependencies and building assets..."
npm install
npm run build

# Clear all caches
echo "Clearing application cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Set maintenance mode
echo "Enabling maintenance mode..."
php artisan down

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Optimize the application
echo "Optimizing the application..."
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear maintenance mode
echo "Disabling maintenance mode..."
php artisan up

# Restart queue workers
echo "Restarting queue workers..."
php artisan queue:restart

# Restart Horizon (if enabled)
if [ "${HORIZON_ENABLED}" = "true" ]; then
    echo "Restarting Horizon..."
    php artisan horizon:terminate
fi

# Verify the application is running
echo "Verifying application health..."
php artisan about

echo "Deployment completed successfully!" 