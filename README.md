# ai-php-api

A Laravel API gateway for multiple AI services, including Gemini, OpenAI  and Claude.

### Gemini is working

### OpenAi work in progress

### Claude not started yet.

## Development Setup with Docker

This project is configured to run in Docker. Due to the way Docker handles file synchronization with volumes, the `vendor` directory is excluded from the real-time sync to ensure high performance.

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
## Running Automated Tests

This project includes a fully containerized test suite that runs on an isolated MariaDB database, ensuring that your main development database is never affected.

### Prerequisites

- The development environment does not need to be running to execute the tests.

### 1. Test Environment Setup

The testing environment relies on its own environment file. Before running the tests for the first time, create a copy of the main `.env` file.

```bash
# This command creates the specific environment file for testing.
cp .env .env.test
```

Review the `.env.test` file. The default database credentials are set up to work with the testing Docker Compose file, but you can adjust them if needed. Ensure that `DB_HOST` is set to `db-tester`.

### 2. Executing the Test Suite

We provide helper scripts to automate the entire process of building the test environment, running the tests, and cleaning up afterwards.

**On Windows:**
```bash
run-tests.bat
```

**On Linux or macOS:**
```bash
# Make sure the script is executable first: chmod +x run-tests.sh
./run-tests.sh
```

The script will perform the following actions:
1.  Build and start dedicated `app-tester` and `db-tester` containers.
2.  Wait for the database to be fully ready.
3.  Execute the PHPUnit test suite inside the `app-tester` container.
4.  Stop and remove all testing containers and volumes upon completion.

The test results will be displayed directly in your terminal.