# Deployment Guide

This app runs as a **Laravel 12 + Filament 4** admin panel on **Vercel** (serverless
PHP) with a **Supabase** PostgreSQL database.

- Local development uses **SQLite**.
- Production uses **Supabase** via the Supavisor connection pooler.
- Database migrations are run **from your machine**, never from Vercel. Vercel only
  serves the application.

---

## 1. Local development

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item database/database.sqlite -ItemType File -Force
php artisan migrate:fresh --seed
php artisan serve
```

Open <http://localhost:8000> — you are redirected to `/admin`.

Demo login (created by `AdminUserSeeder`):

| Email | Password |
| --- | --- |
| `farmer@example.com` | `password` |

Override with `ADMIN_NAME`, `ADMIN_EMAIL` and `ADMIN_PASSWORD` before seeding.

> `php artisan migrate:fresh --seed` **wipes the database**. Only ever run it while
> `DB_CONNECTION=sqlite`. Never run it against Supabase.

---

## 2. Supabase setup

1. Create a project at <https://supabase.com>.
2. Open **Project Settings → Database → Connection string** and copy the
   **Connection pooling** details. They look like:

   | Setting | Value |
   | --- | --- |
   | Host | `aws-0-<region>.pooler.supabase.com` |
   | Port | `5432` (session mode) or `6543` (transaction mode) |
   | Database | `postgres` |
   | User | `postgres.<project-ref>` |
   | Password | your database password |

   Use the **pooler** host. The direct host `db.<project-ref>.supabase.co` is
   IPv6-only and will fail from many networks and from Vercel.

3. Prefer **port 5432** (session mode): it supports server-side prepared
   statements. If you must use **6543** (transaction mode), set
   `DB_EMULATE_PREPARES=true`.

---

## 3. Run migrations against Supabase

Temporarily switch `.env` to the pgsql block from `.env.example`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=aws-0-<region>.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.<project-ref>
DB_PASSWORD=<password>
DB_SSLMODE=require
DB_EMULATE_PREPARES=false
```

Then:

```powershell
php artisan config:clear
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder --force
```

> **Applying the multi-account migration.** `add_user_id_to_farm_tables` gives
> every farm table an owner and moves the existing rows to the oldest account.
> Run `php artisan migrate --force` against Supabase **before** deploying the
> corresponding code: adding the columns is backward compatible, so the old
> deployment keeps working while the schema is upgraded.

Verify the schema landed:

```powershell
php artisan migrate:status
```

Switch `.env` back to SQLite when you are done, or keep the pgsql block active if
you want to develop against Supabase.

---

## 4. Vercel project setup

Import the Git repository at <https://vercel.com/new>. The repo already contains:

- `vercel.json` — runtime `vercel-php@0.9.0` (PHP 8.5, includes `intl`,
  `pdo_pgsql` and `pgsql`), static routes for Filament assets, and serverless-safe
  runtime defaults (`/tmp` caches, `stderr` logging, database sessions/cache,
  synchronous queue).
- The deployment filesystem is **read-only**. Laravel writes its package and
  service manifests during boot, so `vercel.json` redirects every cache file to
  `/tmp`:
  `APP_PACKAGES_CACHE=/tmp/packages.php`, `APP_SERVICES_CACHE=/tmp/services.php`,
  `APP_CONFIG_CACHE`, `APP_EVENTS_CACHE`, `APP_ROUTES_CACHE`, and
  `VIEW_COMPILED_PATH=/tmp/views`. Without `APP_PACKAGES_CACHE` the first request
  fails with
  `The /var/task/user/bootstrap/cache directory must be present and writable.`
- `bootstrap/app.php` trusts the Vercel proxy (`trustProxies(at: '*')`). Vercel
  terminates TLS and forwards `X-Forwarded-Proto: https`; without this Laravel
  emits `http://` asset and form URLs, which browsers block as mixed content.
- `api/index.php` — forwards every request to Laravel's front controller.
- `.vercelignore` — excludes `vendor`, `node_modules`, `.env` and `.git`.

No build command is needed: `composer install` runs during the build and
`filament:upgrade` publishes the CSS/JS/font assets. Those assets are committed to
the repository as well, so they are always available to the static routes.

### Environment variables

Set these in **Vercel → Project → Settings → Environment Variables** for both
**Production** and **Preview**:

| Variable | Value |
| --- | --- |
| `APP_NAME` | `Piggery Management` |
| `APP_ENV` | `production` |
| `APP_KEY` | output of `php artisan key:generate --show` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://<production-domain>` (must be HTTPS, no trailing slash) |
| `LOG_CHANNEL` | `stderr` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | `aws-0-<region>.pooler.supabase.com` |
| `DB_PORT` | `5432` (or `6543` with `DB_EMULATE_PREPARES=true`) |
| `DB_DATABASE` | `postgres` |
| `DB_USERNAME` | `postgres.<project-ref>` |
| `DB_PASSWORD` | your Supabase database password |
| `DB_SSLMODE` | `require` |
| `DB_EMULATE_PREPARES` | `false` (session mode) / `true` (transaction mode) |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `sync` |
| `FILESYSTEM_DISK` | `local` |
| `REGISTRATION_STARTER_DATA` | `true` (give new accounts a starter set of cages) or `false` |

`APP_KEY` must be generated once and reused for every environment:

```powershell
php artisan key:generate --show
```

> `.env` is never uploaded to Vercel. Every secret above must be entered in the
> dashboard.

---

## 5. Deploy

```powershell
git add .
git commit -m "Piggery management system"
git push
```

Vercel builds and deploys automatically. Or deploy from the CLI:

```powershell
npx vercel --prod
```

### Which URL is actually live?

Vercel gives every project three kinds of hostnames:

| Kind | Example | Behaviour |
| --- | --- | --- |
| Production domain | `piggery-management-nu.vercel.app` | Always serves the newest production deployment. **Use this one.** |
| Deployment URL | `piggery-management-<deployment-id>-<team>.vercel.app` | Frozen at one build forever. Also behind Deployment Protection (SSO). |
| Git branch URL | `piggery-management-git-main-<team>.vercel.app` | Preview of the `main` branch. |

A **deployment URL never updates**. If you bookmark one, you will keep seeing
the code from that build — including bugs that were fixed later. When a fix
"doesn't work" on a URL like `…-3qr51cy27-my-project-a76a.vercel.app`, check the
**Domains** tab of the project for the production domain instead of the
deployment-specific URL.

To give the app a permanent address, add a custom domain in
**Vercel → Project → Settings → Domains** and set it as primary.

---

## 6. Post-deploy verification

1. `https://<your-project>.vercel.app/` shows the public landing page with
   **Sign in** and **Create account** buttons.
2. The login page renders with full styling (confirms the Filament asset routes).
3. Log in with the seeded admin account.
4. The dashboard shows the six stat cards and the farrowing / feed / medical
   widgets.
5. Create a cage, a pig, a breeding, and a feed transaction.
6. Record a farrowing from **Breedings → Record farrowing** and confirm a litter
   appears under **Litters**.
7. Register a second account at `/admin/register`. It must get its own cages and
   must not see any data from the first account.

---

## 7. Updating the app

- **Schema change:** add a migration locally, then run
  `php artisan migrate --force` against Supabase, then push the code.
- **New Filament assets:** run `php artisan filament:upgrade` locally, commit the
  regenerated `public/css|js|fonts/filament` folders, then push.
- **New dependency:** commit `composer.json` **and** `composer.lock`.

---

## 8. Troubleshooting

| Symptom | Fix |
| --- | --- |
| `500` on every page | `APP_KEY` missing or wrong in Vercel env vars. |
| `The /var/task/user/bootstrap/cache directory must be present and writable` | Read-only filesystem; make sure `vercel.json` sets `APP_PACKAGES_CACHE=/tmp/packages.php` and `APP_SERVICES_CACHE=/tmp/services.php`. |
| Unstyled panel, or login does nothing | Generated URLs are `http://`; confirm `bootstrap/app.php` calls `trustProxies(at: '*')` so `X-Forwarded-Proto: https` is honoured. |
| An old error still appears after a fix | You are on a **deployment URL**, not the production domain. See "Which URL is actually live?". |
| `Deprecated: Constant PDO::MYSQL_ATTR_SSL_CA` | Running PHP 8.5 with an old build; the fix resolves the constant via `Pdo\Mysql::ATTR_SSL_CA`. Redeploy the latest commit. |
| `could not find driver` | Runtime not `vercel-php@0.9.0`; check `vercel.json`. |
| `connection timed out` to Supabase | Using the direct `db.<ref>.supabase.co` host; switch to the pooler host. |
| `prepared statement ... already exists` | Using port `6543` without `DB_EMULATE_PREPARES=true`. |
| `SQLSTATE[08006] ... ssl` | Set `DB_SSLMODE=require`. |
| Unstyled admin panel | Filament assets missing; run `php artisan filament:upgrade` and commit them. |
| `419 Page Expired` on login | `SESSION_DRIVER` must be `database` and `APP_URL` must match the deployed domain. |
| Writes fail on `storage/` | Serverless filesystem is read-only except `/tmp`; keep `VIEW_COMPILED_PATH=/tmp/views`. |

---

## 9. Security checklist

- Registration is public and email verification is **intentionally disabled**
  because no mail provider is configured. Filament rate-limits sign-ups to two
  attempts per minute per IP. Enable `->emailVerification()` and
  `->passwordReset()` in the panel once SMTP/Resend is set up.
- Users change their own password from **Profile** (avatar menu → Profile).
- Change the seeded admin password immediately after the first login.
- Rotate the Supabase database password if it has ever been committed or shared.
- Keep `APP_DEBUG=false` in production.
- Restrict Supabase network access where possible and keep the pooler credentials
  only in Vercel's environment variables.
