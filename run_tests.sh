code
Sh
#!/bin/bash

# A script to run the PHPUnit test suite in an isolated Docker environment for Unix-like systems.

set -e

COMPOSE_CMD="docker-compose -f docker-compose.test.yml --env-file .env.test"

echo "Starting testing environment..."
$COMPOSE_CMD up -d --build

echo ""
echo "Waiting for Database to be ready..."
sleep 7

echo ""
echo "Running PHPUnit tests..."
$COMPOSE_CMD exec -T app-tester ./vendor/bin/phpunit

echo ""
echo "Tearing down testing environment..."
$COMPOSE_CMD down -v

echo ""
echo "Tests finished."