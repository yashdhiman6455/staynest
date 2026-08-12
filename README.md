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

