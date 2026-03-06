# Sistem Manajemen Akun BangLipai

Sistem manajemen akun komprehensif dengan integrasi Single Sign-On (SSO), kontrol akses berbasis peran, dan fitur manajemen password yang canggih.

## 🚀 Fitur

### Autentikasi & Otorisasi
- **Integrasi SSO**: Autentikasi seamless melalui BangLipai Secure Portal (Logto)
- **Kontrol Akses Berbasis Peran**: Sistem peran tiga tingkat (Superadmin, Admin Account, User)
- **Manajemen Sesi**: Penanganan sesi aman dengan pelacakan aktivitas sign-in

### Manajemen User
- **Operasi CRUD**: Manajemen lifecycle user yang lengkap
- **Filter Lanjutan**: Cari dan filter user berdasarkan peran, nama, atau email
- **Profil User**: Profil user komprehensif dengan informasi kontak
- **Dukungan Avatar**: Integrasi foto profil
- **Pelacakan Aktivitas**: Riwayat sign-in dengan IP, browser, dan informasi perangkat

### Manajemen Password
- **Ganti Password Langsung**: User dapat mengganti password dengan verifikasi password saat ini
- **Reset Password Admin**: Administrator dapat reset password user dengan otorisasi yang tepat
- **Penegakan Hierarki Peran**: 
  - Superadmin dapat reset password siapa saja
  - Admin Account hanya dapat reset password User biasa
  - Proteksi terhadap lateral dan privilege escalation
- **Audit Logging Komprehensif**: Setiap perubahan password dicatat dengan:
  - Siapa yang mengubah password siapa
  - IP address dan user agent
  - Timestamp dan alasan (untuk admin reset)
  - Metode (email link atau perubahan langsung)

### UI/UX Modern
- **Desain Responsif**: Pendekatan mobile-first dengan Tailwind CSS
- **Dark Mode**: Dukungan dark mode penuh
- **Komponen Interaktif**: 
  - Password strength meter dengan feedback real-time
  - Accordion sections yang bisa di-collapse
  - Sticky sidebar untuk navigasi yang lebih baik
- **Validasi Form**: Validasi client-side dengan feedback visual
- **Toast Notifications**: Pesan sukses/error untuk semua aksi

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 12.x
- **Language**: PHP 8.3+
- **Database**: MySQL 8.0+ / PostgreSQL 14+
- **Authentication**: Logto SSO
- **API Client**: Guzzle HTTP

### Frontend
- **Framework**: Vue 3 with Composition API
- **Language**: TypeScript
- **Build Tool**: Vite
- **Routing**: Inertia.js
- **Styling**: Tailwind CSS 3
- **Icons**: Lucide Vue Next

### Development Tools
- **Code Quality**: PHP Stan, Laravel Pint
- **Testing**: Pest PHP
- **Package Manager**: Composer, PNPM

## 📋 Prasyarat

- PHP >= 8.2
- Composer
- Node.js >= 20.x
- PNPM >= 8.x
- MySQL >= 8.0 atau PostgreSQL >= 14
- Logto instance (untuk SSO)

## 🔧 Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/KukuhKKH/account.git
cd account
```

### 2. Install Dependencies
```bash
# Backend dependencies
composer install

# Frontend dependencies
pnpm install
```

### 3. Konfigurasi Environment
```bash
# Copy file environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Environment Variables

Edit file `.env` dengan konfigurasi Anda:

```env
# Application
APP_NAME="BangLipai Account System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=account
DB_USERNAME=root
DB_PASSWORD=

# Logto SSO Configuration
LOGTO_ENDPOINT=https://your-logto-instance.com
LOGTO_APP_ID=your_app_id
LOGTO_APP_SECRET=your_app_secret
LOGTO_REDIRECT_URI=http://localhost/auth/callback

# Logto Management API (M2M)
LOGTO_MANAGEMENT_API_RESOURCE=https://your-logto-instance.com/api
LOGTO_MANAGEMENT_API_IDENTIFIER=https://your-logto-instance.com/api
LOGTO_MANAGEMENT_APP_ID=your_m2m_app_id
LOGTO_MANAGEMENT_APP_SECRET=your_m2m_app_secret

# Logto Webhook (Optional)
LOGTO_WEBHOOK_SIGNING_KEY=your_webhook_signing_key
```

### 5. Migrasi Database
```bash
# Jalankan migrasi
php artisan migrate

# (Opsional) Seed database dengan sample data
php artisan db:seed
```

### 6. Build Assets
```bash
# Development
pnpm dev

# Production
pnpm build
```

### 7. Jalankan Development Server
```bash
php artisan serve
```

Buka `http://localhost:8000`

## 📊 Skema Database

### Tabel Users
- Menyimpan informasi user dengan integrasi SSO
- Field: id, name, email, logto_id, avatar, phone, address, last_login_at, custom_data

### Tabel User Roles
- Relasi many-to-many dengan users
- Role yang didukung: Superadmin, Admin Account, User

### Tabel User Sign-In Logs
- Melacak semua aktivitas login user
- Field: user_id, signed_in_at, ip_address, device_info (browser, OS)

### Tabel Password Change Logs
- Audit trail lengkap untuk perubahan password
- Field: user_id, changed_by_user_id, change_type, ip_address, user_agent, reason, via_logto_api, metadata

## 🔐 Fitur Keamanan

### Hierarki Role
```
Superadmin (Tertinggi)
    ↓
Admin Account (Menengah)
    ↓
User (Terendah)
```

### Matriks Permission

| Aksi | Superadmin | Admin Account | User |
|--------|-----------|--------------|------|
| Lihat Semua User | ✅ | ✅ | ❌ |
| Buat User | ✅ | ✅ | ❌ |
| Edit User Apapun | ✅ | ✅ | ❌ |
| Hapus User | ✅ | ✅ | ❌ |
| Reset Password Superadmin | ✅ | ❌ | ❌ |
| Reset Password Admin | ✅ | ❌ | ❌ |
| Reset Password User | ✅ | ✅ | ❌ |
| Ganti Password Sendiri | ✅ | ✅ | ✅ |
| Lihat Profil Sendiri | ✅ | ✅ | ✅ |

### Metode Perubahan Password

1. **User Self-Service** (`/profile`)
   - Memerlukan verifikasi password saat ini
   - Perubahan password langsung melalui BangLipai Secure Portal
   - Peringatan tentang dampak ke semua layanan terintegrasi

2. **Admin Password Reset** (`/users/{id}/reset-password`)
   - Memerlukan role admin/superadmin
   - Menerapkan hierarki role
   - Field alasan wajib untuk audit trail
   - Checkbox konfirmasi diperlukan

## 🚦 Routes

### Authentication
```
GET  /auth/login          - Redirect ke Logto login
GET  /auth/callback       - OAuth callback handler
POST /auth/logout         - Logout user saat ini
```

### User Management
```
GET    /users              - Daftar semua user (admin only)
GET    /users/create       - Tampilkan form buat user (admin only)
POST   /users              - Buat user baru (admin only)
GET    /users/{id}         - Tampilkan detail user (admin only)
GET    /users/{id}/edit    - Form edit user (admin only)
PUT    /users/{id}         - Update user (admin only)
DELETE /users/{id}         - Hapus user (admin only)
```

### Password Management
```
GET  /users/{id}/reset-password      - Tampilkan form admin reset (admin only)
POST /users/{id}/reset-password      - Eksekusi admin reset (admin only)
POST /profile/password/change        - User ganti password sendiri
```

### Profile
```
GET /profile               - Lihat profil sendiri
```

### Webhooks
```
POST /webhooks/logto       - Logto event webhook handler
```

## 🎨 Komponen UI

### Komponen yang Dapat Digunakan Ulang
- `FormInput.vue` - Input field dengan feedback validasi
- `PasswordInput.vue` - Field password dengan indikator kekuatan
- `PasswordSecurityCard.vue` - Bagian manajemen password yang bisa di-collapse

### Composables
- `useAuth.ts` - Helper authentication dengan pengecekan role
- `useFormValidation.ts` - Utilitas validasi form

## 📝 Standar Kualitas Kode

### Standar PHP
- PSR-12 code style
- Type hints untuk semua parameter dan return method
- PHPDoc block yang komprehensif
- Tidak ada static method di custom model logic (gunakan instance)

### Standar TypeScript
- Strict mode diaktifkan
- Type definitions untuk semua props dan events
- Composition API lebih diutamakan daripada Options API

### Dokumentasi
- Semua model memiliki anotasi `@property` dan `@method` yang lengkap
- Semua service method memiliki dokumentasi detail `@param`, `@return`, dan `@throws`
- Inline comment untuk business logic yang kompleks

## 🧪 Testing

```bash
# Jalankan semua test
php artisan test

# Jalankan specific test suite
php artisan test --testsuite=Feature

# Jalankan dengan coverage
php artisan test --coverage
```

## 🚀 Deployment

### Production Build
```bash
# Build frontend assets
pnpm build

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Konfigurasi Environment
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Konfigurasi database credentials yang sesuai
- Set up queue workers untuk background jobs
- Konfigurasi session dan cache drivers (Redis recommended)

## 🔄 Maintenance

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Database Backup
```bash
# Export database
php artisan db:backup

# Atau gunakan mysqldump
mysqldump -u root -p account > backup.sql
```

## 🤝 Contributing

1. Fork repository ini
2. Buat feature branch (`git checkout -b feature/FiturKeren`)
3. Commit perubahan Anda (`git commit -m 'Tambah fitur keren'`)
4. Push ke branch (`git push origin feature/FiturKeren`)
5. Buka Pull Request

### Code Style
```bash
# Format kode dengan Laravel Pint
./vendor/bin/pint

# Jalankan static analysis
./vendor/bin/phpstan analyse
```

## 📄 License

Project ini adalah proprietary software. All rights reserved.

## 👥 Support

Untuk dukungan dan pertanyaan:
- Email: kukuh.developer@gmail.com
- Issue Tracker: GitHub Issues

## 🙏 Acknowledgments

- Laravel Framework
- Vue.js Team
- Logto SSO Platform
- Tailwind CSS
- Semua open-source contributors

---

**Built with ❤️ by BangLipai Development Team**
