# ai-php-api

A Laravel API gateway for multiple AI services, including Gemini, OpenAI  and Claude.

### Gemini is working

### OpenAi work in progress

### Claude not started yet.

## Development Setup with Docker

This project is configured to run in Docker using a performance-optimized setup for macOS and Windows. Due to the way Docker handles file synchronization with volumes, the `vendor` directory is excluded from the real-time sync to ensure high performance.

Follow these steps to get the development environment running:

### Prerequisites

- Docker and Docker Compose installed.
- You have cloned both the `ai-php-api` and `ai-chat-frontend` repositories.
- You have created the shared Docker network: `docker network create ai-shared-network`.

### 1. Initial Setup

First, you need to set up your local environment file.

```bash
# Copy the example environment file
cp .env.example .env
```
### 2. Initial Setup

Review the `.env` file and make sure the database credentials match the ones in the `docker-compose.yml` file. You also need to add your AI service API keys here.

### 2. Build and Start the Docker Services

This command will build the PHP image and start the `backend` and `db` services.

```bash
docker-compose up -d --build
```
After this command, the containers will be running, but the application is not yet functional because the `vendor` directory is empty inside the container.

### 3. Install Composer Dependencies (Inside the Container)

This is the most important step. You need to run `composer install` *inside* the running PHP container. We have a helper script for this.

```bash
# This script will execute 'composer install' inside the 'ai_php_api' container.
./docker-install.sh
```
Alternatively, you can run the command manually:
```bash
docker-compose exec ai-php-api composer install
```

### 4. Run Database Migrations

Once the dependencies are installed, you can set up the database.

```bash
docker-compose exec ai-php-api php artisan migrate
```

Your API backend is now fully configured and running! It will be accessible to the frontend container on the shared Docker network.

---

### Important Workflow Note

Because the `vendor` directory is not synced with your host machine, any changes to `composer.json` or `composer.lock` (like adding a new package) will require you to re-run the installation command inside the container:

```bash
# After adding a new package to composer.json on your host machine
docker-compose exec ai-php-api composer update
# or
docker-compose exec ai-php-api composer install
```

