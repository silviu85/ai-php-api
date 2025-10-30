@echo off
REM A script to run the PHPUnit test suite in an isolated Docker environment.

echo Starting testing environment...
SET COMPOSE_CMD=docker-compose -f docker-compose.test.yml --env-file .env.test

%COMPOSE_CMD% up -d --build
echo.
echo Waiting for Database to be ready...
timeout /t 7 /nobreak >nul
echo.
echo Running PHPUnit tests...
%COMPOSE_CMD% exec -T app-tester ./vendor/bin/phpunit

echo Tearing down testing environment...
%COMPOSE_CMD% down -v

echo Tests finished.