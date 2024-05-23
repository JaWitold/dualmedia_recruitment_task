# DualMedia Recruitment Task

This repository serves as a recruitment task solution utilizing Symfony framework for API. The architecture is designed to facilitate Docker-based development, optimized for Windows with WSL, and is easily adaptable for Linux & Docker environments.

  The repository contains extra container definitions and configurations because is developed over [github template](https://github.com/JaWitold/fullstack_development_template)
## Prerequisites

- Docker installed on your system
- Docker Compose for orchestrating multi-container Docker applications

## Getting Started

### Environment Setup

Before running the application, environment variables must be set. Execute the following command in the root of the repository to copy `.env.example` files to `.env`:

```bash
find . -type f -name ".env.example" -exec sh -c 'cp "$0" "${0%.example}"' {} \;
```
> **Important:** `.env` files contain sensitive information and are not be committed to the repository.

### Running the Application
With `.env` files prepared, start the application using Docker Compose:

```bash
docker-compose up -d
```

> **Important:** Before accessing `localhost:8080` for the first time it is mandatory to install composer packages and  migrate MySQL migrations. It can be done by executing:
> ```bash
> docker compose exec -t php bash -c "composer install"
> docker compose exec -t php bash -c "php bin/console doctrine:migrations:migrate -n"
> ```

## Sample data

Sample data could be found in `docker/mysql/` directory.

## API Structure
The solution serves the following API routes:

### Order:
- GET http://localhost:8080/api/order
- POST http://localhost:8080/api/order
- GET http://localhost:8080/api/order/{id}

### Product:
- GET http://localhost:8080/api/product
- POST http://localhost:8080/api/product
- GET http://localhost:8080/api/product/{id}

Detailed documentation of the routes is available at  http://localhost:8080/api/doc and http://localhost:8080/api/doc.json

## Running Tests

The following tests could be performed:

- **PHP Code Sniffer**: Checks the code against coding standards to ensure consistency.

   ``` bash
   docker compose exec -t php sh -c "XDEBUG_MODE=off php vendor/bin/phpcs -p"
   ```
- **PHPStan**: Performs static analysis to identify potential errors and improve code quality.

   ``` bash
   docker-compose exec -t php sh -c "XDEBUG_MODE=off php vendor/bin/phpstan analyse"
   ```
- **PHPUnit Tests**: Runs the unit tests to ensure the functionality of the application.

   ``` bash
   docker-compose exec -t php sh -c "XDEBUG_MODE=off php vendor/bin/phpunit"
   ```

Make sure all tests pass without any errors before submitting your merge request.