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

* **Shipping:** USPS (Domestic Prices v3 REST API, OAuth 2.0), FedEx (SOAP/WSDL), Endicia (DAZzle XML labels)
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

## Queue & Background Jobs

| Queue | Purpose | Jobs |
|-------|---------|------|
| `payments` | Payment processing | `ProcessOrderCharge` |
| `emails` | Email notifications | `SendEmailNotification` |
| `default` | General background tasks | `RebuildCustomerSearchIndex` |

Queue workers are managed by **Laravel Horizon** (dashboard at `/horizon`, admin-only access).

```bash
# Local development
php artisan horizon

# Docker (runs automatically via docker-compose)
docker-compose up horizon
```

---

## Monitoring

- **Health check:** `GET /health` — Returns JSON with database, Redis, cache, and storage status
- **Error tracking:** Sentry integration (configure `SENTRY_LARAVEL_DSN` in `.env`)
- **Queue monitoring:** Horizon dashboard at `/horizon`
- **Structured logs:** Domain-specific log channels in `storage/logs/`:
  - `payment.log` (90 days retention)
  - `shipping.log` (30 days)
  - `auth.log` (30 days)
  - `email.log` (14 days)

---

## Modernization History

| Phase | Status |
|-------|--------|
| **Phase 2** | Frontend modernization (Vite, BS5, vanilla JS, Chart.js) - DONE |
| **Phase 4** | CakePHP 2.9.5 to Laravel 11 migration - DONE |
| **Phase 5** | Redis, queue workers (Horizon), monitoring (Sentry), structured logging - DONE |
| **USPS API** | Migrated from retired RateV4 XML API to Domestic Prices v3 REST API (OAuth 2.0) |

---

## USPS API Migration (March 2026)

The USPS Web Tools API (`secure.shippingapis.com/ShippingAPI.dll`, RateV4) was **retired on January 25, 2026**. The integration has been migrated to the new **Domestic Prices v3 REST API**.

| Old (Retired) | New |
|---------------|-----|
| `secure.shippingapis.com/ShippingAPI.dll` | `apis.usps.com/prices/v3/base-rates/search` |
| XML via GET query string | JSON via POST |
| USPS User ID in XML payload | OAuth 2.0 Bearer token |
| `RateV4` | Domestic Prices v3 |

### Setup

1. Sign up at [developers.usps.com](https://developers.usps.com)
2. Create an App to get your **Client ID** and **Client Secret**
3. Get your **EPS Account Number** from the USPS Business Customer Gateway
4. Add to `.env`:

```env
USPS_CLIENT_ID=your_client_id
USPS_CLIENT_SECRET=your_client_secret
USPS_ACCOUNT_NUMBER=your_eps_account_number
```

### How It Works

- OAuth tokens are cached for 7 hours (tokens valid for 8h) via Laravel Cache
- Each configured rate class is queried individually against the v3 endpoint
- Results are returned in the same `['class_id', 'service', 'rate']` format used throughout the app
- Failed token requests throw `RuntimeException`; failed rate queries log warnings and continue

### Current Lookup Boundaries

- The built-in USPS calculator and order charge auto-rating path currently support only the standard machinable parcel lookup flow.
- Oversized and non-machinable parcels are rejected up front with a descriptive message instead of sending doomed requests that USPS will reject as `Package size exceeds...`.
- USPS Ground Advantage oversized / nonstandard pricing is not yet implemented as a separate lookup path, so those parcels still require manual handling.

### Files

| File | Purpose |
|------|---------|
| `app/Services/Shipping/UspsService.php` | Service class (OAuth + rate queries) |
| `config/shipping.php` | USPS credentials and rate class config |
| `.env` / `.env.example` | `USPS_CLIENT_ID`, `USPS_CLIENT_SECRET`, `USPS_ACCOUNT_NUMBER` |

---

## Known Issues / TODO

### Chrome App (Scale & Printer) — Needs Replacement

The `chrome_app/` directory contains a **Chrome App** (`manifest_version: 2`) that handled serial scale reading and Zebra label printing. **Chrome Apps were deprecated in 2020 and fully removed in 2024**, so this no longer works.

**Current state:**
- The navbar settings dropdown (Printer IP, Scale ID, Scale Status) renders the UI but has **no JavaScript** to persist or read the values — settings are inert
- The Chrome App used `chrome.serial` and `chrome.usb` APIs that only existed in Chrome Apps, not extensions
- The `externally_connectable` manifest only lists old domains (`account.apobox.com`, `apobox.dev`), not the Railway deployment URL
- No web app JS exists to send messages to the Chrome App — the bridge code was never migrated from CakePHP

**Recommended replacement:**
- **Scale reading:** Use the [Web Serial API](https://developer.mozilla.org/en-US/docs/Web/API/Web_Serial_API) (Chrome 89+) — no extension needed, works directly from page JS
- **Zebra printing:** Use network HTTP POST to `http://{printer_ip}/pstprnt` from JS, or the backend's existing `ZebraLabelService` TCP socket approach
- **Settings persistence:** Wire up `localStorage` for Printer IP / Scale ID / Scale Status so the navbar dropdown actually saves and loads values

**Files involved:**
| File | Purpose |
|------|---------|
| `chrome_app/scale.js` | Serial scale communication + USB/network printing |
| `chrome_app/background.js` | Chrome App lifecycle + default serial options |
| `chrome_app/window.js` | Serial port config UI |
| `chrome_app/manifest.json` | Chrome App manifest (deprecated format) |
| `resources/views/partials/admin-navbar.blade.php` | Settings dropdown (Printer IP, Scale ID, Scale Status) |
| `app/Services/Shipping/ZebraLabelService.php` | Backend ZPL generation + network print (TCP:9100) |

---

## License

Copyright (c) 2016 APO Box
