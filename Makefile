# =============================================================================
# BangLipai Identity — Makefile
# =============================================================================

COMPOSE_APP   := local/docker-compose.yml
COMPOSE_LOGTO := local/docker-compose.logto.yml
COMPOSE_REDIS := local/docker-compose.redis.yml

DOCKER_PHP    := docker run --rm -v $(CURDIR)/backend:/app -w /app --network identity-local-net -it hyperf/hyperf:8.4-alpine-v3.21-swoole-v6
DOCKER_COMP   := docker run --rm -v $(CURDIR)/backend:/app -w /app --user $(shell id -u):$(shell id -g) hyperf/hyperf:8.4-alpine-v3.21-swoole-v6 composer

BOLD  := \033[1m
RESET := \033[0m

.PHONY: help up down restart logs logs-backend logs-frontend ps up-all down-all logto-up logto-down logto-logs redis-up redis-down redis-logs redis-cli artisan composer migrate migrate-rollback migrate-fresh seed route-list composer-install composer-update composer-dump frontend-dev frontend-build test

help: ## Menampilkan daftar perintah yang tersedia
	@grep -Eh '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "$(BOLD)%-18s$(RESET) %s\n", $$1, $$2}'

## ── App Services (Backend + Frontend) ───────────────────────────────────────

up: ## Menjalankan Backend (9501) & Frontend (3000)
	docker compose -f $(COMPOSE_APP) up -d
	@echo ""
	@echo "🚀 Backend (Hypervel): https://api-identity.home.test (Port: 9501)"
	@echo "🚀 Frontend (Nuxt 4):  https://identity.home.test (Port: 3000)"

down: ## Menghentikan service App
	docker compose -f $(COMPOSE_APP) down

restart: ## Me-restart service App
	docker compose -f $(COMPOSE_APP) restart

logs: ## Melihat log real-time service App
	docker compose -f $(COMPOSE_APP) logs -f

logs-backend: ## Melihat log real-time backend Hypervel
	docker compose -f $(COMPOSE_APP) logs -f backend

logs-frontend: ## Melihat log real-time frontend Nuxt
	docker compose -f $(COMPOSE_APP) logs -f frontend

ps: ## Melihat status container App
	docker compose -f $(COMPOSE_APP) ps

## ── Logto SSO Auth Server ───────────────────────────────────────────────────

logto-up: ## Menjalankan Logto SSO Server (3001) & Admin Console (3002)
	docker compose -f $(COMPOSE_LOGTO) up -d
	@echo ""
	@echo "🔐 Logto Core (Auth):  https://sso.home.test (Port: 3001)"
	@echo "🛠️ Logto Admin Portal: https://console.home.test (Port: 3002)"

logto-down: ## Menghentikan Logto server
	docker compose -f $(COMPOSE_LOGTO) down

logto-logs: ## Melihat log real-time Logto
	docker compose -f $(COMPOSE_LOGTO) logs -f

## ── Redis Cache Server ──────────────────────────────────────────────────────

redis-up: ## Menjalankan Redis cache server (6379)
	docker compose -f $(COMPOSE_REDIS) up -d
	@echo "⚡ Redis Cache: localhost:6379"

redis-down: ## Menghentikan Redis server
	docker compose -f $(COMPOSE_REDIS) down

redis-logs: ## Melihat log real-time Redis
	docker compose -f $(COMPOSE_REDIS) logs -f

redis-cli: ## Masuk ke Redis CLI
	docker compose -f $(COMPOSE_REDIS) exec redis redis-cli

## ── All Services Stack ──────────────────────────────────────────────────────

up-all: ## Menjalankan semua service (App + Redis + Logto)
	docker compose -f $(COMPOSE_REDIS) up -d
	docker compose -f $(COMPOSE_LOGTO) up -d
	docker compose -f $(COMPOSE_APP) up -d
	@echo ""
	@echo "✅ Semua service lokal berhasil dijalankan!"
	@echo "🚀 Backend:      https://api-identity.home.test"
	@echo "🚀 Frontend:     https://identity.home.test"
	@echo "🔐 Logto Core:   https://sso.home.test"
	@echo "🛠️ Logto Admin:  https://console.home.test"
	@echo "⚡ Redis:        localhost:6379"

down-all: ## Menghentikan semua service (App + Redis + Logto)
	docker compose -f $(COMPOSE_APP) down
	docker compose -f $(COMPOSE_LOGTO) down
	docker compose -f $(COMPOSE_REDIS) down

## ── Artisan Commands ────────────────────────────────────────────────────────

artisan: ## Menjalankan artisan (contoh: make artisan CMD="route:list")
	@if docker compose -f $(COMPOSE_APP) ps --status running | grep -q identity-local-backend; then \
		docker compose -f $(COMPOSE_APP) exec backend php artisan $(CMD); \
	else \
		$(DOCKER_PHP) php artisan $(CMD); \
	fi

migrate: ## Menjalankan database migration
	@$(MAKE) artisan CMD="migrate"

migrate-rollback: ## Rollback migration database
	@$(MAKE) artisan CMD="migrate:rollback"

migrate-fresh: ## Fresh migration database
	@$(MAKE) artisan CMD="migrate:fresh"

seed: ## Menjalankan database seeders
	@$(MAKE) artisan CMD="db:seed"

route-list: ## Melihat daftar routes backend
	@$(MAKE) artisan CMD="route:list"

## ── Composer Commands ───────────────────────────────────────────────────────

composer: ## Menjalankan composer (contoh: make composer CMD="require hypervel/redis")
	$(DOCKER_COMP) $(CMD)

composer-install: ## Install dependensi composer
	$(DOCKER_COMP) install

composer-update: ## Update dependensi composer
	$(DOCKER_COMP) update

composer-dump: ## Dump autoload composer
	$(DOCKER_COMP) dump-autoload

## ── Frontend Commands ───────────────────────────────────────────────────────

frontend-dev: ## Menjalankan frontend dev server lokal
	cd frontend && pnpm dev

frontend-typecheck: ## Menjalankan typecheck TypeScript Nuxt
	cd frontend && pnpm typecheck

frontend-generate: ## Generate / Build static frontend Nuxt
	cd frontend && pnpm generate

frontend-build: ## Build frontend Nuxt static SPA
	cd frontend && pnpm generate

## ── Testing & Quality ───────────────────────────────────────────────────────

test-backend: ## Menjalankan test suite backend
	@$(MAKE) artisan CMD="test"

test-frontend: ## Menjalankan typecheck dan build/generate frontend
	cd frontend && pnpm typecheck && pnpm generate

test: ## Menjalankan seluruh verifikasi kualitas (Backend tests + Frontend typecheck & generate)
	@$(MAKE) test-backend
	@$(MAKE) test-frontend
