# StayNest — Technical Documentation

**A complete, code-accurate explanation of the current StayNest implementation.**

This document describes **only what actually exists** in the repository. Where something is not implemented, it is explicitly labeled **"Not implemented in the current project."**

---

## 1. Project Overview

### 1.1 What StayNest is

StayNest is a **property listing website** built with **Laravel 11** and **Vue 3**. It lets travellers browse, search and filter vacation properties, and lets registered hosts list their own spaces. It is deliberately scoped to core listing features only — no bookings, payments, reviews, chat or admin panels.

### 1.2 The problem it solves

There is no single, simple place where a host can put a property online and a traveller can find it. StayNest provides:

- A browsable catalog of published properties with photos, prices and details.
- Backend-powered search and filtering (location, type, price range, guest count).
- User accounts so hosts can create, edit and delete **their own** listings.
- Ownership enforcement so users can never modify someone else's property.

### 1.3 Main users of the system

| Role | What they do |
| --- | --- |
| **Guest (unauthenticated)** | Browse the home page, explore all published stays, filter results, open property details. |
| **Registered host** | Everything a guest can do, plus log in/out, add a property, see "My Properties", edit and delete their own listings. |

There is **no admin user** in the current implementation. There are only two logical roles, both backed by the single `users` table.

### 1.4 Main features currently implemented

1. Home page with hero, search bar and featured property grid (`Home.vue`).
2. Property listing page with responsive grid and pagination (`Properties.vue`).
3. Property details page with stats, host card and contact notice (`PropertyDetails.vue`).
4. Backend search + filters: location, property type, min price, max price, guests.
5. User registration, login, logout via Laravel Sanctum tokens.
6. Add property with image upload (`CreateProperty.vue` + `PropertyForm.vue`).
7. My Properties dashboard with edit and delete (`MyProperties.vue`).
8. Edit property (`EditProperty.vue`).
9. Ownership authorization via Laravel Policies.
10. Responsive mobile-first UI with Tailwind CSS.
11. 49 automated PHPUnit feature tests.

### 1.5 Technology stack (actual)

| Layer | Technology | Version (installed) |
| --- | --- | --- |
| Backend framework | Laravel | 11.55.1 |
| Language | PHP | 8.2+ required; 8.5.9 in the local environment |
| Auth / tokens | Laravel Sanctum | 4.3.3 |
| Database | MySQL (local: XAMPP MySQL, `staynest` database) | 5.7+ / 8.x |
| ORM | Eloquent | bundled with Laravel |
| Frontend framework | Vue 3 (Composition API) | ^3.5.41 |
| Build tool | Vite | ^6.0.11 |
| Routing (frontend) | Vue Router | ^4.6.4 |
| State management | Pinia | ^2.3.1 |
| HTTP client | Axios | ^1.7.4 (installed 1.19.0) |
| Styling | Tailwind CSS | ^3.4.13 |
| Vite Vue plugin | @vitejs/plugin-vue | ^6.0.8 |
| Laravel Vite plugin | laravel-vite-plugin | ^1.2.0 (installed 1.3.0) |
| Package managers | Composer (2.10.2) and npm (11.17.0) | — |
| Testing | PHPUnit | ^11.0.1 |
| Local server | `php artisan serve` (port 8000) + Vite dev server (port 5173) | — |

### 1.6 Backend architecture

Layered Laravel 11 architecture over a stateless REST API:

```
routes/api.php
  → App\Http\Controllers\Api\AuthController | PropertyController
  → App\Http\Requests\Api\* (Form Requests with validation)
  → App\Policies\PropertyPolicy (authorization)
  → App\Models\Property | User (Eloquent)
  → MySQL
  → App\Http\Resources\PropertyResource | UserResource (JSON shape)
```

Key Laravel 11 note: this project uses `bootstrap/app.php` to configure middleware and **global JSON exception rendering for `/api/*` requests** (401/403/404/422 handled centrally).

### 1.7 Frontend architecture

A Vue 3 single-page application (SPA) served by Laravel from `resources/views/app.blade.php`:

```
resources/js/
├── app.js            Entry point (mounts app, Pinia, Router)
├── App.vue           Root component wrapping DefaultLayout + RouterView
├── components/       Reusable UI (Navbar, PropertyCard, PropertyForm, ...)
├── layouts/          DefaultLayout (Navbar + main + Footer)
├── views/            Route-level pages
├── router/           Vue Router + auth/guest guards
├── stores/           Pinia stores (authStore, propertyStore)
├── services/         Axios instance + API service modules
└── utils/            Error extraction, formatting, query builders
```

The frontend talks to the backend **only through Axios** calling the `/api/v1/*` endpoints.

### 1.8 Database

MySQL with two domain tables — `users` and `properties` — plus Laravel's supporting tables (`personal_access_tokens`, `cache`, `jobs`, `sessions`, `password_reset_tokens`). Full schema in Section 5.

### 1.9 Authentication

Sanctum **personal access tokens** (Bearer tokens), stored in the browser's `localStorage` under `staynest_token`. No session cookies for the API. Full flow in Section 7.

### 1.10 API architecture

REST-style JSON API under `/api/v1`, with a consistent response envelope:

```json
{ "success": true, "message": "...", "data": [...], "meta": { ... } }
```

Full endpoint table in Section 9.

### 1.11 File / image handling

Property images are uploaded as multipart `image` files, validated (image type, ≤ 2 MB), stored on Laravel's **public disk** at `storage/app/public/properties/<random>.jpg`, and exposed via the `storage:link` symlink as `/storage/properties/<random>.jpg`. A `getImageUrlAttribute()` accessor produces the full URL. Full flow in Section 12.

### 1.12 Deployment configuration

- `.env.example` exists with production-safe placeholders (no secrets).
- README documents deployment to a VPS/shared host: `composer install --no-dev`, `npm ci && npm run build`, migrate/seed, `storage:link`, point the web root at `public/`, Nginx config included.
- CORS is configurable via `CORS_ALLOWED_ORIGINS`; the API base URL via `VITE_API_URL`.
- **Not implemented in the current project:** an actual live deployment (no platform config, CI/CD, or deployment scripts exist).

### 1.13 30-second interview explanation

> "StayNest is a property listing platform built with Laravel 11 and Vue 3. The backend is a REST API — Laravel exposes JSON endpoints for authentication with Sanctum tokens and for properties with search, filtering and pagination, all enforced by Form Requests, Policies and Eloquent. The frontend is a Vue 3 SPA using Vue Router, Pinia and Axios, styled with Tailwind. Travellers can browse, search and filter published stays; logged-in hosts can add, edit and delete only their own properties, with image uploads stored on Laravel's public storage. Data lives in a MySQL database with a `users` and `properties` table linked by a foreign key, and there are 49 automated API tests covering the whole flow."

---

## 2. Technology Stack — what each piece is and where it is used

### 2.1 Backend

**PHP (8.2+, running 8.5.9 locally)**
- *What:* The server-side scripting language.
- *Why:* Required by Laravel 11.
- *Where:* Runs all Laravel application code under `app/`, `routes/`, `database/`.

**Laravel 11 (11.55.1)**
- *What:* PHP MVC web framework.
- *Why:* Provides routing, Eloquent ORM, validation, auth scaffolding, migrations, a built-in dev server and a huge ecosystem.
- *Where:* Everything backend — controllers, models, requests, resources, policies, migrations, config.

**Laravel Sanctum (4.3.3)**
- *What:* Lightweight token authentication package.
- *Why:* Issues simple Bearer tokens for the SPA → API authentication without sessions or OAuth.
- *Where:* `User` uses `HasApiTokens`; `routes/api.php` uses `auth:sanctum` middleware; tokens stored in `personal_access_tokens`.

**Eloquent ORM**
- *What:* Laravel's ActiveRecord ORM.
- *Why:* Map `users`/`properties` tables to `User`/`Property` models with relationships, scopes and query builder.
- *Where:* `app/Models/`, all controller queries, `scopeFilter`, `scopePublished`, `paginate`, `with('user')`.

**Form Requests**
- *What:* Validation classes that run before a controller action.
- *Why:* Centralize validation rules and messages away from controllers.
- *Where:* `app/Http/Requests/Api/*` — `RegisterRequest`, `LoginRequest`, `StorePropertyRequest`, `UpdatePropertyRequest`.

**API Resources**
- *What:* Classes that transform models into JSON.
- *Why:* Control exactly what fields are exposed and shape the `data` payload.
- *Where:* `app/Http/Resources/PropertyResource.php`, `UserResource.php`.

**REST API**
- *What:* JSON HTTP endpoints following resource conventions (`GET/POST/PUT/DELETE`).
- *Why:* Cleanly separates the Vue SPA from the backend.
- *Where:* `routes/api.php` under the `/api/v1` prefix.

### 2.2 Frontend

**Vue 3 (Composition API)**
- *What:* Progressive JavaScript framework for building UIs.
- *Why:* Reactive components for an SPA experience; Composition API used throughout (`<script setup>`).
- *Where:* `resources/js/views/*.vue`, `components/*.vue`.

**Vite (6.x)**
- *What:* Frontend build tool and dev server with HMR.
- *Why:* Fast dev experience and optimized production bundles.
- *Where:* `vite.config.js` wires the Vue plugin, proxies `/api` and `/storage` to Laravel during dev, `npm run dev` / `npm run build`.

**Vue Router (4.x)**
- *What:* Client-side routing for SPAs.
- *Why:* Lets the SPA navigate without full page reloads; supports route guards.
- *Where:* `resources/js/router/index.js` — 9 routes plus auth/guest guards.

**Pinia (2.x)**
- *What:* Vue state management store library (official successor to Vuex).
- *Why:* Centralizes auth and property state shared across components.
- *Where:* `resources/js/stores/authStore.js`, `propertyStore.js`.

**Axios**
- *What:* Promise-based HTTP client.
- *Why:* Calls the Laravel API, attaches Bearer tokens, handles errors, supports FormData uploads.
- *Where:* `services/api.js` (instance + interceptors), `authService.js`, `propertyService.js`.

**Tailwind CSS (3.x)**
- *What:* Utility-first CSS framework.
- *Why:* Fast responsive styling directly in templates; custom theme colors/fonts in `tailwind.config.js`.
- *Where:* `resources/css/app.css`, all `.vue` files, custom component classes (`.btn-primary`, `.card`, `.input`).

### 2.3 Database and tooling

- **MySQL** — the production-parity database. `users`, `properties`, `personal_access_tokens` and Laravel's cache/jobs/sessions tables live here.
- **SQLite (in-memory)** — used **only** by the test suite (`phpunit.xml` sets `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`).
- **Composer** — PHP dependency manager (`composer.json`).
- **npm** — Node dependency manager (`package.json`).
- **Git / GitHub** — version control; the repo has a full commit history (see Section 26).
- **XAMPP** — the local Windows environment hosting MySQL; Laravel runs via `php artisan serve`.

---

## 3. Architecture and Data Flow

The project follows a **frontend/backend separation** inside one Laravel project.

```
┌────────────────────────── Vue 3 SPA (browser) ──────────────────────────┐
│  Views → Pinia Stores → Services (Axios)                                │
└────────────────────────────────┬─────────────────────────────────────────┘
                                 │  HTTP (JSON / FormData)
                                 ▼
┌────────────────────────── Laravel REST API ─────────────────────────────┐
│  routes/api.php  (prefix /api/v1)                                        │
│  → auth:sanctum middleware (protected routes)                            │
│  → Controllers (AuthController, PropertyController)                       │
│  → Form Requests (validation) → Policies (authorization)                 │
│  → Models (User, Property) → Eloquent                                    │
│  → MySQL                                                                │
│  → API Resources → JSON envelope                                         │
└──────────────────────────────────────────────────────────────────────────┘
```

**Every request follows this same path:**
Vue component → store action → Axios → Laravel route → middleware → controller → validation → policy → Eloquent → MySQL → Resource → JSON → Axios → store → reactive UI.

---

## 4. Folder Structure

### 4.1 Laravel (backend)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php            Base controller (uses AuthorizesRequests → enables Policies)
│   │   └── Api/
│   │       ├── AuthController.php    register, login, logout, user
│   │       └── PropertyController.php index, show, store, update, destroy, myProperties
│   ├── Requests/
│   │   └── Api/                      Form Requests (validation rules)
│   │       ├── RegisterRequest.php
│   │       ├── LoginRequest.php
│   │       ├── StorePropertyRequest.php
│   │       └── UpdatePropertyRequest.php
│   └── Resources/
│       ├── PropertyResource.php      JSON shape for a property
│       └── UserResource.php          JSON shape for a user
├── Models/
│   ├── User.php                      Authenticatable, HasApiTokens, HasMany properties
│   └── Property.php                  BelongsTo user, scopes (published, filter), slug gen
├── Policies/
│   └── PropertyPolicy.php            create/view/update/delete ownership rules
└── Providers/
    └── AppServiceProvider.php        Default, empty boot/register

bootstrap/
└── app.php                           Laravel 11 config: routes, guest redirect, JSON exceptions

routes/
├── api.php                           All API routes (/api/v1)
├── web.php                           SPA catch-all → app view
└── console.php                       Artisan commands

database/
├── migrations/                       Schema definitions
├── factories/                        UserFactory, PropertyFactory (fake data)
├── seeders/
│   ├── DatabaseSeeder.php            3 users + 14 published + 1 draft property
│   └── assets/properties/1..14.jpg   Seeded demo images
└── database.sqlite                   Test helper file (SQLite used in tests)

config/                               cors, sanctum, auth, filesystems, app, ...
public/
├── index.php                         Laravel front controller
├── build/                            Compiled Vite assets (gitignored)
└── storage → storage/app/public      Symlink created by `php artisan storage:link`

storage/
└── app/public/properties/            Uploaded property images
```

### 4.2 Vue (frontend) — `resources/js/`

```
resources/js/
├── app.js                    Create app, install Pinia + Router, mount #app
├── App.vue                   Root; listens for the 'staynest:logout' window event
├── bootstrap.js              Axios global (legacy Laravel Breeze file; not used by SPA calls)
├── assets/                   (exists as a folder placeholder)
├── layouts/
│   └── DefaultLayout.vue     Navbar + <main><slot/></main> + Footer
├── components/
│   ├── Navbar.vue            Sticky navbar, desktop + mobile menus, auth-aware links
│   ├── Footer.vue
│   ├── PropertyCard.vue      Listing card (image, title, location, type, price, guests)
│   ├── SearchBar.vue         Hero search form (location, type, min, max)
│   ├── FilterPanel.vue       Explore page filters (adds guests filter)
│   ├── PropertyForm.vue      Reusable create/edit form with image upload
│   ├── LoadingSpinner.vue    Loading state
│   ├── EmptyState.vue        Empty / error state
│   ├── Pagination.vue        Page buttons
│   └── BrandLogo.vue         StayNest SVG logo
├── router/
│   └── index.js              Routes + requiresAuth/guestOnly guards + page titles
├── stores/
│   ├── authStore.js          token, user, login/register/logout/clear/hydrate
│   └── propertyStore.js      properties, myProperties, current, CRUD actions, pagination meta
├── services/
│   ├── api.js                Axios instance (baseURL, Bearer interceptor, 401 handler)
│   ├── authService.js        register, login, logout, me
│   └── propertyService.js    getAll, getBySlug, getMine, create, update, destroy (+ FormData)
├── utils/
│   ├── errors.js             extractErrorMessage, extractFieldErrors
│   ├── format.js             formatCurrency, formatPricePerNight, formatDate, initials
│   └── properties.js         PROPERTY_TYPES, PRICE_LIMITS, buildPropertyQuery
└── views/
    ├── Home.vue              Hero + search + featured grid
    ├── Properties.vue        Explore page (filters + grid + pagination)
    ├── PropertyDetails.vue   Details page
    ├── Login.vue / Register.vue
    ├── CreateProperty.vue / EditProperty.vue
    ├── MyProperties.vue      Manage own listings
    └── NotFound.vue          404 page
```

---

