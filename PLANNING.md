# 📋 Blueprint & Rencana Migrasi: BangLipai Account / Identity System

Dokumen ini berisi analisis mendalam (*breakdown*) dari sistem akun eksisting di `/home/kukuh/Koding/account` serta peta jalan migrasi (*rewrite roadmap*) menuju arsitektur terpisah (*Decoupled Backend & Frontend*):
- **Backend**: [Hypervel](https://hypervel.org) (High-Performance Coroutine PHP Framework bertenaga Swoole & Hyperf)
- **Frontend**: [Nuxt 4](https://nuxt.com) + [PrimeVue v5](https://primevue.org) + [Tailwind CSS v4](https://tailwindcss.com)

---

## 🔍 1. Breakdown Project Account Eksisting (`/home/kukuh/Koding/account`)

### 1.1 Arsitektur Lama
* **Pola**: Monolitik SPA berbasis **Laravel 12.x + Inertia.js (Vue 3)**.
* **Autentikasi**: BangLipai SSO (OIDC/OAuth2 Authorization Code flow dengan session cookie Laravel).
* **Manajemen User**: Integrasi ganda (Local Database MySQL/PostgreSQL + Sinkronisasi Logto Management API / M2M).
* **Styling**: Tailwind CSS + shadcn-vue/lucide icons kustom.

### 1.2 Skema Database & Model Data
1. **`users`**:
   - Kolom: `id`, `name`, `email`, `password`, `logto_id`, `avatar`, `phone`, `address`, `last_login_at`, `custom_data` (JSON), timestamps.
2. **`user_roles`**:
   - Kolom: `id`, `user_id`, `role` (`Superadmin`, `Admin Account`, `User`), timestamps.
3. **`user_sign_in_logs`**:
   - Kolom: `id`, `user_id`, `signed_in_at`, `ip_address`, `device_info` (User-Agent parsed: browser, OS, device).
4. **`password_change_logs`**:
   - Kolom: `id`, `user_id`, `changed_by_user_id`, `change_type` (`self_change`, `admin_reset`, `reset_link`), `ip_address`, `user_agent`, `reason`, `via_logto_api`, `metadata` (JSON), timestamps.

### 1.3 Core Services & Business Logic
* **`LogtoService`**:
  - Manajemen token M2M (Management API Token caching & renewal).
  - Operasi API Logto: Get Users, Create User, Update User, Delete User, Assign Roles, Reset Password / Generate Reset Ticket.
* **`UserService`**:
  - Sinkronisasi user lokal saat login pertama atau perubahan data.
  - CRUD user lokal + pencatatan aktivitas login.
* **`PasswordChangeService`**:
  - Penegakan hierarki otorisasi reset password:
    - `Superadmin` -> dapat mereset siapa saja.
    - `Admin Account` -> hanya dapat mereset `User`.
    - `User` -> hanya dapat mengganti password miliknya sendiri (dengan verifikasi current password).
  - Audit logging komprehensif ke tabel `password_change_logs`.
* **`WebhookHandler` & `WebhookController`**:
  - Menangani webhook event dari Logto (`User.Created`, `User.Data.Updated`, `User.Deleted`, `User.Signed_in`).
  - Verifikasi signature webhook Logto (`verify.logto.webhook`).

---

## 🚀 2. Target Arsitektur Baru

```
                               ┌─────────────────────────────┐
                               │       BangLipai SSO         │
                               └──────────────┬──────────────┘
                                              │ OAuth2 / M2M API / Webhooks
               ┌──────────────────────────────┴──────────────────────────────┐
               ▼                                                             ▼
┌──────────────────────────────┐                                ┌───────────────────────────────┐
│       Frontend (Nuxt 4)      │ ◄────── REST API (JSON) ─────► │       Backend                 │
│  - PrimeVue v5 (Aura Theme)  │        JWT / Bearer Auth       │  - PHP 8.3+ Coroutine (Swoole)│
│  - Tailwind CSS v4           │                                │  - Connection Pool (DB/Redis) │
│  - Pinia & VueUse            │                                │  - Logto M2M + Webhooks API   │
│  - Responsive & Dark Mode    │                                │  - Strict Audit Logging       │
└──────────────────────────────┘                                └──────────────┬────────────────┘
                                                                               │
                                                                ┌──────────────▼───────────────┐
                                                                │  Database (Postgres / MySQL) │
                                                                └──────────────────────────────┘
```

### Keunggulan Stack Baru:
1. **Ultra High Performance (Hypervel + Swoole)**:
   - *Non-blocking I/O* & *Coroutine engine* membuat request ke database dan eksternal Logto API berjalan secepat kilat tanpa bottleneck thread PHP-FPM konvensional.
   - Built-in connection pool untuk database dan Redis.
2. **Modern & Modular Frontend (Nuxt 4 + PrimeVue v5)**:
   - PrimeVue v5 menghadirkan suite komponen enterprise (DataTable dengan multi-sort/filter, Dialogs, Accordion, Toast, Form Validation, InputOtp, PasswordMeter) yang sangat terpoles.
   - Tailwind CSS v4 dengan performa kompilasi instan via Vite.
3. **Decoupled API**:
   - Backend Hypervel murni berfungsi sebagai Headless API & Webhook Service, memudahkan integrasi dengan aplikasi-aplikasi BangLipai lainnya di masa mendatang.

---

## 📅 3. Fase Pengembangan (Roadmap)

### 📌 Fase 1: Inisialisasi & Setup Environment (SELESAI ✅)
- [x] Inisialisasi framework **Hypervel** di direktori `./backend`.
- [x] Inisialisasi framework **Nuxt 4** di direktori `./frontend`.
- [x] Instalasi & Konfigurasi **PrimeVue v5** (`@primevue/nuxt-module`, `@primeuix/themes`) + **Tailwind CSS v4** (`@tailwindcss/vite`) di frontend.
- [x] Pembuatan blueprint & dokumentasi migrasi (`PLANNING.md`).

### 📌 Fase 2: Backend Database & Coroutine Data Layer
- [ ] Setup konfigurasi database Hypervel di `backend/config/database.php` (mendukung MySQL & PostgreSQL).
- [ ] Porting migration tables dari Laravel ke Hypervel:
  - `users`
  - `user_roles`
  - `user_sign_in_logs`
  - `password_change_logs`
- [ ] Implementasi Eloquent Models di Hypervel dengan Coroutine safety & Casts.
- [ ] Pembuatan Database Seeders untuk role inisial & master admin.

### 📌 Fase 3: Integrasi Logto SSO & M2M Service
- [ ] Pembuatan `LogtoService` di Hypervel (menggunakan Hypervel Coroutine HTTP Client / Guzzle Pool):
  - M2M token caching via Redis/Memory pool.
  - User sync & management API wrappers.
  - Password ticket & direct reset wrappers.
- [ ] Implementasi Webhook endpoint `/api/webhooks/logto` dengan verifikasi signature.
- [ ] Implementasi Auth Middleware (JWT Bearer Token verification / SSO Session Guard).

### 📌 Fase 4: Backend RESTful API & RBAC
- [ ] Endpoint `/api/auth/*`:
  - `GET /api/auth/me` (Profil user yang sedang login beserta role).
  - `POST /api/auth/logout`.
- [ ] Endpoint `/api/profile/*`:
  - `GET /api/profile` & `PUT /api/profile` (Update profil/kontak).
  - `POST /api/profile/password/change` (Ganti password mandiri).
  - `GET /api/profile/sign-in-logs` (Riwayat login user).
- [ ] Endpoint Admin `/api/users/*`:
  - `GET /api/users` (List user dengan pagination, dynamic search & role filter).
  - `POST /api/users` (Buat user baru di Logto + DB lokal).
  - `GET /api/users/{id}` (Detail user & log aktivitas).
  - `PUT /api/users/{id}` (Update data user & role).
  - `DELETE /api/users/{id}` (Hapus user di Logto + DB lokal).
  - `POST /api/users/{id}/reset-password` (Admin reset password sesuai hierarki RBAC).
  - `GET /api/audit-logs/passwords` (Audit trail perubahan password).

### 📌 Fase 5: Frontend Nuxt 4 (SPA Mode) + PrimeVue v5 + Tailwind
- [ ] Konfigurasi State Management (Pinia) & Auth Store untuk Logto Token / User Session.
- [ ] Layouts:
  - `default.vue`: Sidebar responsif, Header dengan Dark Mode switch & User Dropdown Menu.
  - `auth.vue`: Halaman login / callback SSO.
- [ ] Pages & Views:
  - `/` & `/dashboard`: Ringkasan statistik akun, quick actions, ringkasan aktivitas terakhir.
  - `/profile`: Form edit profil, riwayat sign-in log card, modal ganti password dengan password meter.
  - `/users`: PrimeVue `DataTable` canggih dengan filtering role/search, pagination, action button (Edit, Delete, Reset Password).
  - `/users/[id]`: Detail user view & activity audit timeline.
- [ ] Komponen Interaktif:
  - Password Strength Indicator.
  - Confirmation Dialog & Toasts untuk setiap mutasi data.

### 📌 Fase 6: Containerization, Testing & Deployment (SELESAI ✅)
- [x] Pipeline CI/CD GitHub Actions mengikuti pattern BangLipai (`.github/workflows/build.yml`, `.github/base/`, `.github/build/`, `.github/deploy/`).
- [x] Konfigurasi Docker local development zero-host dependency di folder `local/` (`docker-compose.yml`, `artisan.sh`, `composer.sh`, `run.sh`).
- [ ] Unit & Feature Testing (Pest/PHPUnit untuk Backend, Vitest untuk Frontend).

---

## 💻 4. Panduan Menjalankan Project di Local (`Makefile`)

Semua interaksi diatur melalui [Makefile](file:///home/kukuh/Koding/identity/Makefile) di root repository:

### 4.1 Manajemen Service (Backend & Frontend)
```bash
make up          # Menjalankan Backend (9501) & Frontend (3000)
make logs        # Melihat live logs
make down        # Menghentikan services
make restart     # Me-restart services
```

### 4.2 Database & Artisan via Container
```bash
make migrate     # Jalankan migrasi database
make seed        # Jalankan database seeders
make route-list  # Cek routing backend
make artisan CMD="make:controller ExampleController"
```

### 4.3 Composer & Frontend
```bash
make composer CMD="require hypervel/redis"   # Install composer package
make frontend-dev                            # Dev server frontend
make frontend-build                          # Generate static SPA output (.output/public)
```
