# 🛡️ BangLipai Identity — SSO & IAM Engine

[![Hypervel](https://img.shields.io/badge/Hypervel-PHP%208.4%20Swoole%20Coroutine-787CB5?logo=php&logoColor=white)](https://hypervel.org)
[![Nuxt 4](https://img.shields.io/badge/Nuxt%204-Vue%203%20%7C%20Vite%20%7C%20Pinia-00DC82?logo=nuxtdotjs&logoColor=white)](https://nuxt.com)
[![PrimeVue v4](https://img.shields.io/badge/PrimeVue-v4%20Aura%20Theme-41B883?logo=vuedotjs&logoColor=white)](https://primevue.org)
[![Tailwind CSS v4](https://img.shields.io/badge/Tailwind_CSS-v4-38B2AC?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Logto SSO](https://img.shields.io/badge/Auth%20Provider-Logto%20OIDC%20%26%20M2M-483699?logo=openid&logoColor=white)](https://logto.io)
[![Test Suite](https://img.shields.io/badge/Tests-122%20Passed%20%7C%20529%20Assertions-brightgreen)](https://github.com/KukuhKKH/identity)

> **BangLipai Identity** adalah sistem manajemen identitas terpusat (_Single Sign-On / SSO Engine_, _Identity & Access Management / IAM_, dan _Backend-for-Frontend / BFF_) untuk seluruh ekosistem **banglipai.web.id**. Proyek ini menggantikan arsitektur monolitik lama dengan pendekatan arsitektur terpisah (_Decoupled Architecture_) berbasis performa tinggi, keamanan berstandar enterprise, dan pengalaman antarmuka modern.

---

## 🧭 Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                             BANG LIPAI ECOSYSTEM                            │
└─────────────────────────────────────┬───────────────────────────────────────┘
                                      │
                 ┌────────────────────┴───────────────────┐
                 ▼                                        ▼
    ┌──────────────────────────┐             ┌────────────────────────┐
    │   Nuxt 4 SPA Frontend    │ ◄─ BFF ───► │   Hypervel Coroutine   │
    │   (PrimeVue v4 + TW v4)  │   Cookies   │   Backend (Port 9501)  │
    └──────────────────────────┘             └───────────┬────────────┘
                                                         │
                        ┌────────────────────────────────┴────────────────┐
                        ▼                                                 ▼
          ┌───────────────────────────┐                     ┌───────────────────────────┐
          │     Logto SSO / OIDC      │                     │        Central DB         │
          │     & M2M Management      │                     │     PostgreSQL / MySQL    │
          │    (Core: 3001, M2M API)  │                     │       (10.10.10.60)       │
          └───────────────────────────┘                     └───────────────────────────┘
```

---

## ⚡ Tech Stack

### Backend (`/backend`)

- **Framework**: [Hypervel](https://hypervel.org) (PHP 8.4+ Engine berbasis Hyperf & Swoole Coroutine).
- **Runtime**: Coroutine Event-Loop (Zero-blocking I/O, instan concurrency).
- **Auth & Federation**: Logto OIDC & M2M Management API.
- **Cache & Session**: Redis 7.
- **Database**: PostgreSQL / MySQL.
- **Security Pipeline**: Decoupled FormRequest, Enterprise Audit Policy, Immutable Audit Logs, Cryptographic HMAC-SHA256 Webhook Verification.

### Frontend (`/frontend`)

- **Framework**: [Nuxt 4](https://nuxt.com) (SPA Mode, Vue 3, Vite, Pinia, VueUse).
- **UI Component Suite**: [PrimeVue v4](https://primevue.org) (`@primeuix/themes` Aura).
- **Styling**: [Tailwind CSS v4](https://tailwindcss.com) (`@tailwindcss/vite`).
- **Icons**: PrimeIcons & Lucide Icons.
- **Type Safety**: TypeScript 5.8+ Strict Mode.

---

## 📁 Struktur Direktori

```text
.
├── backend/                  # Hypervel PHP 8.4 Coroutine Engine
│   ├── app/
│   │   ├── Data/             # Strongly Typed DTOs (Audit, Webhook, User)
│   │   ├── Enums/            # Typed Enums (UserRole, UserStatus, PasswordChangeType)
│   │   ├── Exceptions/       # Domain Exceptions & Centralized Handler
│   │   ├── Http/
│   │   │   ├── Controllers/  # Thin Controllers (Urutan: Request/DTO baru route $id)
│   │   │   ├── Middleware/   # AuthenticateSession, AuditContext, CORS
│   │   │   ├── Requests/     # Form Requests (Validated Input Sanitization)
│   │   │   └── Resources/    # JsonResource Serialization (Zero Secret Leaks)
│   │   ├── Models/           # Eloquent Models (SoftDeletes, Immutable Casts)
│   │   ├── Policies/         # Authorization Policies (AuditPolicy, UserPolicy)
│   │   └── Services/         # Business Invariants, Atomic DB Transactions, M2M Sync
│   ├── config/               # Application & Service Configurations
│   ├── routes/               # api.php (Public/Webhook) & web.php (BFF Session)
│   └── tests/                # Unit & Feature Test Suite (co-phpunit)
├── frontend/                 # Nuxt 4 + PrimeVue v4 Modern SPA
│   ├── app/
│   │   ├── assets/           # Global CSS & Tailwind Directives
│   │   ├── components/       # Reusable Dashboard & UI Components
│   │   ├── composables/      # Reactive Composables (useAuth, useUsers, useAudit)
│   │   ├── layouts/          # Default & Dashboard App Shells
│   │   ├── pages/            # File-based Routing (/dashboard, /users, /audit, /profile)
│   │   ├── stores/           # Pinia Stores (Auth, App State)
│   │   └── types/            # TypeScript Interfaces & API Contracts
├── local/                    # Local Docker Environment & Compose Stacks
│   ├── docker-compose.yml    # App Services (Backend: 9501, Frontend: 3000)
│   ├── docker-compose.logto.yml # Logto Core (3001) & Admin Console (3002)
│   └── docker-compose.redis.yml # Redis Cache & Session Store (6379)
├── fase-development.md       # Roadmap & Status Rinci Fase Pengembangan
├── Makefile                  # Toolchain CLI & Developer Automation
└── README.md                 # Dokumentasi Utama Proyek
```

---

## 🚀 Memulai (Quick Start)

### 1. Prasyarat Sistem

#### ✅ Cara Beradab & Praktis (Recommended):
- **Docker & Docker Compose** (*Zero-host dependency*, anti ribet).
- **GNU Make** utility (`build-essential`).

> [!TIP]
> Cukup install Docker & Make, seluruh runtime (PHP 8.4 Swoole Coroutine, Node.js 22, pnpm, Redis 7, Logto SSO) sudah otomatis terisolasi di container. Zero polusi di OS host!

#### 💀 Cara Susah / Bare Metal (*Hell Nah!*):
Kalau kamu memang hobi menyiksa diri dan ingin setup manual secara bare metal:
- **PHP 8.4+ CLI** (*Wajib CLI Coroutine, bukan PHP-FPM!*)
- **Ekstensi PHP**: `swoole` (v6.0+), `pdo_pgsql`, `pdo_mysql`, `redis`, `bcmath`, `mbstring`, `openssl`, `pcntl`
- **Composer 2.8+**
- **Node.js 22+** & **pnpm 9+**
- **Redis Server 7.x**
- **PostgreSQL 16+ / MySQL 8.4+**
- **Instance Logto Core & Admin Console** terpisah
*(Sangat tidak direkomendasikan kecuali sedang ingin menguji batas kesabaran hidup 🗿)*

### 2. Menjalankan Seluruh Stack Lokal

```bash
# Menjalankan Redis, Logto SSO, Backend Hypervel, dan Frontend Nuxt
make up-all
```

Akses layanan di browser:

- **Frontend Dashboard**: `http://localhost:3000` (atau `https://identity.home.test`)
- **Backend API**: `http://localhost:9501` (atau `https://api-identity.home.test`)
- **Logto SSO Core**: `http://localhost:3001` (atau `https://sso.home.test`)
- **Logto Admin Console**: `http://localhost:3002` (atau `https://console.home.test`)
- **Redis Cache**: `localhost:6379`

### 3. Migrasi & Seeding Database

```bash
# Jalankan migrasi database lokal
make migrate

# Atau jalankan fresh migration dengan seeder
make migrate-fresh
```

---

## 🛠️ Perintah Makefile (Developer Toolchain)

| Perintah                        | Deskripsi                                                     |
| :------------------------------ | :------------------------------------------------------------ |
| `make up-all`                   | Menjalankan seluruh stack layanan lokal (App + Redis + Logto) |
| `make down-all`                 | Menghentikan seluruh stack layanan                            |
| `make up`                       | Menjalankan Backend (9501) & Frontend (3000)                  |
| `make down`                     | Menghentikan Backend & Frontend                               |
| `make logs`                     | Menampilkan log real-time aplikasi                            |
| `make artisan CMD="<command>"`  | Menjalankan perintah artisan Hypervel                         |
| `make composer CMD="<command>"` | Menjalankan composer dependency manager                       |
| `make test-backend`             | Menjalankan test suite backend (`co-phpunit`)                 |
| `make test-frontend`            | Menjalankan static typecheck & generate frontend              |
| `make test`                     | Menjalankan verifikasi penuh kualitas (Backend + Frontend)    |
| `make frontend-build`           | Melakukan build static SPA frontend untuk deployment          |

---

## 🔒 Standar Keamanan & Tata Kelola (Security Governance)

Sistem menerapkan prinsip **Defense-in-Depth** dan **Decoupled Architecture Pipeline**:

1. **Hierarchy & Password Policy Engine**:
   - `Superadmin`: Memiliki hak penuh atas reset kata sandi seluruh tier akun.
   - `Admin`: Dibatasi hanya untuk mengelola dan mereset kata sandi akun ber-role `User` reguler.
   - `Anti-Self-Reset Guard`: Mencegah eskalasi hak akses atau kesalahan reset akun sendiri via endpoint admin.
2. **Immutable Audit Trail**:
   - Pencatatan permanen pada `password_change_logs` dan `user_sign_in_logs`.
   - Isolasi investigasi audit via `AuditPolicy` (Admin hanya melihat log user biasa; Superadmin melihat global cluster).
3. **Logto Real-Time Webhook Consumer**:
   - Verifikasi tanda tangan kriptografis HMAC SHA-256 (`logto-signature-sha256`) pada payload raw stream.
   - Sinkronisasi asynchronous non-blocking event di lingkungan Coroutine.
4. **Isolasi Lingkungan**:
   - DDL dan migrasi development hanya dieksekusi di database lokal (`postgres:5433`).

---

## 🧪 Status Pengujian & Kualitas

```text
Backend Test Suite:      122 Tests, 529 Assertions (100% Passed)
PHPStan Static Analysis: Level Strict, 98 Files (0 Errors)
Frontend TypeScript:     Nuxt TypeCheck (0 Errors)
Frontend Prerender:      10 Routes Prerendered (100% Success)
```

---

_Dikembangkan dengan penuh dedikasi untuk ekosistem BangLipai Identity._ 🛡️✨
