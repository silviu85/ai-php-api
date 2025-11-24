@echo off
REM A script to start Dev suite.

echo Starting development environment...
SET COMPOSE_CMD=docker-compose -f docker-compose.yml --env-file .env

%COMPOSE_CMD% up -d
echo.
cd ../ai-chat-react
echo.
echo Starting React development server...
%COMPOSE_CMD% up -d
cd ../ai-php-api
echo.
echo Dev environment is up and running...