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
