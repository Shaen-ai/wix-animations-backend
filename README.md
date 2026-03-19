# Site Animation API (Laravel + MySQL)

Laravel backend for the Site Animation dashboard. Same API as the Node.js/MongoDB version.

## Requirements

- PHP 8.2+
- Composer
- MySQL 5.7+ (or SQLite for local dev)

## Setup

1. **Install dependencies**
   ```bash
   composer install
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Edit `.env` and set your database:
   - **MySQL**: `DB_CONNECTION=mysql`, create DB: `mysql -u root -e "CREATE DATABASE site_animation;"`
   - **SQLite** (no MySQL needed): `DB_CONNECTION=sqlite`, `DB_DATABASE=database/database.sqlite`, then `touch database/database.sqlite`

3. **Run migrations**
   ```bash
   php artisan migrate
   ```

4. **Seed banners (optional)**
   ```bash
   php artisan db:seed
   ```

5. **Start the server**
   ```bash
   php artisan serve --port=4000
   ```

## API Routes

| Method | Path | Description |
|--------|------|-------------|
| GET | `/` | Health check |
| GET | `/config` | Get widget config |
| POST | `/config` | Save widget config |
| GET | `/banners` | Get banner definitions |

## Assets

Banner images are served from `../assets/animations/`. A symlink is created automatically on boot from `public/assets` to the parent `assets` folder. Ensure the `assets/animations` directory exists at the project root.

## Dashboard

The dashboard expects the API at `http://localhost:4000`. Set `VITE_SETTINGS_URL` and `VITE_BANNERS_URL` in the dashboard's `.env` if using a different base URL.
