# 🧠 scholaRSerbisyo Backend API

A Laravel REST API backend with Laravel Sail, Sanctum auth, and Cloudflare R2 integration.

---

## 🚀 Getting Started

### 📦 Install Composer Dependencies

```bash
composer install
```

---

Install sanctum using composer

```bash
composer require laravel/sanctum
```

---

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

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=your-aws-access-key-id
AWS_SECRET_ACCESS_KEY=your-aws-secret-access-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-aws-bucket-name
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_NO_ANALYTICS=false

# Cloudflare R2
CLOUDFLARE_ACCOUNT_ID=your-cloudflare-account-id
CLOUDFLARE_ACCESS_KEY_ID=your-cloudflare-access-key
CLOUDFLARE_SECRET_ACCESS_KEY=your-cloudflare-secret-key
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
Includes:

- AdminTypesSeeder
- EventTypeSeeders
- ScholarTypesSeeder
- UserRolesSeeder

Run individual seeders if needed:

```bash
./vendor/bin/sail artisan db:seed --class=UserRolesSeeder
```

---

## 🔐 Auth & API

- **Sanctum** for API auth
- JWT integration (optional)
- RESTful endpoints for admin, scholar, events, comments, etc.

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
