#!/bin/bash

# Exit on error
set -e

echo "Starting local development setup..."

# Copy environment file
if [ ! -f .env ]; then
    cp .env.local .env
    php artisan key:generate
fi

# Install Composer dependencies
echo "Installing Composer dependencies..."
composer install

# Install NPM dependencies
echo "Installing NPM dependencies..."
npm install

# Create database
echo "Setting up database..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS cafm_local"

# Run migrations and seeders
echo "Running migrations and seeders..."
php artisan migrate:fresh --seed

# Generate IDE helper files
echo "Generating IDE helper files..."
php artisan ide-helper:generate
php artisan ide-helper:models -N
php artisan ide-helper:meta

# Link storage
echo "Linking storage..."
php artisan storage:link

# Clear caches
echo "Clearing caches..."
php artisan optimize:clear

# Start development servers
echo "Starting development servers..."
echo "Run the following commands in separate terminals:"
echo "1. php artisan serve"
echo "2. npm run dev"
echo "3. php artisan queue:work"

echo "Local development setup completed!"
echo "Visit http://localhost:8000 to access the application" 