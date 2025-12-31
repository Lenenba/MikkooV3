# Mikkoo V3

This project is a Laravel 12 + Vue 3 application. It uses Inertia and Vite for frontend development.

## Prerequisites

- PHP 8.2 or higher
- [Composer](https://getcomposer.org/)
- Node.js 18 or higher and npm

## Installation

1. Clone the repository and change into the project directory.
2. Copy `.env.example` to `.env` and adjust your environment variables as needed.
   - Configure your database connection (`DB_CONNECTION`, `DB_DATABASE`, etc.). By default a SQLite database is used.
   - If you plan to use address autocompletion, provide a `VITE_GEOAPIFY_KEY`.
   - Optional role name variables can be set (`PARENT_ROLE_NAME`, `BABYSITTER_ROLE_NAME`, `SUPER_ADMIN_ROLE_NAME`).
3. Install PHP dependencies:
   ```bash
   composer install
   ```
4. Install Node dependencies:
   ```bash
   npm install
   ```
5. Generate the application key:
   ```bash
   php artisan key:generate
   ```
6. Run the database migrations:
   ```bash
   php artisan migrate
   ```
7. (Optional) Seed the database with demo data:
   ```bash
   php artisan db:seed
   ```
   This executes the `DevSeeder` defined in `database/seeders`.

## Development server

Start the application locally by running the Laravel server and the Vite dev server:

```bash
php artisan serve
npm run dev
```

You can also use the Composer script which runs both plus the queue worker:

```bash
composer run dev
```
