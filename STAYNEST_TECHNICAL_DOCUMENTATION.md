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

## 13. Vue Architecture

### 13.1 Views vs Components vs Stores vs Services

| Layer | Role | Examples |
| --- | --- | --- |
| **View** | Route-level page; composes components and calls stores | `Home.vue`, `Properties.vue`, `Login.vue` |
| **Component** | Reusable UI building block with props/events | `PropertyCard.vue`, `Navbar.vue`, `PropertyForm.vue` |
| **Store** (Pinia) | Shared reactive state + actions | `authStore`, `propertyStore` |
| **Service** | Thin wrappers around Axios calls | `authService`, `propertyService` |

**Why separate API logic from UI logic?** Views never see Axios; they only call store actions or services. This makes components easier to test, keeps HTTP details (base URL, tokens, FormData) in one place (`api.js`), and means the UI can be swapped or reused without touching network code.

### 13.2 Composition API usage

All components use `<script setup>` (Composition API) with `ref`, `reactive`, `computed`, `watch`, `onMounted`, `defineProps`, `defineEmits`.

---

## 14. Pinia

- **What:** Vue's official state management library.
- **Why:** Auth status and property lists are shared across many components; a store prevents prop-drilling and duplicate API calls.

### 14.1 `authStore` (`stores/authStore.js`)

- **State:** `token` (from `localStorage`), `user` (parsed from `localStorage`), `loading`.
- **Getter:** `isAuthenticated` → `Boolean(token)`.
- **Actions:**
  - `register(payload)` / `login(payload)` → call service, persist token+user to `localStorage`.
  - `logout()` → call API, then `clear()` storage.
  - `clear()` → empty state + remove `localStorage` keys.
  - `hydrate(payload)` → update user + persist (kept for future use).

### 14.2 `propertyStore` (`stores/propertyStore.js`)

- **State:** `properties`, `myProperties`, `current`, `loading`, `saving`, `meta` (pagination).
- **Actions:** `fetchProperties(params)`, `fetchProperty(slug)`, `fetchMyProperties()`, `createProperty(payload)`, `updateProperty(id, payload)`, `deleteProperty(id)`, `resetCurrent()`.

### 14.3 How data moves through stores

Login → authStore persists → Navbar/route guards read `auth.isAuthenticated` / `auth.user`. Browse → propertyStore fetches → grid re-renders. Create → propertyStore POSTs → details page reads `response.data`.

---

## 15. Axios

### 15.1 Instance (`services/api.js`)

```js
const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || '/api/v1',
    headers: { Accept: 'application/json' },
});
```

- **Request interceptor:** attaches `Authorization: Bearer <staynest_token>` to every request.
- **Response interceptor:** on `401` (except login/register), clears `localStorage` and dispatches the `staynest:logout` window event; `App.vue` listens and reloads the page so the UI returns to the logged-out state.

### 15.2 How each HTTP verb is used

- `GET` — `api.get('/properties', { params })`, `api.get('/properties/'+slug)`, `api.get('/my-properties')`, `api.get('/user')`.
- `POST` — `api.post('/register', payload)` (JSON), `api.post('/properties', formData)` (multipart).
- `PUT` — `api.put('/properties/'+id, formData)` (multipart).
- `DELETE` — `api.delete('/properties/'+id)`.

### 15.3 Error handling

`utils/errors.js`:
- `extractErrorMessage(error, fallback)` — prefers `response.data.message`; maps `Network Error` to a friendly connection message.
- `extractFieldErrors(error)` — flattens Laravel's `errors` object to `{ field: firstMessage }` for inline display.

### 15.4 FormData + the Content-Type gotcha

`propertyService.toFormData()` builds a `FormData` object. **Important:** the Axios instance must NOT set a global `Content-Type: application/json`, because axios 1.19 then converts FormData to JSON and silently drops the file — producing Laravel's `"Please choose a property image."` error. This was a real bug fixed in this project by removing the hard-coded `Content-Type` from `api.js`, so axios selects `multipart/form-data` for FormData and `application/json` for plain objects automatically.

---

## 16. Routing

### 16.1 Laravel routes

**`routes/web.php`** — a catch-all for the SPA:

```php
Route::get('/{any}', fn () => view('app'))->where('any', '.*');
```

Any URL (e.g. `/properties`, `/properties/urban-nest-apartment`) returns `app.blade.php`, which loads the Vue bundle. Vue Router takes over from there.

**`routes/api.php`** — all API routes under `/api/v1` (see endpoint table in Section 9).

### 16.2 Vue Router (`router/index.js`)

| Path | Name | Page | Guard |
| --- | --- | --- | --- |
| `/` | `home` | Home | — |
| `/properties` | `properties` | Properties | — |
| `/properties/create` | `create-property` | CreateProperty | `requiresAuth` |
| `/properties/:id/edit` | `edit-property` | EditProperty | `requiresAuth` |
| `/properties/:slug` | `property-details` | PropertyDetails | — |
| `/my-properties` | `my-properties` | MyProperties | `requiresAuth` |
| `/login` | `login` | Login | `guestOnly` |
| `/register` | `register` | Register | `guestOnly` |
| `/:pathMatch(.*)*` | `not-found` | NotFound | — |

Guards read `localStorage('staynest_token')`:
- `requiresAuth` + no token → redirect to `/login?redirect=<current path>`.
- `guestOnly` + token → redirect to Home.

`router.afterEach` sets `document.title` per route meta.

### 16.3 Why two routers?

The **backend router** owns data and auth over HTTP. The **frontend router** owns the view state inside the SPA. This separation is what makes the frontend a true SPA: navigation never reloads the page, while the API remains cleanly exposed and reusable (e.g. for a mobile app later).

---

## 17. Validation

### 17.1 Form Requests (actual rules)

**`RegisterRequest`**
```php
name      => required|string|max:255
email     => required|string|email|max:255|unique:users,email
password  => required|string|min:8|confirmed     // needs password_confirmation field
```

**`LoginRequest`**
```php
email    => required|string|email
password => required|string
```

**`StorePropertyRequest`** (custom messages for `image.required/image/max`)
```php
title           => required|string|max:255
description     => required|string
property_type   => required|in:[Apartment, House, Villa, Cottage, Hotel, Guest House]
location        => required|string|max:255
city/country    => nullable|string|max:255
price_per_night => required|numeric|min:1|max:9999999
guests          => required|integer|min:1|max:100
bedrooms        => required|integer|min:0|max:100
bathrooms       => required|integer|min:0|max:100
status          => nullable|in:[published, draft]
image           => required|image|mimes:jpeg,png,jpg,webp|max:2048
```

**`UpdatePropertyRequest`** — identical, except `image => nullable|image|...` (keep existing image on edit).

### 17.2 Why backend validation when Vue validates?

- The client can be bypassed (curl, Postman, scripts).
- The API is a public surface — bad data must never reach the DB.
- Laravel returns structured `errors` (`422`) that the UI renders inline; single source of truth for rules.
- Client validation is only for UX (instant feedback, e.g. the 2MB image check).

---

## 18. Security

**Implemented in the current project:**

1. **Password hashing** — `User` model casts `password` → `hashed` (bcrypt). Plain text is never stored.
2. **Sanctum tokens** — stateless Bearer tokens in `personal_access_tokens`; `auth:sanctum` guards protected routes.
3. **Auth middleware** — `auth:sanctum` on all mutating/my-properties routes; unauthenticated → 401.
4. **Authorization policies** — `PropertyPolicy` restricts update/delete/view to the owner.
5. **Input validation** — Form Requests on every input endpoint (type, length, ranges, enum lists).
6. **Mass-assignment protection** — `$fillable` on both models; controllers pass explicit arrays to `create`/`update` (no `$request->all()`).
7. **File upload validation** — server-side `image` + `mimes` + `max:2048`; Laravel's `store()` generates random names (no user-controlled paths).
8. **Ownership validation** — enforced in the controller via policies (never trusted from the frontend).
9. **CORS** — `config/cors.php` restricts API origins to `CORS_ALLOWED_ORIGINS` (default `http://localhost:5173`); methods/headers allowed generically.
10. **No secrets committed** — `.env` is gitignored; `.env.example` has placeholders only.
11. **Pagination bounds** — `per_page` clamped to 1–24 to prevent huge queries.
12. **`Accept: application/json`** on Axios so Laravel responds in JSON.

**Not implemented in the current project:** CSRF token validation for the API (not needed with stateless Bearer auth), rate limiting beyond Laravel defaults, password reset/email verification flows, user avatar uploads, HTTPS enforcement, S3 remote storage.

---

## 19. Error Handling

### 19.1 Backend (`bootstrap/app.php`)

- `shouldRenderJsonWhen(api/*)` — all API errors return JSON, not HTML.
- `AuthenticationException` → `401` `"You must be logged in to access this resource."`
- `AccessDeniedHttpException` → `403` `"You are not allowed to perform this action."`
- `NotFoundHttpException` → `404` `"Resource not found."`
- `ValidationException` → `422` `{ message:"The given data was invalid.", errors:{field:[...]} }`
- `redirectGuestsTo` → API requests return JSON; web requests redirect to `/login`.
- **500s** → default Laravel JSON error when `Accept: application/json` and `APP_DEBUG` is on locally; production `APP_DEBUG=false` returns a generic message. No custom 500 renderer exists.

### 19.2 Frontend

- `extractErrorMessage()` shows `response.data.message` (friendly copy) — technical details are never shown.
- `extractFieldErrors()` maps Laravel `errors` → inline field messages under inputs.
- Views handle specific statuses: `PropertyDetails.vue` checks `err.response.status === 404` for "This property could not be found."
- Dedicated states: `LoadingSpinner` (loading), `EmptyState` (no results / empty list), error banners with **"Try again"** buttons on Home and Properties.
- `404` pages (unknown routes) → `NotFound.vue`.
- Global `401` handling via the Axios interceptor → logout + reload.

---

## 20. Performance

### Already optimized

1. **Eager loading** — `->with('user:id,name,email,phone,avatar')` on listings prevents N+1 user lookups; only needed columns are loaded.
2. **Pagination** — listings use `paginate()`; the frontend never loads all rows.
3. **Bounds on query params** — `per_page` clamped to 1–24, `page` ≥ 1.
4. **Database index** — composite index `(status, property_type)` supports the most common listing filter path.
5. **Backend filtering** — filtering happens in SQL, not in JS.
6. **`limit 6` on Home** — featured grid fetches only 6.
7. **Lazy-loaded images** — `loading="lazy"` on property card images.
8. **Route-level code splitting** — all views are lazy imports (`() => import(...)`), so Vite emits separate chunks loaded on demand.

### Possible performance improvements (not currently implemented)

- Full-text search / MySQL `FULLTEXT` index on `title`+`description` instead of `LIKE %...%`.
- Caching (`Cache` store is configured to `database` but no cache usage exists).
- Image resizing/compression pipeline (uploads currently store original files; no `intervention/image` or `imagine` is installed).
- Database-paginate `my-properties` (currently `->get()`, no pagination).
- Index on `slug` already unique; could add indexes for `price_per_night`, `guests`, `user_id`+`status` if tables grow.
- Nginx reverse-proxy / CDN in production.

---

## 21. Complete User Journey (technical)

1. **Open StayNest** — `GET /` → `web.php` catch-all → `app.blade.php` → Vite assets → Vue mounts.
2. **Browse** — Home mounts → `propertyStore.fetchProperties({per_page:6})` → `GET /api/v1/properties` → grid of `PropertyCard`s.
3. **Search** — hero `SearchBar` → `router.push({name:'properties', query})` → URL changes → `Properties.vue` reloads.
4. **Filter** — `FilterPanel` adds `guests`; URL drives everything; "Clear filters" resets the URL.
5. **Open property** — click card → `/properties/{slug}` → `store.fetchProperty(slug)` → details page.
6. **Register** — `/register` → POST `/api/v1/register` → 201 + token → stored → redirected Home.
7. **Login** — `/login` → POST `/api/v1/login` → 200 + token → stored → redirected.
8. **Add property** — `/properties/create` (guard) → multipart POST → 201 → redirect to details with success banner.
9. **View own listing** — `/my-properties` → GET `/api/v1/my-properties` (Bearer) → rows with status badges.
10. **Edit listing** — `/properties/{id}/edit` → PUT multipart → 200 → redirect to details with "updated" banner.
11. **Delete listing** — confirm modal → DELETE → 200 → list refetched.
12. **Logout** — POST `/api/v1/logout` (token revoked) → localStorage cleared → Home.

---

## 22. Complete Request Lifecycle (one representative request)

Using **Add Property** (`POST /api/v1/properties`) as the canonical example:

```
Browser: PropertyForm submit → payload (includes File)
   │
   ▼  Vue
CreateProperty.handleSubmit(payload)
   │
   ▼  Pinia
propertyStore.createProperty(payload)
   │
   ▼  Service
propertyService.create(payload) → toFormData(payload)
   │
   ▼  Axios
api.post('/properties', formData)
      request interceptor: Authorization: Bearer <token>
      axios sets multipart/form-data boundary
   │
   ▼  Laravel Route
POST /api/v1/properties  (auth:sanctum)
   │
   ▼  Middleware
Sanctum validates token → resolves User
   │
   ▼  Form Request
StorePropertyRequest::rules() → fails → 422 errors
   │
   ▼  Policy
authorize('create', Property::class) → true
   │
   ▼  Controller
image->store('properties','public') → Property::create([...])
   │
   ▼  Eloquent → MySQL
INSERT INTO properties (...) VALUES (...)
   │
   ▼  Resource
new PropertyResource($property->load('user'))
   │
   ▼  JSON
201 { success, message, data:{ ...image_url... } }
   │
   ▼  Axios
response resolved; interceptor passes through
   │
   ▼  Vue
createProperty returns → router.push(details?created=1)
   │
   ▼  UI
PropertyDetails renders success banner
```

---

## 23. Architecture Diagrams

### A. Authentication (login)

```
Login.vue ──► authStore.login ──► authService ──► POST /api/v1/login
                                                            │
                    localStorage ◄── token+user ◄── AuthController (Hash::check)
                    (staynest_token)                          │
                    router.push(redirect||home)          createToken()
```

### B. Home page loading

```
Home.vue ──► store.fetchProperties({per_page:6})
        ──► GET /api/v1/properties
        ──► PropertyController@index → published()→with(user)→filter→latest→paginate
        ──► MySQL → PropertyResource[] → PropertyCard grid
```

### C. Search/filter

```
SearchBar/FilterPanel ──► URL query ──► Properties.vue
        ──► buildPropertyQuery ──► GET /api/v1/properties?location&type&min_price&max_price&guests
        ──► scopeFilter() ──► composed WHERE ... LIMIT 12
        ──► results or EmptyState
```

### D. Add property

```
Navbar "Add Property" ──► /properties/create (guard) ──► PropertyForm
        ──► FormData(image) ──► POST /api/v1/properties ──► validation → policy
        ──► Storage(public/properties) + INSERT ──► 201 ──► details?created=1
```

### E. Edit property

```
MyProperties ──► /properties/{id}/edit ──► prefilled PropertyForm
        ──► PUT /api/v1/properties/{id} (multipart)
        ──► authorize('update') → replace image if new → UPDATE → 200
        ──► details?updated=1
```

### F. Delete property

```
MyProperties "Delete" ──► confirm modal ──► DELETE /api/v1/properties/{id}
        ──► authorize('delete') → delete image file → DELETE row → 200
        ──► fetchMyProperties() refresh
```

### G. Image upload

```
<input type=file> ──► client 2MB check ──► form.image (File) ──► FormData
        ──► multipart POST ──► StorePropertyRequest(image rules)
        ──► $file->store('properties','public') ──► /storage/properties/<rand>.jpg
        ──► DB path ──► image_url accessor ──► <img>
```

### H. Frontend–backend communication

```
Vue Views ⇄ Pinia Stores ⇄ Services ⇄ Axios (Bearer, FormData)
        ⇄ HTTP JSON ⇄ Laravel API (routes/middleware/controllers/requests/policies/models)
        ⇄ MySQL
```

### I. Database relationships

```
users ──1───── many──► properties
 (PK id)              (FK user_id → users.id, CASCADE)
                      (slug UNIQUE, status+property_type INDEX)
```

---

## 24. Interview Questions (with answers)

### BEGINNER

**Q1. What is StayNest?**
- *Short:* A property listing platform built with Laravel 11 + Vue 3.
- *Detail:* Travellers browse/search/filter published stays; registered hosts create and manage their own listings.
- *StayNest example:* Home page grid, explore filters, add/edit/delete own property, Sanctum login.

**Q2. What are the main features?**
- *Short:* Browse, search, filters, details, register/login, add/edit/delete property, my properties.
- *Detail:* All backed by a REST API with pagination, image uploads, ownership policies, responsive Tailwind UI.
- *Example:* `GET /api/v1/properties?location=Chandigarh&type=Apartment&max_price=5000`.

**Q3. What is Eloquent?**
- *Short:* Laravel's ORM.
- *Detail:* Maps tables to PHP models, provides relationships, query builder, scopes, pagination, mass-assignment protection.
- *Example:* `Property::published()->with('user')->filter($filters)->latest()->paginate(12)`.

**Q4. What is a migration?**
- *Short:* Version control for your database schema.
- *Detail:* PHP classes whose `up()` creates/alters tables; reproducible with `php artisan migrate`.
- *Example:* `create_properties_table` defines `user_id` FK, unique slug, status enum, composite index.

**Q5. What is a seeder/factory?**
- *Short:* Scripts that insert demo/test data.
- *Detail:* Factory generates fake model attributes; Seeder creates actual rows (3 users, 15 properties).
- *Example:* `DatabaseSeeder` copies 14 JPEGs into `storage/app/public/properties`.

**Q6. What is Laravel Sanctum?**
- *Short:* Lightweight token auth for SPAs/APIs.
- *Detail:* Issues personal access tokens stored hashed in `personal_access_tokens`.
- *Example:* `$user->createToken('auth-token')->plainTextToken` returned on login/register.

**Q7. What is a Vue component?**
- *Short:* Reusable reactive UI block.
- *Detail:* `<script setup>` + template; receives `props`, emits events.
- *Example:* `PropertyCard.vue` renders one listing; `PropertyForm.vue` shared by create and edit.

**Q8. What is Pinia?**
- *Short:* Vue state management.
- *Detail:* Stores hold shared reactive state and actions.
- *Example:* `authStore` holds token/user; `propertyStore` holds listings and pagination meta.

**Q9. What is Vue Router?**
- *Short:* Client-side router for SPAs.
- *Detail:* Maps URLs to components, supports guards and lazy loading.
- *Example:* `/properties/:slug` → `PropertyDetails.vue`; `requiresAuth` guard redirects to login.

**Q10. What is Axios?**
- *Short:* Promise HTTP client.
- *Detail:* Used to call the Laravel API with headers, interceptors and FormData support.
- *Example:* `api.js` instance + Bearer interceptor + 401 handler.

**Q11. Why MySQL?**
- *Short:* Reliable, widely used relational DB.
- *Detail:* Fits relational data (users → properties FK), ACID, indexed queries, runs on XAMPP.
- *Example:* Foreign key `user_id` with cascade delete.

**Q12. What is an API Resource?**
- *Short:* Transformer that shapes models into JSON.
- *Detail:* Controls exactly which fields appear in responses.
- *Example:* `PropertyResource` adds computed `image_url` and includes `user` when loaded.

### INTERMEDIATE

**Q13. How does registration work end-to-end?**
- *Short:* Vue → Axios → API → validation → hashed create → token → stored in localStorage.
- *Detail:* `RegisterRequest` validates; `User` model's `hashed` cast hashes the password; token saved to `personal_access_tokens`; `authStore` persists to `staynest_token`.
- *Example:* See Section 7.1.

**Q14. How is the password stored?**
- *Short:* Bcrypt hash.
- *Detail:* `'password' => 'hashed'` cast in `User`; verified with `Hash::check` on login.
- *Example:* `AuthController::login()`.

**Q15. How does the token work?**
- *Short:* Bearer token in the `Authorization` header.
- *Detail:* Created via Sanctum, stored hashed in DB; Axios request interceptor attaches it; `auth:sanctum` middleware validates it.
- *Example:* `config.headers.Authorization = 'Bearer ' + localStorage.getItem('staynest_token')`.

**Q16. How does logout work?**
- *Short:* Revokes the token server-side, clears localStorage.
- *Detail:* `AuthController::logout()` deletes `currentAccessToken()`; `authStore.clear()` removes both keys.
- *Example:* `POST /api/v1/logout`.

**Q17. How does search/filter work?**
- *Short:* Backend Eloquent scopes.
- *Detail:* Query params → `Property::scopeFilter()` composes `when(...)` conditions → SQL WHERE → paginated results.
- *Example:* `GET /api/v1/properties?location=Chandigarh&type=Apartment&max_price=5000` (Section 11).

**Q18. Why filter on the backend?**
- *Short:* Correct pagination, less data transfer, single source of truth.
- *Detail:* Only filtered rows are counted/paged; the client never receives the whole table.
- *Example:* URL drives `Properties.vue` reloads.

**Q19. How does image upload work?**
- *Short:* File → FormData → multipart POST → storage → DB path → image_url.
- *Detail:* `image` file rules (≤2MB), `store('properties','public')`, random names, `storage:link` public access, `image_url` accessor.
- *Example:* Section 12.

**Q20. What is a Form Request and why use it?**
- *Short:* Dedicated validation class.
- *Detail:* Encapsulates `authorize()` + `rules()` + `messages()`; keeps controllers clean.
- *Example:* `StorePropertyRequest` with image rules and friendly messages.

**Q21. How does the property listing page work?**
- *Short:* Fetch → store → grid → pagination.
- *Detail:* `Properties.vue` syncs filters to the URL query; pagination pushes `page`; `watch(route.query)` reloads.
- *Example:* `Pagination.vue` emits `change(page)` → `goToPage`.

**Q22. How is a slug generated?**
- *Short:* `Str::slug` + uniqueness suffix.
- *Detail:* `Property::generateSlug()` checks for collisions and appends `-2`, `-3`…
- *Example:* "Urban Nest Apartment" → `urban-nest-apartment`.

**Q23. How are properties ordered?**
- *Short:* `latest()`.
- *Detail:* `ORDER BY created_at DESC` on the listing query.
- *Example:* `PropertyController::index()`.

**Q24. What is eager loading?**
- *Short:* Loading relations in fewer queries.
- *Detail:* `with('user:id,...')` issues one join/query instead of N queries.
- *Example:* Prevents N+1 on the listings grid.

**Q25. How does pagination work?**
- *Short:* Laravel `paginate()` + frontend page buttons.
- *Detail:* API returns `meta {current_page,last_page,per_page,total}`; Vue renders pages and preserves query filters.
- *Example:* `Pagination.vue`.

**Q26. How does the frontend handle a 401?**
- *Short:* Global interceptor logs the user out.
- *Detail:* Axios response interceptor clears storage and dispatches `staynest:logout`; `App.vue` reloads.
- *Example:* Expired/invalid token on any protected call.

**Q27. What is CORS and how is it configured?**
- *Short:* Browser security policy controlling cross-origin requests.
- *Detail:* `config/cors.php` allows `api/*` paths from `CORS_ALLOWED_ORIGINS`.
- *Example:* Dev frontend at 5173 calls API at 8000.

**Q28. What happens when an unauthenticated user opens `/properties/create`?**
- *Short:* Redirect to login with redirect-back.
- *Detail:* Router `requiresAuth` guard checks localStorage token → `{name:'login', query:{redirect}}`; after login the user is returned.
- *Example:* `Login.vue` uses `route.query.redirect`.

### ADVANCED

**Q29. How is authorization implemented?**
- *Short:* Laravel Policies + `AuthorizesRequests`.
- *Detail:* `PropertyPolicy` compares `$user->id === $property->user_id`; controllers call `$this->authorize(...)`; failures become 403 JSON.
- *Example:* `update`/`delete` on another user's property → 403 (tested in `PropertyOwnershipTest`).

**Q30. How does the SPA + REST separation work?**
- *Short:* Vue is static SPA; Laravel is pure JSON API.
- *Detail:* `web.php` catch-all serves the shell; `api.php` serves data; Axios bridges them.
- *Example:* One Laravel deploy hosting both the built frontend and the API.

**Q31. Why Laravel over Core PHP?**
- *Short:* Batteries included and secure by default.
- *Detail:* Routing, ORM, validation, auth, migrations, tests out of the box — writing these in core PHP is error-prone.
- *Example:* Form Requests + Policies + Resources saved hundreds of lines.

**Q32. Why Vue over Blade?**
- *Short:* Reactive, interactive SPA.
- *Detail:* State-driven UI, client-side routing, component reuse; Blade is server-rendered per page.
- *Example:* `PropertyForm.vue` reused by create and edit; instant filter feedback.

**Q33. Why the response envelope `{success, message, data, meta}`?**
- *Short:* Consistent, predictable API.
- *Detail:* Frontend utilities rely on `message` and `errors`; pagination reads `meta`.
- *Example:* `extractErrorMessage` uses `response.data.message`.

**Q34. How is validation exposed to Vue?**
- *Short:* 422 with `errors` object.
- *Detail:* Exception handler renders Laravel's `$e->errors()`; `extractFieldErrors` maps field→message.
- *Example:* `fieldErrors.title` under the title input in `PropertyForm.vue`.

**Q35. What is mass-assignment protection?**
- *Short:* `$fillable` whitelist.
- *Detail:* Only listed attributes can be set via `create/update`; prevents unexpected column writes.
- *Example:* `Property::$fillable`, controllers pass explicit arrays.

**Q36. How is file storage decoupled?**
- *Short:* Laravel Storage disks.
- *Detail:* Code writes to `Storage::disk('public')`; swapping to S3 later is a config change.
- *Example:* `$request->file('image')->store('properties', 'public')`.

**Q37. How does the app avoid N+1?**
- *Short:* `with()` eager loading.
- *Detail:* Listing loads owner once with limited columns.
- *Example:* `->with('user:id,name,email,phone,avatar')`.

**Q38. Why is a policy needed in addition to middleware?**
- *Short:* Middleware says *who can be here*; policy says *what they may do*.
- *Detail:* `auth:sanctum` only proves login; `PropertyPolicy` enforces ownership per-entity.
- *Example:* Logged-in user A gets 403 editing B's property.

**Q39. How would you scale search?**
- *Short:* Add MySQL FULLTEXT indexes.
- *Detail:* Replace `LIKE %…%` with `MATCH … AGAINST` for title/description.
- *Example:* Future improvement to `scopeFilter`.

**Q40. What happens to orphaned images?**
- *Short:* They are deleted.
- *Detail:* `update` deletes the replaced file; `destroy` deletes the property's file before deleting the row.
- *Example:* `Storage::disk('public')->delete($property->image)`.

**Q41. How are the tests structured?**
- *Short:* Feature tests against in-memory SQLite.
- *Detail:* `ApiTestCase` helpers (`authUser`, `propertyPayload`, `fakeImage`); 49 tests covering auth, CRUD, filters, ownership, drafts, images.
- *Example:* `php artisan test` — all green.

**Q42. What would you improve next?**
- *Short:* Search indexing, image optimization, caching, paginated my-properties, email flows.
- *Detail:* All are marked "possible improvements" in Section 20.

---

## 25. "Why did you use this?" — decision rationale

| Choice | Why | Actual evidence in StayNest |
| --- | --- | --- |
| **Laravel vs Core PHP** | Framework gives routing, ORM, auth, validation, migrations, tests | Controllers, Requests, Policies, Resources all use framework features |
| **Vue vs Blade** | Reactive SPA, client routing, component reuse | 9 router views, shared `PropertyForm`, Pinia reactivity |
| **MySQL** | Relational data, ACID, FKs, runs on XAMPP | `users`/`properties` with `user_id` FK and cascade delete |
| **REST API** | Clean frontend/backend boundary, reusable, testable | `/api/v1/*` JSON endpoints + 49 feature tests |
| **Sanctum** | Simple, stateless token auth for SPAs | `createToken`, `auth:sanctum`, `personal_access_tokens` |
| **Axios** | Interceptors for tokens/401s, FormData support | `api.js` request/response interceptors |
| **Pinia** | Shared state without prop drilling | `authStore`, `propertyStore` used across Navbar/views |
| **Eloquent** | Productive ORM with scopes/relations/pagination | `scopePublished`, `scopeFilter`, `with('user')`, `paginate()` |
| **Form Requests** | Validation kept out of controllers | `app/Http/Requests/Api/*` |
| **API Resources** | Explicit JSON contract | `PropertyResource` adds `image_url`, hides nothing sensitive |
| **Policies** | Entity-level ownership authorization | `PropertyPolicy::update/delete` → 403 for non-owners |
| **Foreign keys** | Referential integrity at DB level | `->constrained()->cascadeOnDelete()` |
| **Pagination** | No unbounded result sets | `paginate()` + `meta` + frontend `Pagination.vue` |
| **Backend filtering** | Correct paging, minimal transfer | `scopeFilter()` builds SQL; verified by filter tests |

---

## 26. Project Challenges (real, from the repository)

### Challenge 1 — Unauthenticated API calls returned 500 instead of 401
- **Problem:** Missing-token requests broke with a server error.
- **Cause:** The API had no JSON rendering for `AuthenticationException`.
- **Solution:** `bootstrap/app.php` `withExceptions` renders 401/403/404/422 as JSON for `/api/*`.
- **Learned:** Always shape your API's error contract centrally (commit `8de7924`).

### Challenge 2 — Policies weren't firing in controllers
- **Problem:** `$this->authorize()` had no effect.
- **Cause:** The base `Controller` lacked the `AuthorizesRequests` trait.
- **Solution:** Added `AuthorizesRequests` to `app/Http/Controllers/Controller.php` (commit `7b5ffd3`).
- **Learned:** Laravel auto-discovers policies, but the controller must actually use the authorization trait.

### Challenge 3 — Draft properties leaked to the public details endpoint
- **Problem:** Anyone could view a draft by guessing its slug.
- **Cause:** `show` loaded by slug without a status check.
- **Solution:** Resolve the Sanctum user on the public route and return 404 unless the requester is the owner (commit `1fcfa83`).
- **Learned:** "Public" endpoints still need ownership-aware guards; add tests for draft visibility.

### Challenge 4 — Unbounded pagination parameters
- **Problem:** `per_page=999999` or negative `page` could stress the DB.
- **Cause:** Params were passed straight to `paginate`.
- **Solution:** Clamp `per_page` to 1–24 and `page` to ≥1 (commit `b0b8f64`).
- **Learned:** Never trust client query params; validate/bound everything that touches SQL.

### Challenge 5 — Image upload broke with "Please choose a property image."
- **Problem:** Uploads with a selected image still failed `image.required`.
- **Cause:** The Axios instance hard-coded `Content-Type: application/json`; axios 1.19 then **converted the FormData to JSON**, silently dropping the file.
- **Solution:** Removed the global Content-Type from `api.js` so axios picks `multipart/form-data` for FormData and `application/json` for objects.
- **Learned:** When sending `FormData`, never set a JSON content-type globally; verify uploads end-to-end with a real multipart request.

### Challenge 6 — Mobile menu / delete modal UX
- **Problem:** Mobile menu stayed open, body scrolled behind the modal, Escape didn't close.
- **Solution:** Route-change watcher closes the menu; `watch(confirmOpen)` + `onBeforeUnmount` lock body scroll; a keydown listener closes on Escape (commits `3f2602e`, `a65eccd`).
- **Learned:** Modal/menu polish needs lifecycle-aware cleanup to avoid leaked scroll locks.

### Challenge 7 — Stale `public/hot` file broke the app in dev
- **Problem:** Browser loaded assets from a dead Vite server.
- **Cause:** A leftover `public/hot` file made Laravel think the dev server was running.
- **Solution:** Deleted `public/hot` (gitignored); the production build (`public/build`) is served instead.
- **Learned:** `public/hot` is transient state; if `npm run dev` isn't running, remove it and rebuild.

---

## 27. 60-Second Interview Explanation

> "StayNest is a property listing web app I built with Laravel 11 and Vue 3. The backend is a stateless REST API: Laravel exposes JSON endpoints for authentication using Sanctum bearer tokens, and for properties with search, filtering and pagination. Validation lives in Form Requests, authorization in a Property Policy, and data access goes through Eloquent against a MySQL database with `users` and `properties` linked by a foreign key. The frontend is a Vue 3 SPA — Vue Router for pages, Pinia for state, Axios for API calls, Tailwind for a responsive, mobile-friendly UI. A guest can browse, search and filter stays; a logged-in host can upload a property image, and create, edit or delete only their own listings. Ownership is enforced server-side by policies, so a user can never touch someone else's property. I also wrote 49 PHPUnit feature tests that cover registration, login, filters, pagination, image uploads and ownership, and the whole project is deployable with documented steps, environment files and CORS configured."

---

## 28. 2-Minute Interview Explanation

> "StayNest is a full-stack property listing platform. I chose Laravel for the backend because it gives me routing, Eloquent ORM, validation, authentication and testing out of the box, and Vue 3 on the frontend because I wanted a reactive single-page experience rather than server-rendered pages.
>
> The architecture is a clean API boundary. The Vue app runs as an SPA — Vue Router handles pages like `/properties/:slug`, Pinia stores share state, and Axios calls the Laravel API at `/api/v1`. Every response uses a consistent envelope: `success`, `message`, `data`, and `meta` for pagination.
>
> Authentication is Sanctum bearer tokens. On login or register the API returns a token which the Pinia auth store keeps in localStorage; an Axios interceptor attaches it to every request, and a global 401 handler logs the user out. Protected routes are wrapped in `auth:sanctum`, and the router also guards pages client-side.
>
> For the data model: a user has many properties. The `properties` table stores title, slug, type, location, price, guests, bedrooms, bathrooms, image path and a published/draft status. The `user_id` is a foreign key with cascade delete.
>
> The interesting parts: search and filtering run on the backend. The `Property` model has a `filter` query scope that conditionally adds WHERE clauses for location, type, min/max price and guests, then paginates — so the frontend never downloads the whole table. Image uploads go through FormData to a validated endpoint, Laravel stores them on the public disk with random names, and a `storage:link` symlink makes them publicly accessible; the resource exposes a computed `image_url`.
>
> Authorization is enforced twice: routes require a token, and a Property Policy ensures you can only edit or delete properties where `user_id` matches your id — otherwise the API returns 403. I built a reusable `PropertyForm` shared by create and edit, confirmation dialogs for delete, loading and empty states everywhere, and responsive Tailwind styling for mobile.
>
> Finally, I have 49 PHPUnit feature tests running against an in-memory SQLite database that prove registration, login, CRUD, filters, draft visibility, image uploads and ownership rules all behave correctly. Deployment is documented in the README — build the frontend with Vite, let Laravel serve it from `public/`, migrate and seed MySQL, and run `storage:link`."

---

## 29. Interview Cheat Sheet — concepts you must know

1. **MVC** — Model (data), View (UI), Controller (logic). Laravel: Models/Eloquent, Vue views, controllers orchestrate.
2. **REST API** — resource-style HTTP endpoints; GET reads, POST creates, PUT updates, DELETE removes.
3. **Sanctum** — issues stateless Bearer tokens for SPAs; stored (hashed) in `personal_access_tokens`.
4. **Authentication vs Authorization** — "who are you?" vs "what may you do?". Middleware = auth; Policies = authorization.
5. **Eloquent** — Laravel's ActiveRecord ORM; maps tables to objects, chains queries.
6. **Relationships** — `hasMany` (User → Property), `belongsTo` (Property → User); foreign key `user_id`.
7. **Form Requests** — validation classes run before controllers; 422 with `errors` on failure.
8. **API Resources** — transform models to JSON; `PropertyResource` exposes `image_url`, nested `user`.
9. **Vue Composition API** — `<script setup>`, `ref/reactive/computed/watch/lifecycle`; logic organized by concern.
10. **Vue Router** — client-side routing + guards (`requiresAuth`, `guestOnly`) + lazy-loaded views.
11. **Pinia** — stores shared state and actions (`authStore`, `propertyStore`).
12. **Axios** — HTTP client with interceptors (Bearer token, 401 handler) and FormData uploads.
13. **MySQL foreign keys** — enforce referential integrity; `constrained()->cascadeOnDelete()`.
14. **HTTP status codes** — 200 OK, 201 Created, 401 Unauthenticated, 403 Forbidden, 404 Not Found, 422 Validation.
15. **Middleware** — runs before controllers; `auth:sanctum` protects API routes.
16. **Policies** — per-model authorization; `PropertyPolicy::update` = owner check.
17. **File uploads / Storage** — Laravel Storage disks, public disk, `store()`, `storage:link`.
18. **FormData** — browser form encoding for file uploads; multipart requests.
19. **CORS** — cross-origin access policy; origins allow-listed in `config/cors.php`.
20. **Environment variables** — `.env` (gitignored) vs `.env.example` (committed); `APP_URL`, `DB_*`, `CORS_ALLOWED_ORIGINS`, `VITE_API_URL`.
21. **Response envelope** — consistent `{success, message, data, meta}` JSON contract.
22. **Exception handling** — `bootstrap/app.php` renders API errors as JSON (401/403/404/422).
23. **Eager loading** — `with('user')` avoids N+1.
24. **Slug** — URL-friendly unique identifier (`Str::slug` + uniqueness suffix).
25. **Testing** — PHPUnit feature tests with in-memory SQLite and `RefreshDatabase`.

---

## Not implemented in the current project (for honesty)

- Bookings, payments, Stripe, reviews, favorites/wishlist, notifications, chat, messaging, admin dashboard, maps, AI, real-time functionality.
- Password reset and email verification flows (tables exist, no routes/controllers).
- User avatar upload and phone in the registration form (DB columns exist, no endpoints/inputs).
- S3/cloud storage (local public disk only).
- Rate limiting beyond Laravel defaults.
- Any deployed/live URL or CI/CD configuration.

---

*This document describes the StayNest codebase as it exists in the repository at the time of writing. Every claim was verified against the source: migrations, models, controllers, requests, resources, policies, routes, config, Vue views/components/stores/services, package files and tests.*
