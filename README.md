# Metinca - Modular Enterprise Application

Aplikasi enterprise modular berbasis Laravel dengan arsitektur **Modular Monolith**, **Repository-Service Pattern**, dan **Role-Based Access Control (RBAC)**.

## Tentang Project

Metinca adalah platform aplikasi enterprise yang dirancang untuk mengelola berbagai modul bisnis dalam satu sistem terintegrasi. Setiap modul bersifat independen namun dapat saling berkomunikasi.

### Fitur Utama

- **Modular Architecture** - Setiap fitur dipisah menjadi modul independen
- **Single Sign-On** - Satu login untuk semua modul
- **RBAC dengan Spatie** - Role & Permission management yang fleksibel
- **Repository Pattern** - Abstraksi akses database yang clean
- **Service Pattern** - Business logic terpisah dari controller
- **Module-Specific Dashboard** - Setiap modul punya dashboard sendiri

### Modul yang Tersedia

| Modul | Deskripsi | Status |
|-------|-----------|--------|
| **Repair** | Manajemen laporan kerusakan mesin | ✅ Active |
| *Inventory* | Manajemen stok dan produk | 📋 Planned |
| *HR* | Human Resources management | 📋 Planned |

## Tech Stack

- **Framework**: Laravel 12.x
- **PHP**: >= 8.4
- **Database**: MySQL 8.x
- **RBAC**: Spatie Laravel Permission
- **Export**: Maatwebsite Excel
- **Frontend**: Bootstrap 5 + Blade Templates
- **Icons**: Bootstrap Icons

## Arsitektur

```
┌─────────────────────────────────────────────────────────────┐
│                        PRESENTATION                          │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐        │
│  │  Views  │  │ Routes  │  │  Auth   │  │Middleware│        │
│  └────┬────┘  └────┬────┘  └────┬────┘  └────┬────┘        │
└───────┼────────────┼────────────┼────────────┼──────────────┘
        │            │            │            │
┌───────▼────────────▼────────────▼────────────▼──────────────┐
│                       CONTROLLERS                            │
│            (Thin - hanya routing & validation)               │
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────┐
│                        SERVICES                              │
│              (Business Logic & Orchestration)                │
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────┐
│                      REPOSITORIES                            │
│                 (Data Access Abstraction)                    │
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────┐
│                        MODELS                                │
│              (Eloquent ORM + Relationships)                  │
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────┐
│                       DATABASE                               │
│                        (MySQL)                               │
└─────────────────────────────────────────────────────────────┘
```

## Struktur Direktori

```
metinca/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Main app controllers
│   │   └── Middleware/         # Global middleware
│   ├── Models/                 # Global models (User)
│   ├── Modules/                # ⭐ MODULAR STRUCTURE
│   │   ├── Core/               # Base classes & interfaces
│   │   │   ├── Contracts/
│   │   │   └── Repositories/
│   │   └── Repair/             # Repair Module
│   │       ├── Controllers/
│   │       ├── Models/
│   │       ├── Repositories/
│   │       ├── Services/
│   │       ├── Resources/views/
│   │       ├── Routes/
│   │       └── Providers/
│   └── Providers/
├── config/
│   └── modules.php             # Module registration
├── database/
│   ├── migrations/
│   └── seeders/
├── docs/                       # ⭐ DOKUMENTASI DEVELOPER
│   └── PANDUAN_PENGEMBANGAN_MODUL.md
├── public/
├── resources/
│   └── views/
│       ├── layouts/
│       ├── auth/
│       └── dashboard.blade.php
└── routes/
    └── web.php
```

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/your-org/metinca.git
cd metinca/backend-new
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi Database

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=metinca_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Jalankan Migration & Seeder

```bash
php artisan migrate
php artisan db:seed
```

### 6. Setup Storage Link

```bash
php artisan storage:link
```

### 7. Jalankan Aplikasi

```bash
php artisan serve
```

Buka `http://localhost:8000`

## Test Users

Setelah menjalankan seeder, tersedia user-user berikut untuk testing:

| Email | Password | Role | Akses |
|-------|----------|------|-------|
| `super@metinca.local` | `password` | Super Admin | Semua modul |
| `reporter@metinca.local` | `password` | Repair User | View & Create report |
| `tech@metinca.local` | `password` | Repair Technician | Update status |
| `supervisor@metinca.local` | `password` | Repair Supervisor | Full repair access |
| `manager@metinca.local` | `password` | Repair Manager | Full repair access |

## Alur Penggunaan

```
1. Buka Homepage (/)
       │
       ▼
2. Klik modul (contoh: "Repair")
       │
       ▼
3. Redirect ke Login (/login?module=repair)
       │
       ▼
4. Login dengan credentials
       │
       ▼
5. Redirect ke Module Dashboard (/repair)
       │
       ▼
6. Akses fitur sesuai permission
```

## RBAC (Role-Based Access Control)

### Struktur Permission

Format: `[module].[action]`

```
repair.view          - Lihat laporan
repair.create        - Buat laporan baru
repair.update        - Edit laporan
repair.delete        - Hapus laporan
repair.export        - Export ke Excel
repair.assign        - Assign teknisi
repair.update-status - Update status laporan
```

### Hierarki Role

```
super_admin (Global)
    │
    └── Akses ke SEMUA modul dan permission

repair.manager
    │
    └── repair.* (semua permission repair)

repair.supervisor
    │
    └── repair.view, create, update, delete, export, assign, update-status

repair.technician
    │
    └── repair.view, update-status

repair.user
    │
    └── repair.view, create
```

## Dokumentasi Developer

### Panduan Lengkap

Dokumentasi lengkap untuk developer tersedia di:

```
docs/PANDUAN_PENGEMBANGAN_MODUL.md
```

Isi dokumentasi:

1. **Arsitektur Aplikasi** - Penjelasan alur dan struktur
2. **Membuat Modul Baru** - Step-by-step guide
3. **Migration & Model** - Database schema design
4. **Repository Pattern** - Data access layer
5. **Service Pattern** - Business logic layer
6. **Controller** - Request handling
7. **Routes & Middleware** - URL routing dengan permission
8. **Views** - Blade templates
9. **RBAC Setup** - Roles & Permissions
10. **Best Practices** - Do's and Don'ts
11. **Contoh Lengkap** - Membuat modul Inventory

### Quick Start untuk Developer Baru

```bash
# 1. Baca dokumentasi
cat docs/PANDUAN_PENGEMBANGAN_MODUL.md

# 2. Pahami struktur modul existing
ls -la app/Modules/Repair/

# 3. Lihat contoh model
cat app/Modules/Repair/Models/DamageReport.php

# 4. Lihat contoh service
cat app/Modules/Repair/Services/DamageReportService.php

# 5. Lihat contoh controller
cat app/Modules/Repair/Controllers/DamageReportController.php
```

## Perintah Artisan Berguna

```bash
# Clear semua cache
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear

# Lihat daftar route
php artisan route:list

# Lihat route untuk modul tertentu
php artisan route:list --path=repair

# Jalankan seeder tertentu
php artisan db:seed --class=RolesAndPermissionsSeeder

# Fresh migration (HATI-HATI: hapus semua data)
php artisan migrate:fresh --seed

# Tinker untuk testing
php artisan tinker
```

### Testing di Tinker

```php
// Cek user permissions
$user = User::where('email', 'tech@metinca.local')->first();
$user->can('repair.view'); // true
$user->can('repair.delete'); // false

// Cek accessible modules
$user->accessibleModules(); // ['repair']

// Test service
$service = app(DamageReportServiceInterface::class);
$service->getFilteredReports([], $user);
```

## Development Workflow

### Membuat Fitur Baru

```bash
# 1. Buat branch baru
git checkout -b feature/nama-fitur

# 2. Develop fitur (ikuti panduan di docs/)

# 3. Test manual

# 4. Commit
git add .
git commit -m "feat(module): deskripsi fitur"

# 5. Push & create PR
git push origin feature/nama-fitur
```

### Commit Message Convention

```
feat(repair): add export to PDF feature
fix(auth): resolve login redirect issue
docs(readme): update installation guide
refactor(inventory): simplify stock calculation
```

## Troubleshooting

### Error: Permission Denied (Storage)

```bash
chmod -R 775 storage bootstrap/cache
```

### Error: Class Not Found

```bash
composer dump-autoload
php artisan config:clear
```

### Error: Route Not Found

```bash
php artisan route:clear
php artisan cache:clear
```

### Error: View Not Found

```bash
php artisan view:clear
# Pastikan namespace view benar: 'repair::dashboard'
```

### Error: Permission Denied (403)

```php
// Cek di tinker apakah user punya permission
$user = auth()->user();
$user->can('repair.view');
$user->getAllPermissions()->pluck('name');
```

## Kontribusi

1. Fork repository
2. Buat branch feature (`git checkout -b feature/AmazingFeature`)
3. Ikuti coding standards dan dokumentasi
4. Commit dengan conventional commits
5. Push dan buat Pull Request

## Tim Development

- **Project Lead**: [Nama]
- **Backend Developer**: [Nama]
- **Frontend Developer**: [Nama]

## Lisensi

Project ini bersifat proprietary untuk PT. Metinca.

---

## Quick Links

| Resource | Link |
|----------|------|
| Dokumentasi Developer | [docs/PANDUAN_PENGEMBANGAN_MODUL.md](docs/PANDUAN_PENGEMBANGAN_MODUL.md) |
| Laravel Docs | [laravel.com/docs](https://laravel.com/docs) |
| Spatie Permission | [spatie.be/docs/laravel-permission](https://spatie.be/docs/laravel-permission) |

---

**Happy Coding!** 🚀
