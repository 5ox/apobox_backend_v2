# Railway Surface Split

This app can now run in three modes via `APP_SURFACE`:

- `all`: local/dev mode, loads customer + admin + API routes
- `customer`: public/customer surface only
- `admin`: admin/warehouse surface + API routes only

## Recommended Railway layout

Use one Railway project and one environment per stage (`production`, `staging`).

Create these services from the same repo:

- `customer-web`
- `admin-web`
- `mysql`
- `redis`
- optional: `worker`

## Domains

- `customer-web` -> `app.apobox.com`
- `admin-web` -> `ops.apobox.com`

## Shared environment variables

Set these at the environment level:

- `APP_KEY`
- `DB_*`
- `REDIS_*`
- `PAYPAL_*`
- mail settings
- Sentry settings

## Service-specific variables

### customer-web

- `APP_SURFACE=customer`
- `APP_URL=https://app.apobox.com`
- `SESSION_COOKIE=apobox_customer_session`
- `SESSION_DOMAIN=app.apobox.com`

### admin-web

- `APP_SURFACE=admin`
- `APP_URL=https://ops.apobox.com`
- `SESSION_COOKIE=apobox_admin_session`
- `SESSION_DOMAIN=ops.apobox.com`
- `ADMIN_LEGACY_LOGIN=false`

## Verification checklist

### customer-web

- `/health` returns `200`
- `/account` works
- `/admin/login` returns `404`
- `/manager` returns `404`
- `/employee` returns `404`
- `/api/*` returns `404`

### admin-web

- `/health` returns `200`
- `/admin/login` works
- `/manager` works for manager accounts
- `/employee` works for employee accounts
- `/account` returns `404`
- `/orders` returns `404`
- `/api/*` is reachable only where expected

## Notes

- Keep `APP_SURFACE=all` for local development unless you are explicitly testing a single surface.
- Session cookies should not be shared across `app.apobox.com` and `ops.apobox.com`.
- The current `warehouse.ip` middleware can stay in place as defense in depth, but the admin service should still be protected upstream by SSO + MFA.
