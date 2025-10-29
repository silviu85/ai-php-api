#!/bin/bash

# A helper script to run composer install inside the Docker container.

# Check if the container is running
if [ ! "$(docker ps -q -f name=ai_php_api)" ]; then
    echo "Error: The 'ai_php_api' container is not running."
    echo "Please start the services first with 'docker-compose up -d'"
    exit 1
fi

echo "Running 'composer install' inside the container..."

# Execute the command
docker-compose exec ai-php-api composer install

echo "Composer dependencies installed successfully!"