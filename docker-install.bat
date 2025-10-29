@echo off
REM A helper script to run composer install inside the Docker container.

REM Check if the container is running
docker ps -q -f name=ai_php_api | findstr . > nul
if %errorlevel% neq 0 (
    echo Error: The 'ai_php_api' container is not running.
    echo Please start the services first with 'docker-compose up -d'
    exit /b 1
)

echo Running 'composer install' inside the container...

REM Execute the command
docker-compose exec ai-php-api composer install

echo Composer dependencies installed successfully!