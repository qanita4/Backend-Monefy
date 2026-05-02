# Monefy Backend API

Backend API untuk aplikasi manajemen keuangan pribadi **Monefy**. Dibangun dengan Laravel 13 dan PostgreSQL sebagai database.

## 📋 Daftar Isi

- [Prasyarat](#prasyarat)
- [Instalasi & Setup](#instalasi--setup)
- [Konfigurasi](#konfigurasi)
- [Menjalankan Project](#menjalankan-project)
- [Testing API dengan Scramble](#testing-api-dengan-scramble)
- [Struktur Project](#struktur-project)
- [Troubleshooting](#troubleshooting)

## 🔧 Prasyarat

Pastikan teman Anda memiliki software berikut yang sudah terinstal di laptop mereka:

- **PHP** versi 8.3 atau lebih tinggi ([Download](https://www.php.net/downloads))
- **Composer** (PHP Package Manager) ([Download](https://getcomposer.org/download/))
- **Node.js & npm** versi 18+ ([Download](https://nodejs.org/))
- **PostgreSQL** versi 12 atau lebih tinggi ([Download](https://www.postgresql.org/download/))
- **Git** ([Download](https://git-scm.com/))
- **Text Editor/IDE** (VSCode, PHPStorm, dll)

Untuk memverifikasi instalasi:

```bash
php --version
composer --version
node --version
npm --version
psql --version
git --version
```

## 📦 Instalasi & Setup

### Step 1: Clone Repository

```bash
cd /lokasi/yang/diinginkan
git clone <repository-url>
cd Backend-Monefy
```

### Step 2: Jalankan Setup Script

Kami sudah menyediakan script otomatis untuk setup. Jalankan command berikut:

```bash
composer run setup
```

Script ini akan secara otomatis:

- 📥 Install semua dependency PHP (composer)
- 🔑 Generate application key
- 🗄️ Jalankan database migrations
- 📦 Install Node dependencies (npm)
- 🏗️ Build asset frontend (Vite)

### Step 3: Setup Database PostgreSQL

Jika belum ada database PostgreSQL, buat database baru:

```bash
# Masuk ke PostgreSQL shell
psql -U postgres

# Jalankan command berikut di dalam PostgreSQL:
CREATE DATABASE monefy_db;
CREATE USER monefy_user WITH PASSWORD 'password_anda';
ALTER ROLE monefy_user SET client_encoding TO 'utf8';
ALTER ROLE monefy_user SET default_transaction_isolation TO 'read committed';
ALTER ROLE monefy_user SET default_transaction_deferrable TO on;
ALTER ROLE monefy_user SET default_transaction_deferrable TO on;
ALTER ROLE monefy_user SET timezone TO 'UTC';
GRANT ALL PRIVILEGES ON DATABASE monefy_db TO monefy_user;
\q
```

## ⚙️ Konfigurasi

### File .env

Setelah setup, edit file `.env` di root project:

```bash
# Buka file .env dengan text editor
# Edit bagian DATABASE

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1          # atau localhost
DB_PORT=5432               # port default PostgreSQL
DB_DATABASE=monefy_db      # nama database Anda
DB_USERNAME=monefy_user    # username PostgreSQL Anda
DB_PASSWORD=password_anda  # password PostgreSQL Anda

# Pastikan bagian APP juga sesuai:
APP_URL=http://localhost:8000
APP_DEBUG=true
```

### Migrasi Database

Jalankan semua database migrations:

```bash
php artisan migrate
```

Jika ada seeder, jalankan:

```bash
php artisan db:seed
```

## 🚀 Menjalankan Project

### Cara 1: Menggunakan Script Development (Rekomendasi)

Menjalankan server Laravel, queue listener, dan Vite dev server secara bersamaan:

```bash
composer run dev
```

Output akan menunjukkan:

- 🖥️ Laravel server berjalan di `http://localhost:8000`
- 👁️ Logs real-time

Tekan `Ctrl + C` untuk menghentikan.

### Cara 2: Menjalankan Manual

Buka 3 terminal terpisah:

**Terminal 1 - Laravel Server:**

```bash
php artisan serve
```

Server akan berjalan di `http://localhost:8000`

**Terminal 2 - Build Assets:**

```bash
npm run dev
```

**Terminal 3 - Queue Listener (Opsional):**

```bash
php artisan queue:listen
```

## � Git & Commit Convention

Untuk menjaga history commit yang rapi dan mudah dipahami, gunakan format commit message sebagai berikut:

### Format Commit Message

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Penjelasan:

**Type** - Jenis perubahan (required):

- `feat` - Fitur baru
- `fix` - Bug fix
- `docs` - Perubahan dokumentasi
- `style` - Perubahan formatting (whitespace, semicolon, dll)
- `refactor` - Refactoring code tanpa mengubah functionality
- `test` - Menambah atau update test
- `chore` - Update dependencies, build tools, dll
- `perf` - Improvement performa

**Scope** (optional) - Area/module yang diubah:

- `auth`, `wallet`, `transaction`, `api`, `database`, dll

**Subject** (required) - Deskripsi singkat (max 50 character):

- Gunakan imperative mood ("add" bukan "added" atau "adds")
- Tidak boleh diakhiri dengan titik (.)
- Dimulai dengan huruf kecil

**Body** (optional) - Penjelasan detail:

- Jelaskan WHY bukan WHAT
- Pisahkan dari subject dengan blank line
- Wrap di 72 character per line

**Footer** (optional) - Referensi issue:

- `Closes #123`
- `Fixes #456`
- `Related to #789`

### ✅ Contoh Commit Message yang Baik:

#### 1. Fitur baru

```
feat(auth): add JWT token refresh endpoint

Add automatic token refresh mechanism to prevent session timeout.
User can refresh their token 5 minutes before expiration.

Closes #42
```

#### 2. Bug fix

```
fix(wallet): resolve balance calculation error

Balance was not updated correctly when multiple transactions
occur in the same minute. Fixed by using transaction locking.

Fixes #85
```

#### 3. Dokumentasi

```
docs: update API documentation for auth endpoints

Add examples for bearer token usage and refresh token flow.
```

#### 4. Refactor

```
refactor(transaction): extract validation logic to service class

Move validation logic from controller to TransactionService
for better code reusability and testability.
```

#### 5. Test

```
test(wallet): add unit tests for balance calculation
```

#### 6. Chore

```
chore: update laravel framework to v13.1
```

### ❌ Contoh yang TIDAK BAIK:

```
✗ fixed bug                    # Tidak deskriptif
✗ Fix                          # Terlalu singkat
✗ Added new feature to auth    # Word "Added" (imperative: "Add")
✗ wip                          # Tidak jelas
✗ asdasd                       # Random text
✗ Update code.                 # Terlalu generic dengan titik
```

### 💡 Quick Tips:

1. **Commit frequently** - commit setiap ada perubahan logical yang selesai
2. **Single responsibility** - 1 commit = 1 fitur/fix
3. **Test before commit** - pastikan code berjalan sebelum commit
4. **Review yourself** - baca commit message sebelum push

---

## �📚 Testing API dengan Scramble

Scramble adalah dokumentasi API otomatis untuk Laravel. Documentasi API sudah di-generate otomatis berdasarkan code Anda.

### Akses Dokumentasi Scramble

1. Pastikan server Laravel sedang berjalan (`php artisan serve`)
2. Buka browser dan pergi ke:
    ```
    http://localhost:8000/api/documentation
    ```

Anda akan melihat dokumentasi interaktif lengkap dengan semua endpoint API.

### Cara Test API di Scramble

#### 1. **Authentication - Register User**

**Endpoint:** `POST /api/register`

Di Scramble interface:

1. Cari endpoint "POST /register" di sidebar
2. Klik endpoint tersebut
3. Di panel kanan, isi form dengan:
    ```json
    {
        "name": "John Doe",
        "email": "john@example.com",
        "password": "password123",
        "password_confirmation": "password123"
    }
    ```
4. Klik tombol "Try it out" atau "Execute"
5. Respons akan menampilkan token dan user data

**Contoh Response:**

```json
{
    "token": "1|abc123token...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

#### 2. **Authentication - Login**

**Endpoint:** `POST /api/login`

1. Cari endpoint "POST /login"
2. Isi request body:
    ```json
    {
        "email": "john@example.com",
        "password": "password123"
    }
    ```
3. Klik "Execute"
4. Copy token dari response

**Contoh Response:**

```json
{
    "token": "2|xyz789token...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

#### 3. **Menggunakan Token untuk Endpoint Terproteksi**

Endpoint yang membutuhkan autentikasi (ada "auth:sanctum") memerlukan token:

1. Di Scramble interface, cari tombol "Authorize" atau lock icon (🔒) di atas
2. Klik untuk membuka Authorization dialog
3. Pilih "Bearer Token"
4. Paste token yang Anda dapatkan dari login:
    ```
    1|abc123token...
    ```
5. Klik "Authorize" atau "Submit"

Sekarang semua endpoint terproteksi bisa diakses dengan token tersebut.

#### 4. **Test Endpoint: Buat Wallet**

**Endpoint:** `POST /api/wallets`

Request body:

```json
{
    "name": "Rekening Tabungan",
    "balance": 5000000,
    "currency": "IDR"
}
```

Response:

```json
{
    "id": 1,
    "user_id": 1,
    "name": "Rekening Tabungan",
    "balance": 5000000,
    "currency": "IDR",
    "created_at": "2026-05-02T10:30:00Z"
}
```

#### 5. **Test Endpoint: Buat Transaction**

**Endpoint:** `POST /api/transactions`

Request body:

```json
{
    "wallet_id": 1,
    "type": "expense",
    "category": "food",
    "amount": 50000,
    "description": "Makan siang",
    "transaction_date": "2026-05-02"
}
```

#### 6. **Test Endpoint: Get Transactions**

**Endpoint:** `GET /api/transactions`

Tidak perlu request body, hanya pastikan token sudah di-set. Response akan menampilkan semua transactions user.

#### 7. **Test Endpoint: Get Dashboard Summary**

**Endpoint:** `GET /api/dashboard/summary`

Response:

```json
{
  "total_balance": 5000000,
  "total_income": 0,
  "total_expense": 0,
  "recent_transactions": [...]
}
```

### Tips & Tricks untuk Testing

1. **Copy cURL Command**: Di Scramble, ada tombol untuk copy command dalam format cURL, berguna untuk testing di terminal:

    ```bash
    curl -X POST http://localhost:8000/api/login \
      -H "Content-Type: application/json" \
      -d '{"email":"john@example.com","password":"password123"}'
    ```

2. **Test dengan Tools Alternatif**: Jika lebih nyaman, bisa gunakan Postman atau Insomnia:
    - Import collections dari Scramble
    - Atau setup manual dengan base URL `http://localhost:8000/api`

3. **Debug Response**: Jika ada error, perhatikan:
    - Status code (200, 401, 422, 500, dll)
    - Error message di response
    - Check file di `storage/logs/laravel.log` untuk detail error

## 📂 Struktur Project

```
Backend-Monefy/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/           # API Controllers
│   │   ├── Middleware/         # Authentication middleware
│   │   └── Requests/           # Form request validation
│   ├── Models/                 # Eloquent models (User, Wallet, Transaction)
│   └── Providers/
├── database/
│   ├── migrations/             # Database migrations
│   ├── seeders/                # Database seeders
│   └── factories/              # Model factories untuk testing
├── routes/
│   ├── api.php                 # API routes
│   └── web.php
├── tests/                      # Unit & Feature tests
├── storage/
│   └── logs/                   # Application logs
├── .env.example                # Environment template
├── composer.json               # PHP dependencies
└── package.json                # Node dependencies
```

## 🔍 Troubleshooting

### 1. **Error: "SQLSTATE[HY000] [2002] No such file or directory"**

Masalah: Database connection error
Solusi:

```bash
# Cek PostgreSQL sudah running:
# Windows: lihat di Services atau psql -U postgres
# macOS: brew services list | grep postgres
# Linux: sudo systemctl status postgresql

# Verifikasi .env sudah benar:
grep "^DB_" .env
```

### 2. **Error: "Composer dependency not found"**

Solusi:

```bash
# Delete lock file dan install ulang
rm composer.lock
composer install --no-dev
```

### 3. **Laravel Key Not Generated**

```bash
php artisan key:generate
```

### 4. **NPM/Node Issues**

```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules dan install ulang
rm -rf node_modules package-lock.json
npm install
```

### 5. **Port 8000 Sudah Digunakan**

```bash
# Jalankan di port lain
php artisan serve --port=8001
```

### 6. **Dokumentasi Scramble Tidak Muncul**

Pastikan:

```bash
# 1. Cek APP_URL di .env
grep "APP_URL" .env

# 2. Clear cache
php artisan cache:clear
php artisan config:cache

# 3. Jalankan generate documentation (jika ada)
php artisan scramble:document
```

## 📞 Support & Bantuan Tambahan

Jika teman Anda menemui masalah:

1. **Check Laravel Logs:**

    ```bash
    tail -f storage/logs/laravel.log
    ```

2. **Cek Database Connection:**

    ```bash
    php artisan tinker
    >>> DB::connection()->getPDO();  // Harus berhasil
    ```

3. **Reset Database (Hati-hati!):**
    ```bash
    php artisan migrate:reset
    php artisan migrate
    ```

---

**Happy coding! 🎉**

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
