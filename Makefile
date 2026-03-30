.PHONY: help install up down restart logs shell composer artisan queue-logs scheduler-logs workers-restart ps npm-install npm-build npm-dev up-build

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: ## Install project dependencies
	docker-compose exec app composer install

up: ## Start Docker containers
	docker-compose up -d

down: ## Stop Docker containers
	docker-compose down

restart: ## Restart Docker containers
	docker-compose restart

logs: ## Show container logs
	docker-compose logs -f

ps: ## Show running containers
	docker-compose ps

shell: ## Open shell in app container
	docker-compose exec app bash

composer: ## Run composer command (usage: make composer CMD="install package")
	docker-compose exec app composer $(CMD)

artisan: ## Run artisan command (usage: make artisan CMD="migrate")
	docker-compose exec app php artisan $(CMD)

setup: ## Initial project setup
	docker-compose up -d
	@echo "Waiting for containers to be ready..."
	@sleep 10
	docker-compose exec app composer install
	docker-compose exec app cp .env.example .env
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate
	@echo "Setup complete! Access your app at http://localhost:8000"

fresh: ## Fresh migration with seeding
	docker-compose exec app php artisan migrate:fresh --seed

test: ## Run tests
	docker-compose exec app php artisan test

queue-logs: ## Tail queue worker logs
	docker-compose logs -f queue

scheduler-logs: ## Tail scheduler logs
	docker-compose logs -f scheduler

workers-restart: ## Restart queue worker + scheduler
	docker-compose restart queue scheduler

npm-install: ## Install frontend dependencies via Node container
	docker-compose run --rm node npm install

npm-build: ## Build frontend assets via Vite
	docker-compose run --rm node_build

npm-dev: ## Run Vite dev server via Node container
	docker-compose up node

up-build: ## Build frontend assets then start backend containers
	docker-compose --profile build run --rm node_build
	docker-compose up -d app queue scheduler webserver db phpmyadmin redis

cache-clear: ## Clear all caches
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

db-shell: ## Open MySQL shell
	docker-compose exec db mysql -u laravel_user -proot laravel_db
