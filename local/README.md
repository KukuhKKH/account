# 🐳 Local Development Guide (Modular Docker Stacks)

Setiap stack service dipisahkan ke file docker-compose masing-masing di dalam folder `local/` dan saling terhubung melalui bridge network `identity-local-net`:

- **[local/docker-compose.yml](file:///home/kukuh/Koding/identity/local/docker-compose.yml)**: App Stack (Backend Hypervel `9501` + Frontend Nuxt 4 `3000`)
- **[local/docker-compose.redis.yml](file:///home/kukuh/Koding/identity/local/docker-compose.redis.yml)**: Redis Stack (`6379`)
- **[local/docker-compose.logto.yml](file:///home/kukuh/Koding/identity/local/docker-compose.logto.yml)**: Logto SSO Stack (`logto-db` + `logto-init` + `logto` `3001` / `3002`)

Semua perintah dijalankan melalui **`Makefile`** di root repository.

---

## 🚀 1. Manajemen Service

```bash
# Menjalankan SEMUA service (App + Redis + Logto)
make up-all

# Menghentikan SEMUA service
make down-all

# --- Manajemen Terpisah ---

# 1. App Stack (Backend & Frontend saja)
make up
make down
make logs-backend
make logs-frontend

# 2. Redis Stack
make redis-up
make redis-down
make redis-logs
make redis-cli

# 3. Logto SSO Stack
make logto-up
make logto-down
make logto-logs
```

---

## 🛠️ 2. Database & Artisan Commands

```bash
# Menjalankan migration database
make migrate

# Rollback migration
make migrate-rollback

# Fresh migration
make migrate-fresh

# Menjalankan database seeders
make seed

# Melihat daftar routes
make route-list

# Menjalankan perintah artisan kustom
make artisan CMD="make:controller Auth/LogtoController"
make artisan CMD="make:model UserRole"
```

---

## 📦 3. Composer & Package Management

```bash
# Install package baru via container
make composer CMD="require hypervel/redis"

# Composer update
make composer-update

# Dump autoload
make composer-dump
```

---

## 💻 4. Frontend Commands

```bash
# Menjalankan dev server frontend lokal
make frontend-dev

# Build production static SPA Nuxt 4
make frontend-build
```

---

## 🌐 5. Port & URL Akses

| Service | Host Port | URL | File Compose |
|---|---|---|---|
| **Backend (Hypervel)** | `9501` | `http://localhost:9501` | `local/docker-compose.yml` |
| **Frontend (Nuxt 4 SPA)** | `3000` | `http://localhost:3000` | `local/docker-compose.yml` |
| **Redis Server** | `6379` | `localhost:6379` | `local/docker-compose.redis.yml` |
| **Logto Core (OIDC / SSO)** | `3001` | `http://localhost:3001` | `local/docker-compose.logto.yml` |
| **Logto Admin Console** | `3002` | `http://localhost:3002` | `local/docker-compose.logto.yml` |
