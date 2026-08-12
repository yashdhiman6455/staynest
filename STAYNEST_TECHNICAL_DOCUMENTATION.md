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

## 5. Database Design

### 5.1 `users`

Created by `database/migrations/0001_01_01_000000_create_users_table.php`.

```
users
│
├── id               BIGINT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT
├── name             VARCHAR(255), NOT NULL
├── email            VARCHAR(255), NOT NULL, UNIQUE
├── email_verified_at  TIMESTAMP, NULL
├── password         VARCHAR(255), NOT NULL (bcrypt hash)
├── phone            VARCHAR(255), NULL
├── avatar           VARCHAR(255), NULL (path, not stored by any endpoint)
├── remember_token   VARCHAR(100), NULL (session-only helper)
└── created_at / updated_at  TIMESTAMP, NULL
```

Purpose: one row per registered host/traveller.

### 5.2 `properties`

Created by `database/migrations/2026_08_12_075529_create_properties_table.php`.

```
properties
│
├── id               BIGINT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT
├── user_id          BIGINT UNSIGNED, NOT NULL
│                    FOREIGN KEY → users.id, ON DELETE CASCADE
├── title            VARCHAR(255), NOT NULL
├── slug             VARCHAR(255), NOT NULL, UNIQUE
├── description      TEXT, NOT NULL
├── property_type    VARCHAR(255), NOT NULL   (Apartment | House | Villa | Cottage | Hotel | Guest House)
├── location         VARCHAR(255), NOT NULL
├── city             VARCHAR(255), NULL
├── country          VARCHAR(255), NULL
├── price_per_night  DECIMAL(10,2), NOT NULL
├── guests           INTEGER UNSIGNED, NOT NULL
├── bedrooms         INTEGER UNSIGNED, NOT NULL, DEFAULT 0
├── bathrooms        INTEGER UNSIGNED, NOT NULL, DEFAULT 0
├── image            VARCHAR(255), NULL  (storage path e.g. properties/xxx.jpg)
├── status           ENUM('published','draft'), NOT NULL, DEFAULT 'published'
└── created_at / updated_at  TIMESTAMP, NULL

INDEX: (status, property_type)     ← composite index on migration line 32
```

Purpose: one row per property listing, owned by exactly one user.

### 5.3 `personal_access_tokens` (Sanctum)

Created by `2026_08_12_074544_create_personal_access_tokens_table.php`. Stores Bearer tokens:
`id`, `tokenable_type`/`tokenable_id` (morphs), `name`, `token` (unique, 64 chars, hashed), `abilities`, `last_used_at`, `expires_at`, timestamps.

### 5.4 Supporting tables (Laravel defaults)

- `password_reset_tokens` — email + token. **Not implemented in the current project:** no password-reset flow uses it.
- `sessions` — database session store (configured via `SESSION_DRIVER=database`).
- `cache` — database cache store (`CACHE_STORE=database`).
- `jobs` — database queue (`QUEUE_CONNECTION=database`). No queued jobs exist.

### 5.5 Relationships

```
users 1 ────── * properties
```

- `User` **hasMany** `Property` (a user owns many listings).
- `Property` **belongsTo** `User` (each listing belongs to one host).
- **No many-to-many relationships exist** in the current project.

---

## 6. Relationship Explanation (Eloquent)

### 6.1 `User::properties()` — HasMany

```php
// app/Models/User.php
public function properties(): HasMany
{
    return $this->hasMany(Property::class);
}
```

- `hasMany` = one user is associated with many property rows.
- Laravel infers the foreign key `user_id` on `properties` from the model name.
- Typical query: `$user->properties` → `SELECT * FROM properties WHERE user_id = ?`.

### 6.2 `Property::user()` — BelongsTo

```php
// app/Models/Property.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

- `belongsTo` = this property row points at one user.
- Typical query: `$property->user->name` → `SELECT * FROM users WHERE id = ?`.
- Controllers use `->with('user:id,name,email,phone,avatar')` to **eager-load** only needed user columns and avoid N+1 queries.

### 6.3 Example query combining both

`PropertyController::index()`:

```php
Property::query()
    ->published()                                      // WHERE status = 'published'
    ->with('user:id,name,email,phone,avatar')          // eager load owner
    ->filter($request->all())                          // dynamic WHERE clauses
    ->latest()                                         // ORDER BY created_at DESC
    ->paginate($perPage);                              // LIMIT/OFFSET + count
```

---

## 7. Authentication Flow

StayNest uses **Laravel Sanctum personal access tokens** — a stateless Bearer-token system. There are no session cookies for API calls.

### 7.1 Registration

1. **`Register.vue`** collects `name`, `email`, `password`, `password_confirmation`.
2. `authStore.register(payload)` calls `authService.register()` → `api.post('/register', payload)`.
3. Laravel route `POST /api/v1/register` → `AuthController::register()`.
4. **`RegisterRequest`** validates: `name` required string ≤255; `email` required email **unique** in `users`; `password` required string min 8 **confirmed**.
5. `User::create([...])` creates the row. The `User` model has `'password' => 'hashed'` cast, so the password is **bcrypt-hashed automatically**.
6. `$user->createToken('auth-token')->plainTextToken` inserts a row in `personal_access_tokens` and returns a plain-text token.
7. Response `201` with `{ success, message, token, user: UserResource }`.
8. `authStore` saves `token` → `localStorage('staynest_token')` and `user` → `localStorage('staynest_user')`.
9. Router redirects to Home. Navbar now shows the user's name.

### 7.2 Login

1. **`Login.vue`** collects `email` and `password`.
2. `authStore.login()` → `authService.login()` → `POST /api/v1/login`.
3. **`LoginRequest`** validates both fields are present.
4. `AuthController::login()` finds the user by email and checks `Hash::check($password, $user->password)`. On failure → `401` `"These credentials do not match our records."`.
5. On success, a new token is issued and `200` with the same envelope as register.
6. Frontend stores token + user and redirects to `route.query.redirect` (from the auth guard) or Home.

### 7.3 Authenticated user

- `GET /api/v1/user` (requires `auth:sanctum`) → `AuthController::user()` returns `UserResource`.
- **Not called on app boot** — the app trusts `localStorage('staynest_user')` from login/register.

### 7.4 Logout

1. Navbar `Log out` → `authStore.logout()` → `POST /api/v1/logout` (with Bearer token).
2. `AuthController::logout()` deletes `$request->user()->currentAccessToken()` — the DB row is removed.
3. Frontend `clear()` removes both `localStorage` keys, then the router pushes to Home.

### 7.5 How protected routes are enforced

**Backend:** `routes/api.php` wraps protected endpoints in `->middleware('auth:sanctum')`. A request without a valid Bearer token gets `401`. The exception handler in `bootstrap/app.php` renders:

```json
{ "success": false, "message": "You must be logged in to access this resource." }
```

**Frontend:** the Axios request interceptor attaches the token from `localStorage`:

```js
config.headers.Authorization = `Bearer ${token}`;
```

and the router guard redirects unauthenticated visitors away from protected pages:

```js
if (to.meta.requiresAuth && !token) {
    return { name: 'login', query: { redirect: to.fullPath } };
}
```

**Session/Sanctum nuance:** `SANCTUM_STATEFUL_DOMAINS` and the `web` guard are configured, but the app actually authenticates via pure Bearer tokens in `localStorage` — **not** via Sanctum's cookie/Sanctum CSRF flow.

---

## 8. Login Flow — detailed request lifecycle

```
User types email + password
        │
        ▼
Login.vue  (v-model.form, submit() → auth.login(form))
        │
        ▼
authStore.login(payload)            ← sets loading=true
        │
        ▼
authService.login(payload)          ← api.post('/login', payload)
        │
        ▼
api.js request interceptor          ← adds "Authorization: Bearer <localStorage token>"
        │
        ▼
POST /api/v1/login  (Laravel)
        │
        ▼
LoginRequest  (validates email/password → 422 if invalid)
        │
        ▼
AuthController::login()
        ├── User::where('email', ...)->first()
        ├── Hash::check(password)  → 401 on mismatch
        └── createToken('auth-token')->plainTextToken
        │
        ▼
200 JSON  { success, message, token, user }
        │
        ▼
authStore persists token + user to localStorage, loading=false
        │
        ▼
router.push(redirect || home)   → Navbar shows user name
```

---

## 9. API Architecture

### 9.1 Endpoints (actual)

Base URL: `/api/v1` (see `routes/api.php`).

| Method | Endpoint | Purpose | Auth | Controller method |
| --- | --- | --- | --- | --- |
| POST | `/api/v1/register` | Create account + token | Public | `AuthController@register` |
| POST | `/api/v1/login` | Log in + token | Public | `AuthController@login` |
| POST | `/api/v1/logout` | Revoke current token | Bearer | `AuthController@logout` |
| GET | `/api/v1/user` | Current user profile | Bearer | `AuthController@user` |
| GET | `/api/v1/properties` | List published, filterable, paginated | Public | `PropertyController@index` |
| GET | `/api/v1/properties/{slug}` | Single property by slug | Public* | `PropertyController@show` |
| POST | `/api/v1/properties` | Create property (multipart) | Bearer | `PropertyController@store` |
| PUT | `/api/v1/properties/{property}` | Update own property | Bearer | `PropertyController@update` |
| DELETE | `/api/v1/properties/{property}` | Delete own property | Bearer | `PropertyController@destroy` |
| GET | `/api/v1/my-properties` | List the user's own properties | Bearer | `PropertyController@myProperties` |

\* `show` is public but hides **draft** properties unless the requester is the owner (checked with `$request->user('sanctum')`).

### 9.2 Response envelope

Every endpoint returns:

```json
{
    "success": true,
    "message": "Properties retrieved successfully.",
    "data": [ ... ],
    "meta": { "current_page": 1, "last_page": 2, "per_page": 12, "total": 14 }
}
```

`meta` appears only on paginated listings.

### 9.3 REST principles used

- **GET** = read (list/detail), no side effects.
- **POST** = create resource (`/properties`, `/register`).
- **PUT** = full update (`/properties/{property}`).
- **DELETE** = remove resource.
- **Nouns, not verbs** in URLs (`/properties`, `/my-properties`).
- **Stateless** — each request carries its own Bearer token.
- **Status codes** carry meaning: `200` OK, `201` Created, `401` Unauthenticated, `403` Forbidden, `404` Not found, `422` Validation failed.

---

## 10. Property Flow

### 10.1 Add property — complete flow

```
User clicks "Add Property" (Navbar)
        │
        ▼
Route: /properties/create  (requiresAuth guard → redirect to /login?redirect=... if not logged in)
        │
        ▼
CreateProperty.vue  → renders PropertyForm.vue
        │
        ├── form fields: title, description, property_type, location, city, country,
        │               price_per_night, guests, bedrooms, bathrooms, image
        ├── client-side image check: file.size > 2MB → inline error, block submit
        │
        ▼
submit() → payload = { ...form }; if image is null, delete payload.image
        │
        ▼
emit('submit', payload)  →  CreateProperty.handleSubmit(payload)
        │
        ▼
propertyStore.createProperty(payload)  →  propertyService.create(payload)
        │
        ▼
toFormData(payload)  →  FormData (append every non-empty field; image appended as a File)
        │
        ▼
api.post('/properties', formData)   [Axios auto-sets multipart/form-data]
        │
        ▼
POST /api/v1/properties  (auth:sanctum)
        │
        ▼
StorePropertyRequest:
   title required|string|max:255
   description required|string
   property_type required|in:[Apartment,House,Villa,Cottage,Hotel,Guest House]
   location required|string|max:255
   price_per_night required|numeric|min:1|max:9999999
   guests required|integer|min:1|max:100
   bedrooms/bathrooms required|integer|min:0|max:100
   status nullable|in:[published,draft]
   image required|image|mimes:jpeg,png,jpg,webp|max:2048
        │
        ▼
PropertyController@store:
   authorize('create', Property::class)          ← policy: any authenticated user
   $request->file('image')->store('properties','public')
                                                ← saves to storage/app/public/properties/<random>
   Property::create([
       user_id, title, slug: Property::generateSlug($title),
       description, property_type, location, city, country,
       price_per_night, guests, bedrooms, bathrooms,
       image: $imagePath, status: published|draft
   ])                                            ← INSERT into MySQL
   load('user:id,name,avatar')
        │
        ▼
201 { success, message, data: PropertyResource }
        │
        ▼
CreateProperty receives response → router.push to property details
   with query { created: 1 }  → success banner "Your property has been published successfully!"
```

### 10.2 Property listing flow (Home page)

```
Browser opens /  →  Home.vue mounted
        │
        ▼
onMounted → store.fetchProperties({ per_page: 6 })
        │
        ▼
propertyService.getAll({ per_page: 6 })  →  api.get('/properties', { params })
        │
        ▼
GET /api/v1/properties?per_page=6   (public)
        │
        ▼
PropertyController@index:
   per_page clamped between 1 and 24
   Property::query()->published()->with('user:id,...')->filter($request->all())->latest()->paginate()
        │
        ▼
MySQL  →  PropertyResource collection  →  JSON with meta
        │
        ▼
propertyStore.properties = response.data;  meta = response.meta
        │
        ▼
Home.vue renders <PropertyCard v-for> in a responsive grid (1/2/3 columns)
```

### 10.3 Explore page (with filters)

`Properties.vue` reads the URL query (`route.query`) and calls `store.fetchProperties({ ...buildPropertyQuery(query), per_page: 12, page })`. A `watch(() => route.query, load)` reloads whenever the URL changes, so filters, pagination and browser back/forward all work through the URL.

### 10.4 Property details flow

```
User clicks a PropertyCard (RouterLink to /properties/{slug})
        │
        ▼
Vue Router route 'property-details' (path '/properties/:slug')
        │
        ▼
PropertyDetails.vue  → onMounted → store.fetchProperty(route.params.slug)
        │
        ▼
api.get(`/properties/${slug}`)
        │
        ▼
GET /api/v1/properties/{slug}
        │
        ▼
PropertyController@show:
   Property::with('user:id,...')->where('slug', $slug)->first()
   404 if missing; 404 if draft and not owner
        │
        ▼
200 { data: PropertyResource }
        │
        ▼
PropertyDetails renders image, title, location, price, stats (guests/bedrooms/bathrooms/type),
   description, host card, "Contact Host" button
```

**Why a slug?** Slugs are human-readable, SEO-friendly URLs (`/properties/urban-nest-apartment`) and are made unique by `Property::generateSlug()` (appends `-2`, `-3`, … on collisions). The route uses the slug, not the numeric id, for the public detail page.

### 10.5 Edit property flow

1. `MyProperties.vue` links each row to `/properties/{id}/edit`.
2. `EditProperty.vue` mounts, and if the store is empty calls `store.fetchMyProperties()`. It then finds the property **by id** in the user's own list. If absent → "Property not found" state (front-end guard).
3. `PropertyForm.vue` is pre-filled with `initial` values; the existing image is shown as a preview (`previewUrl = initial.image_url`).
4. Submitting → `propertyStore.updateProperty(id, payload)` → `api.put('/properties/{id}', formData)`.
5. `UpdatePropertyRequest` — same rules as store but `image` is **nullable** (keep existing).
6. `PropertyController@update` → `authorize('update', $property)` → if a new file is present, the old file is deleted and the new one stored; slug is regenerated **only if the title changed**; all fields updated → `UPDATE` SQL → `200` with `PropertyResource`.
7. Frontend redirects to the property details page with `{ updated: 1 }` → success banner.

### 10.6 Delete property flow

1. `MyProperties.vue` "Delete" button → `askDelete(property)` opens a **confirmation modal** (also closes on `Escape`, locks body scroll).
2. Confirm → `store.deleteProperty(id)` → `api.delete('/properties/{id}')`.
3. `PropertyController@destroy` → `authorize('delete', $property)` → if the stored image exists on the public disk it is deleted → `$property->delete()` → `DELETE` SQL → `200 { message }`.
4. Store refetches `fetchMyProperties()` → the deleted row disappears from the UI.

**How User A is prevented from deleting User B's property** (and editing): the controller calls `$this->authorize('update'/'delete', $property)`, which invokes `PropertyPolicy`:

```php
public function update(User $user, Property $property): bool {
    return $user->id === $property->user_id;   // owner only
}
```

If A tries it, Laravel throws `AccessDeniedHttpException` → the exception handler renders `403` `{ success:false, message:"You are not allowed to perform this action." }`. This is verified by tests `PropertyOwnershipTest`.

---

## 11. Search & Filter Flow

**Filtering happens on the backend** (Eloquent → SQL `WHERE`), never by loading everything into Vue.

### 11.1 Query parameter contract (`GET /api/v1/properties`)

| Parameter | Example | SQL generated |
| --- | --- | --- |
| `location` | `location=Chandigarh` | `WHERE (location LIKE %Chandigarh% OR city LIKE %Chandigarh% OR country LIKE %Chandigarh%)` |
| `type` | `type=Apartment` | `WHERE property_type = 'Apartment'` |
| `min_price` | `min_price=1000` | `WHERE price_per_night >= 1000` |
| `max_price` | `max_price=5000` | `WHERE price_per_night <= 5000` |
| `guests` | `guests=4` | `WHERE guests >= 4` |
| `search` | `search=lakeview` | `WHERE (title LIKE %lakeview% OR description LIKE %lakeview%)` |
| `per_page` | `per_page=12` | `LIMIT` (clamped 1–24) |
| `page` | `page=2` | `OFFSET` |

### 11.2 The Eloquent scope

`Property::scopeFilter()` (`app/Models/Property.php`) uses `$query->when($value, fn($q,$v)=>...)` — a conditional `where` only applied when the parameter is present:

```php
public function scopeFilter($query, array $filters): void
{
    $query->when($filters['search'] ?? null, fn ($q, $s) => $q->where(function ($q) use ($s) {
        $q->where('title', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%");
    }));
    $query->when($filters['location'] ?? null, ...where location/city/country LIKE...);
    $query->when($filters['type'] ?? null,     fn ($q, $t) => $q->where('property_type', $t));
    $query->when($filters['min_price'] ?? null, fn ($q, $m) => $q->where('price_per_night', '>=', $m));
    $query->when($filters['max_price'] ?? null, fn ($q, $m) => $q->where('price_per_night', '<=', $m));
    $query->when($filters['guests'] ?? null,    fn ($q, $g) => $q->where('guests', '>=', $g));
}
```

### 11.3 End-to-end example — "Chandigarh, Apartment, max ₹5,000"

1. User types `Chandigarh`, selects `Apartment`, sets Max price `5000`, clicks **Search stays**.
2. `SearchBar.vue` emits the filters → `Home.vue handleSearch` → `buildPropertyQuery(filters)` produces `{ location:'Chandigarh', type:'Apartment', max_price:'5000' }` → `router.push({ name:'properties', query })`.
3. URL becomes `/properties?location=Chandigarh&type=Apartment&max_price=5000`.
4. `Properties.vue` reads the query → `buildPropertyQuery` → `store.fetchProperties({ location, type, max_price, per_page:12, page:1 })`.
5. Axios → `GET /api/v1/properties?location=Chandigarh&type=Apartment&max_price=5000`.
6. `PropertyController@index` → `scopeFilter` composes:
   `WHERE status='published' AND property_type='Apartment' AND price_per_night <= 5000 AND (location LIKE '%Chandigarh%' OR city LIKE '%Chandigarh%' OR country LIKE '%Chandigarh%') ORDER BY created_at DESC LIMIT 12`.
7. Matching `PropertyResource`s are returned; the UI shows the count and the "Clear filters" pill, or the `EmptyState` ("No stays match your search") if zero.

**Why backend filtering?** (1) correct pagination — you can only page through *filtered* results; (2) the browser doesn't download the whole table; (3) a single source of truth for matching logic; (4) security — the client can never bypass or see data that the API doesn't return.

### 11.4 Frontend helpers

- `SearchBar.vue` — hero form (location, type, min price, max price). Used on Home.
- `FilterPanel.vue` — explore sidebar (adds a `guests` select). Emits `apply`/`clear`.
- `buildPropertyQuery()` in `utils/properties.js` strips empty values so only real filters hit the API.
- "Clear filters" replaces the URL with no query → `watch` fires → reload → full list.

---

## 12. Image Upload Flow

```
Browser: PropertyForm.vue <input type="file"> → onFileChange(event)
   ├── client check: file.size > 2MB → inline error, abort
   └── form.image = file  +  object-URL preview (URL.createObjectURL)
        │
        ▼
submit() → payload = { ...form }   (image = File object)
        │
        ▼
propertyService.toFormData(payload)  →  FormData.append('image', file)
        │
        ▼
api.post('/properties', formData)    → Axios sends multipart/form-data (browser sets boundary)
        │
        ▼
POST /api/v1/properties
        │
        ▼
StorePropertyRequest: image required|image|mimes:jpeg,png,jpg,webp|max:2048
        │
        ▼
PropertyController@store:
   $request->file('image')->store('properties', 'public')
        │
        ▼
Laravel Storage writes to  storage/app/public/properties/<random-40-hex>.jpg
        │
        ▼
DB stores the relative path e.g. "properties/zhfpZwxohAr1mBsfRLr90TYBfAYncBpcn1NKnIqP.jpg"
        │
        ▼
Property::getImageUrlAttribute():
   if image is a full URL → return as-is
   else → return url('storage/' . $this->image)   = http://host/storage/properties/xxx.jpg
        │
        ▼
PropertyResource exposes "image_url" → Vue <img :src="property.image_url">
```

Key details:

- **Where files are stored:** Laravel's **public disk** → `storage/app/public/properties/`. The `config/filesystems.php` `public` disk root is `storage/app/public`.
- **Public access:** `php artisan storage:link` creates `public/storage` → `storage/app/public` (a symlink). Without it, `/storage/...` returns 404. The symlink already exists in this working copy and is gitignored (`/public/storage`).
- **File naming:** Laravel generates a random 40-character name — no user-controlled filenames, no collisions, no path traversal.
- **Validation:** server (`image`, `mimes`, `max:2048`) *and* client (file size check) both apply.
- **Edit:** if a new file is uploaded, the old file is deleted from disk first (`Storage::disk('public')->delete(...)`), then the new one is stored.
- **Delete:** the stored image file is removed when the property is deleted.

---

