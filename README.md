# 🧠 scholaRSerbisyo Backend API

A Laravel REST API backend with Laravel Sail, Sanctum auth, and Cloudflare R2 integration.

---

## 🚀 Getting Started

### 📦 Install Composer Dependencies

```bash
composer install
```

### 🐳 Start the Laravel Sail Environment (Docker)

```bash
./vendor/bin/sail up -d
```

Optional (to recreate containers):

```bash
./vendor/bin/sail up --force-recreate -d
```

---

## ⚙️ Environment Setup

Create your `.env` file using `.env.example` as a template.

<details>
<summary>Example .env (click to expand)</summary>

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:your-app-key-here
APP_DEBUG=true
APP_TIMEZONE=Asia/Manila
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

FILESYSTEM_DISK=local

# Cloudflare R2
CLOUDFLARE_ACCOUNT_ID=your-account-id
CLOUDFLARE_ACCESS_KEY_ID=your-access-key
CLOUDFLARE_SECRET_ACCESS_KEY=your-secret-key
CLOUDFLARE_BUCKET_NAME=eventimages
```

</details>

---

## 🔧 Database Setup

### Run Migrations

```bash
./vendor/bin/sail artisan migrate
```

### Run Seeders

Run all:

```bash
./vendor/bin/sail artisan db:seed
```

Or individual:

```bash
./vendor/bin/sail artisan db:seed --class=UserRolesSeeder
```

---

## 🔐 Auth & API

- **Sanctum** for API auth
- JWT integration (optional)
- RESTful endpoints for admin, scholar, events, comments, etc.

---

## 🧪 Running Tests

```bash
./vendor/bin/sail artisan test
```

---

## 📦 Tech Stack

- **Laravel 10**
- **Sail (Docker)**
- **MySQL**
- **Sanctum Auth**
- **REST API**
- **Cloudflare R2 (S3-Compatible Storage)**

---

## 📌 Notes

- Do not commit real credentials; always use `.env.example` for configuration templates.
