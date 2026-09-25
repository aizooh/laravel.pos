# Mamicar POS

Point-of-sale system for a small cyber café + accessories shop.

## Features

- Product inventory with stock adjustments and low-stock alerts
- Cyber services catalog
- Fast POS screen with mixed product/service cart
- Sales with receipts (Cash / M-PESA / Card)
- Void & unvoid with full audit trail
- Expenses tracking
- Daily financial position (opening / closing) across banks, M-PESA, cash
- Reports: daily, monthly, inventory, void log
- User management with roles (admin / attendant)

## Tech Stack

- Laravel 12
- PHP 8.2+
- SQLite (default) — easily switchable to MySQL/PostgreSQL
- Blade + vanilla JS

## Local Setup

```bash
git clone <repo-url> mamicar-pos
cd mamicar-pos
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve