# StayNest

**Find a place you'll love to stay.**

StayNest is a polished, responsive property listing platform built with **Laravel 11** and **Vue 3**. Hosts can list their spaces, travellers can search and filter stays, and everyone gets a fast, mobile-friendly experience backed by a clean REST API.

> This is an assignment-focused implementation. The scope is deliberately limited to core property listing features — no bookings, payments, reviews, chat or admin panels.

---

## Table of Contents

- [Features](#features)
- [Technology Stack](#technology-stack)
- [Getting Started](#getting-started)
  - [Requirements](#requirements)
  - [Installation](#installation)
  - [Environment Setup](#environment-setup)
  - [Database Setup](#database-setup)
  - [Seed Data](#seed-data)
  - [Run Laravel](#run-laravel)
  - [Run Vue (Vite)](#run-vue-vite)
- [Demo Credentials](#demo-credentials)
- [API Overview](#api-overview)
- [Frontend Structure](#frontend-structure)
- [Deployment](#deployment)
- [Testing](#testing)
- [License](#license)

---

## Features

**Public browsing**

- Home page with a modern hero, search bar and featured property grid
- Explore page listing all published properties with responsive grid + pagination
- Property details page with image, description, stats and host info
- Backend-driven search & filters: location, property type, min/max price, guests
- Clear filters, empty-result state, loading state and friendly error states

**User accounts**

- Register, login and logout with Laravel Sanctum (token-based)
- Validation with clear, inline error messages
- Logged-in users see their name in the navbar

**Hosting**

- Add a property with image upload (stored on the public disk)
- My Properties dashboard to view, edit and delete your own listings
- Edit and delete restricted to the property owner via a Laravel policy (403 otherwise)

## Technology Stack

| Layer      | Technology                                             |
| ---------- | ------------------------------------------------------ |
| Backend    | PHP 8.2+, Laravel 11, Laravel Sanctum, Eloquent ORM    |
| Database   | MySQL                                                  |
| Frontend   | Vue 3 (Composition API), Vue Router, Pinia, Axios      |
| Tooling    | Vite, Tailwind CSS                                     |
| Testing    | PHPUnit (feature tests)                                |

---

## Getting Started

### Requirements

- PHP **8.2 or higher** (with the `pdo_mysql`, `fileinfo`, `gd` or `imagick` extensions)
- Composer
- Node.js **18+** and npm
- MySQL **5.7+ / 8.x**

### Installation

Clone the repository and install the PHP and JavaScript dependencies:

```bash
git clone https://github.com/yashdhiman6455/staynest.git
cd staynest

composer install
npm install
```

### Environment Setup

Copy the example environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and configure your database and URLs:

```dotenv
APP_NAME=StayNest
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=staynest
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public

CORS_ALLOWED_ORIGINS=http://localhost:5173
```

> **CORS**: keep `http://localhost:5173` (the Vite dev server) in `CORS_ALLOWED_ORIGINS` during development. In production the SPA is served by Laravel on the same origin, so the API is same-origin and no CORS entry is needed.
>
> **API URL**: `VITE_API_URL` is empty by default, which makes Axios call the API relative to the app origin (`/api/v1`). Set it to an absolute URL only if the API is hosted separately.

### Database Setup

Create the database in MySQL:

```sql
CREATE DATABASE staynest CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Run the migrations:

```bash
php artisan migrate
```

Link the public storage directory so uploaded property images are accessible:

```bash
php artisan storage:link
```

### Seed Data

Seed the database with **3 demo users** and **15 properties** (14 published, 1 draft) including images:

```bash
php artisan db:seed
```

> Rebuild the database from scratch with `php artisan migrate:fresh --seed`.

### Run Laravel

Start the API / backend on `http://localhost:8000`:

```bash
php artisan serve
```

### Run Vue (Vite)

Start the Vite dev server on `http://localhost:5173` (it proxies `/api` and `/storage` to Laravel):

```bash
npm run dev
```

Open **http://localhost:5173** in your browser.

For a production build, run `npm run build` — the compiled assets land in `public/build` and are served automatically by Laravel.

---

## Demo Credentials

| Name         | Email               | Password  |
| ------------ | ------------------- | --------- |
| Yash Dhiman  | yash@staynest.test  | password  |
| Priya Sharma | priya@staynest.test | password  |
| Amit Verma   | amit@staynest.test  | password  |

All three seeded users own several properties, so you can log in as any of them to test "My Properties", editing, and deleting. Attempting to edit another user's property returns **403 Forbidden**.

---

## API Overview

Base URL: `http://localhost:8000/api/v1`

All responses share a consistent envelope:

```json
{
    "success": true,
    "message": "Properties retrieved successfully.",
    "data": [],
    "meta": { "current_page": 1, "last_page": 1, "per_page": 12, "total": 0 }
}
```

### Authentication

| Method | Endpoint                    | Description                       |
| ------ | --------------------------- | --------------------------------- |
| POST   | `/api/v1/register`          | Register a new user               |
| POST   | `/api/v1/login`             | Log in and receive a Bearer token |
| POST   | `/api/v1/logout`            | Revoke the current token          |
| GET    | `/api/v1/user`              | Get the authenticated user        |

`logout` and `user` require the `Authorization: Bearer <token>` header.

**Register payload**

```json
{
    "name": "Riya Kapoor",
    "email": "riya@staynest.test",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Properties

| Method | Endpoint                          | Auth | Description                          |
| ------ | --------------------------------- | ---- | ------------------------------------ |
| GET    | `/api/v1/properties`              | No   | List published properties (filters)  |
| GET    | `/api/v1/properties/{slug}`       | No   | Single property (draft owner only)   |
| POST   | `/api/v1/properties`              | Yes  | Create a property (multipart form)   |
| PUT    | `/api/v1/properties/{property}`   | Yes  | Update own property                  |
| DELETE | `/api/v1/properties/{property}`   | Yes  | Delete own property                  |
| GET    | `/api/v1/my-properties`           | Yes  | List the authenticated user's stays  |

**Query parameters** (GET `/api/v1/properties`)

| Parameter   | Example                        |
| ----------- | ------------------------------ |
| `location`  | `location=Chandigarh`          |
| `type`      | `type=Apartment`               |
| `min_price` | `min_price=1000`               |
| `max_price` | `max_price=5000`               |
| `guests`    | `guests=4`                     |
| `search`    | `search=lakeview`              |
| `per_page`  | `per_page=12` (max 24)         |
| `page`      | `page=2`                       |

Example: `GET /api/v1/properties?location=Chandigarh&type=Apartment&max_price=5000`

**Create / update payload** — send as `multipart/form-data` with the `image` file field:

```text
title, description, property_type, location, city, country,
price_per_night, guests, bedrooms, bathrooms, image (file, max 2MB)
```

### HTTP Status Codes

| Code | Meaning                                              |
| ---- | ---------------------------------------------------- |
| 200  | Success                                              |
| 201  | Resource created (register / create property)        |
| 401  | Unauthenticated — missing or invalid token           |
| 403  | Forbidden — not the owner of the property            |
| 404  | Property or resource not found                       |
| 422  | Validation failed (errors returned under `errors`)   |

---

## Frontend Structure

```text
resources/js/
├── assets/
├── components/      Navbar, Footer, PropertyCard, SearchBar, FilterPanel,
│                    LoadingSpinner, EmptyState, Pagination, PropertyForm, BrandLogo
├── layouts/         DefaultLayout
├── views/           Home, Properties, PropertyDetails, Login, Register,
│                    CreateProperty, EditProperty, MyProperties, NotFound
├── router/          Vue Router with auth/guest guards
├── stores/          authStore, propertyStore (Pinia)
├── services/        api.js (Axios instance), authService, propertyService
└── utils/           errors, format, properties helpers
```

## Deployment

These steps describe deploying to a typical shared host or VPS with PHP + MySQL. The same flow applies to platforms such as Forge, Heroku or Render.

### 1. Upload the code and install dependencies

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

### 2. Configure the environment

```bash
cp .env.example .env
php artisan key:generate
```

Set production values:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

FILESYSTEM_DISK=public
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

### 3. Migrate, seed and link storage

```bash
php artisan migrate --force
php artisan db:seed --force        # optional demo data
php artisan storage:link
```

### 4. Point the web root at `public/`

The document root must be the Laravel `public/` directory (the SPA index and the `storage` symlink both live there). With Nginx:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/staynest/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$index.php;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```

### 5. Storage configuration

Uploaded property images are stored on the **public** disk (`storage/app/public/properties`). `php artisan storage:link` creates `public/storage` → `storage/app/public`, so images are served at `https://your-domain.com/storage/properties/xxx.jpg`. For a horizontally-scaled setup, swap the disk to S3 by setting `FILESYSTEM_DISK=s3` and configuring the `AWS_*` variables.

> **Security**: never commit `.env`. No secrets are tracked in this repository. The `.env.example` template only contains placeholders.

---

## Testing

The test suite runs against an in-memory SQLite database, so no MySQL setup is needed:

```bash
php artisan test
```

Coverage includes registration/login/logout, property listing with filters, pagination, draft visibility, property create/update/delete, image uploads and ownership policy enforcement (403 responses).

---

## License

StayNest is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


