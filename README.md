# APO Box Account (v2)

[![CI](https://github.com/5ox/apobox_backend_v2/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/5ox/apobox_backend_v2/actions)

Account management application for APO Box customers. Allows users to create and manage APO Box accounts including orders, custom requests, addresses, and payment details. Provides two levels of admin users (managers and employees) to perform all administrative tasks.

* **Production URL:** https://account.apobox.com
* **Repository:** https://github.com/5ox/apobox_backend_v2
* **Modernization Roadmap:** See `APO_Box_Account_Codebase_Review.docx` in the repo root

> **Note:** This is the v2 modernization repository. It was created as a clean starting point from the original `loadsys/apobox-account` codebase to chart a path toward a modern, supported stack.

---

## Current Stack

| Layer | Current | Target |
|-------|---------|--------|
| **Backend** | CakePHP 2.9.5 | CakePHP 5.x or Laravel 11 |
| **PHP** | 5.6 | 8.1+ |
| **Database** | MySQL 5.5 | MySQL 8.0 |
| **Frontend** | Vanilla JS + Bootstrap 5.3 | - |
| **Build Tools** | Vite + Dart Sass | - |
| **Charts** | Chart.js 4 | - |
| **Caching** | Memcached | Redis |
| **Containerization** | Docker + Docker Compose | Docker + Docker Compose |
| **CI/CD** | GitHub Actions | GitHub Actions |

---

## Project Structure

```
├── Config/              # App configuration (routes, database, ACL, email, queue)
│   └── Schema/          # Database schema and migration SQL
├── Console/             # CLI commands (shells)
├── Controller/          # MVC controllers (15 controllers)
│   └── Component/       # Reusable controller components
├── Lib/                 # Shared libraries (Payment, FedEx, USPS, Endicia, Zebra)
├── Model/               # Data models (33 models)
│   └── Behavior/        # Model behaviors
├── Plugin/              # CakePHP plugins (Endicia, FedEx, USPS)
├── Serializer/          # JSON:API serializers
├── Test/                # PHPUnit tests & fixtures
│   ├── Case/            # Test cases
│   └── Fixture/         # Test data fixtures
├── View/                # Server-rendered templates (.ctp)
│   ├── Elements/        # Reusable view partials
│   ├── Layouts/         # Page layouts & email templates
│   └── [Controller]/    # Per-controller view directories
├── Vendor/              # Composer dependencies (not committed)
├── webroot/             # Public web directory
│   ├── css/             # Compiled stylesheets
│   ├── sass/            # SCSS source files
│   ├── js/              # JavaScript (bundled & source)
│   │   └── src/         # JS source files (18 modules)
│   ├── img/             # Images
│   └── widgets/         # Embeddable signup widgets
├── chrome_app/          # Chrome extension (scale & label printing)
├── docker/              # Docker configuration files
├── Dockerfile           # Container build definition
├── docker-compose.yml   # Local development orchestration
├── scripts/             # Build scripts (email, widget)
├── composer.json        # PHP dependencies
├── package.json         # Node.js dependencies
└── vite.config.js       # Vite build configuration
```

---

## Key Integrations

* **Shipping:** USPS, FedEx, Endicia (label generation and tracking)
* **Payment:** PayPal REST API, credit card validation (PayPal vault storage)
* **Hardware:** Zebra label printers, shipping scale (via Chrome extension)
* **Email:** HTML templates with CSS inlining (juice)
* **Background Jobs:** CakePHP Queue plugin (cron-based, every 14 minutes)

---

## Environment

### Requirements

* PHP 5.6+ (with intl, pdo_mysql, mbstring, openssl, memcached extensions)
* MySQL 5.5+
* Memcached (production)
* Node.js 18+ + npm (for frontend asset building)
* Docker + Docker Compose (recommended for local development)

### Development Setup (Docker)

```bash
git clone https://github.com/5ox/apobox_backend_v2.git
cd apobox_backend_v2

# Start the containers
docker-compose up -d

# Install PHP dependencies (inside container)
docker-compose exec web composer install

# Install frontend dependencies (on host)
npm install

# Build frontend assets
npm run build
```

### Development Setup (Legacy Vagrant)

```bash
git clone https://github.com/5ox/apobox_backend_v2.git
cd apobox_backend_v2
./bootstrap.sh vagrant
```

Add the following to `/etc/hosts`:

```
192.168.15.43 apobox.dev
```

The site will be available at https://apobox.dev. Default credentials: `test@loadsys.com` / `password`.

---

## Configuration

App configuration is stored in `Config/core.php`, extended by environment-specific files:

| File | Environment |
|------|-------------|
| `Config/core-dev.php` | Development |
| `Config/core-stage.php` | Staging |
| `Config/core-ci.php` | CI/CD |
| `Config/core-local.php` | Local overrides (not committed) |

Set the `APP_ENV` environment variable to select the configuration (e.g., `export APP_ENV=dev`).

---

## Database

All SQL changes are recorded in `Config/Schema/db_updates.sql`. The full schema is in `Config/Schema/database.sql`.

Changes are applied manually in production and automatically during initial provisioning in development.

---

## Build Commands

| Command | Description |
|---------|-------------|
| `npm run build` | Build JS bundle and compile SCSS via Vite |
| `npm run dev` | Watch mode — rebuild on file changes |
| `npm run build:email` | Build email templates with CSS inlining |
| `npm run build:widget` | Build the embeddable signup widget |
| `npm run build:app` | Package the Chrome extension |
| `npm run build:all` | Run all build tasks |

---

## Testing

Tests use PHPUnit with a minimum 67% code coverage enforced.

```bash
# Run all tests (inside VM or container)
bin/run-tests

# Run tests without coverage (faster)
bin/cake test app All

# Run PHP CodeSniffer
bin/run-codesniffer
```

Coverage reports are generated in `tmp/coverage/html/`.

---

## Branching

| Branch | Purpose |
|--------|---------|
| `main` | Primary branch (production-ready code) |

---

## Modernization Roadmap

This repository is the starting point for a phased modernization effort. See `APO_Box_Account_Codebase_Review.docx` for the full analysis and plan.

| Phase | Goal | Timeline |
|-------|------|----------|
| **Phase 1** | Upgrade PHP 5.6 to 8.1+, MySQL to 8.0 | 2-3 months |
| **Phase 2** | ~~Replace Grunt/JSPM/Compass with Vite, upgrade Bootstrap 5~~ **DONE** | - |
| **Phase 3** | Extract service layer, standardize API | 3-4 months |
| **Phase 4** | Migrate to CakePHP 5 or Laravel 11 | 4-6 months |
| **Phase 5** | Redis, queue workers, monitoring | Ongoing |

---

## License

Copyright (c) 2016 APO Box
