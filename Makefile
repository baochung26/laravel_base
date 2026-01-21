.PHONY: help install up down restart logs shell composer npm artisan

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: ## Install project dependencies
	docker-compose exec app composer install
	docker-compose exec app npm install

up: ## Start Docker containers
	docker-compose up -d

down: ## Stop Docker containers
	docker-compose down

restart: ## Restart Docker containers
	docker-compose restart

logs: ## Show container logs
	docker-compose logs -f

shell: ## Open shell in app container
	docker-compose exec app bash

composer: ## Run composer command (usage: make composer CMD="install package")
	docker-compose exec app composer $(CMD)

npm: ## Run npm command (usage: make npm CMD="install")
	docker-compose exec app npm $(CMD)

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

cache-clear: ## Clear all caches
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

db-shell: ## Open MySQL shell
	docker-compose exec db mysql -u laravel_user -proot laravel_db
