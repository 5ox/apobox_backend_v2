# APO Box Account (v2)

Account management application for APO Box customers. Allows users to create and manage APO Box accounts including orders, custom requests, addresses, and payment details. Provides two levels of admin users (managers and employees) to perform all administrative tasks.

* **Production URL:** https://account.apobox.com
* **Repository:** https://github.com/5ox/apobox_backend_v2
* **Modernization Roadmap:** See `APO_Box_Account_Codebase_Review.docx` in the repo root

---

## Current Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | Laravel 11 |
| **PHP** | 8.2+ |
| **Database** | MySQL 8.0 (InnoDB) |
| **Frontend** | Vanilla JS + Bootstrap 5.3 |
| **Build Tools** | Vite + Dart Sass + laravel-vite-plugin |
| **Charts** | Chart.js 4 |
| **Caching** | Redis |
| **Queue** | Redis (Laravel Queue) |
| **Auth** | Session (multi-guard) + Sanctum (API) |
| **Containerization** | Docker + Docker Compose |

---

## Project Structure

```
├── app/
│   ├── Auth/                # Custom password hasher (legacy MD5+salt support)
│   ├── Console/Commands/    # Artisan commands (reminders, search index, sync)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/         # JSON API controllers (Sanctum auth)
│   │   │   ├── Auth/        # Login controllers (customer, admin, Google OAuth)
│   │   │   ├── Customer/    # Customer portal controllers
│   │   │   └── Manager/     # Admin panel controllers (shared manager/employee)
│   │   ├── Middleware/       # IP whitelist, role check, admin layout
│   │   └── Requests/        # Form request validation (13 classes)
│   ├── Models/
│   │   ├── Concerns/        # Traits: MasksCreditCard, Searchable
│   │   └── OrderLineItems/  # STI subclasses (10 types on orders_total)
│   ├── Observers/           # Customer, OrderLineItem, AuthorizedName
│   ├── Policies/            # Order, Address, AuthorizedName, CustomPackageRequest
│   ├── Providers/           # App + View service providers
│   └── Services/
│       └── Shipping/        # USPS, FedEx, Endicia, Zebra label services
├── bootstrap/               # Laravel app bootstrap
├── config/                  # App config (apobox.php, shipping.php, auth.php)
├── database/
│   ├── migrations/          # 20 migration files (all tables)
│   ├── reference/           # Original CakePHP schema SQL (for reference)
│   └── seeders/             # Country, Zone, Insurance, OrderStatus seeders
├── public/                  # Web root (index.php, assets, images)
├── resources/
│   ├── js/                  # JavaScript source (Vite entry)
│   ├── sass/                # SCSS source (global, public, admin, email)
│   └── views/
│       ├── auth/            # Login/register/password reset templates
│       ├── components/      # Blade components
│       ├── customer/        # Customer portal views
│       ├── emails/          # Email notification templates
│       ├── errors/          # Error pages (404, 500)
│       ├── layouts/         # Page layouts (default, manager, error, email)
│       ├── manager/         # Admin panel views
│       ├── pages/           # Static pages (TOS, widget)
│       └── partials/        # Shared partials (navbar, footer, flash, sidebar)
├── routes/
│   ├── web.php              # Web routes (~100 routes)
│   ├── api.php              # API routes (Sanctum-protected)
│   └── console.php          # Scheduled commands
├── storage/
│   └── wsdl/                # FedEx WSDL files
├── tests/                   # PHPUnit tests
├── chrome_app/              # Chrome extension (scale & label printing)
├── scripts/                 # Build scripts (email, widget)
├── Dockerfile               # PHP 8.2 + Apache container
├── docker-compose.yml       # Local dev (app, queue, MySQL, Redis)
├── composer.json             # PHP dependencies
├── package.json              # Node.js dependencies
└── vite.config.js            # Vite build configuration
```

---

## Key Integrations

* **Shipping:** USPS (XML API), FedEx (SOAP/WSDL), Endicia (DAZzle XML labels)
* **Payment:** PayPal REST API (vault storage, authorize, charge)
* **Hardware:** Zebra label printers (ZPL, network printing), shipping scale (Chrome extension)
* **Email:** Blade email templates with HTML layout
* **Background Jobs:** Laravel Queue (Redis driver) with scheduled commands

---

## Environment

### Requirements

* PHP 8.2+ (with intl, pdo_mysql, mbstring, openssl, redis, soap, gd extensions)
* MySQL 8.0+
* Redis
* Node.js 18+ + npm (for frontend asset building)
* Composer 2
* Docker + Docker Compose (recommended for local development)

### Development Setup (Docker)

```bash
git clone https://github.com/5ox/apobox_backend_v2.git
cd apobox_backend_v2

# Copy environment file
cp .env.example .env

# Start the containers
docker-compose up -d

# Install PHP dependencies (inside container)
docker-compose exec app composer install

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations and seed data
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed

# Install frontend dependencies and build
npm install
npm run build
```

The site will be available at http://localhost:8080.

---

## Configuration

Environment configuration uses `.env` file (see `.env.example` for all options):

| Config File | Purpose |
|-------------|---------|
| `config/apobox.php` | App-specific settings (fees, reminders, billing) |
| `config/shipping.php` | Shipping API credentials (USPS, FedEx, Endicia, Zebra) |
| `config/auth.php` | Multi-guard auth (customer, admin, Sanctum) |

---

## Database

Migrations are in `database/migrations/`. Original CakePHP schema preserved in `database/reference/` for reference.

```bash
# Run migrations
php artisan migrate

# Seed reference data (countries, zones, insurance rates, order statuses)
php artisan db:seed

# Fresh migration + seed
php artisan migrate:fresh --seed
```

---

## Build Commands

| Command | Description |
|---------|-------------|
| `npm run build` | Build JS bundle and compile SCSS via Vite |
| `npm run dev` | Vite dev server with HMR |
| `npm run build:email` | Build email templates with CSS inlining |
| `npm run build:widget` | Build the embeddable signup widget |
| `npm run build:app` | Package the Chrome extension |
| `npm run build:all` | Run all build tasks |

---

## Artisan Commands

| Command | Description |
|---------|-------------|
| `php artisan app:customer-reminders --awaiting-payment` | Email customers with unpaid orders |
| `php artisan app:customer-reminders --partial-signups` | Email incomplete registrations |
| `php artisan app:customer-reminders --expiring-cards` | Email customers with expiring CCs |
| `php artisan app:customer-reminders --purge-partials` | Remove stale partial signups |
| `php artisan app:rebuild-search-index` | Rebuild FULLTEXT search index |
| `php artisan app:sync-customers-info` | Create missing customers_info records |
| `php artisan app:match-custom-orders` | Match custom requests to orders by tracking |

---

## Authentication

| Guard | Provider | Method |
|-------|----------|--------|
| `customer` | Customer model (session) | Email/password login |
| `admin` | Admin model (session) | Email/password + Google OAuth |
| `sanctum` | Admin model (tokens) | API token auth |

Legacy passwords (MD5+salt) are transparently upgraded to bcrypt on login.

---

## Modernization History

| Phase | Status |
|-------|--------|
| **Phase 2** | Frontend modernization (Vite, BS5, vanilla JS, Chart.js) - DONE |
| **Phase 4** | CakePHP 2.9.5 to Laravel 11 migration - DONE |

---

## License

Copyright (c) 2016 APO Box
