# Piggery Management System

A multi-account piggery manager built with **Laravel 12** and **Filament 4**.
Anyone can register, and every account manages its own herd in complete
isolation from other farms.

## Accounts & privacy

- Public sign-up at `/admin/register`; the landing page at `/` links to it.
- Every cage, pig, breeding, farrowing, litter, feed, feed transaction, medical
  log and custom medication carries a `user_id` owner.
- All queries are scoped to the signed-in account, so one farm can never see or
  edit another farm's records. Identifiers such as `PIG-001` or cage `G1` only
  need to be unique inside a single account.
- The built-in medication catalog is shared and read-only; each account can add
  its own medications on top of it.
- New accounts start with an empty set of cages (configurable via
  `REGISTRATION_STARTER_DATA`) and no invented animals or stock.

## Modules

| Module | What it does |
| --- | --- |
| **Cages** | Gestation, farrowing, boar, weaning, grower and quarantine pens with capacity and live occupancy counts. |
| **Pigs** | Sows, boars, gilts and piglets with tag, breed, birth date, cage and status. |
| **Breedings** | Mating records with automatic expected farrowing date (breeding date + 114 days), pregnancy status and due/overdue tracking. |
| **Farrowings & Litters** | Delivery outcomes (born alive, stillborn, mummified) and litter-level piglet tracking. |
| **Feed** | Feed types with kg stock, bag weight, reorder level and a stock-in/out/adjustment ledger that keeps stock balances accurate. |
| **Health** | Medication catalogue and polymorphic medical logs (pigs or litters) with next-due dates and booster scheduling. |

## Dashboard

- Active sows and boars
- Farrowings due in the next 7 days (and overdue)
- Piglets currently nursing
- Feeds at or below their reorder level
- Medical schedules due or overdue

## Key business rules

- Gestation is **114 days**; the expected farrowing date is computed and stored on the breeding.
- A sow can only have **one active pregnancy** at a time.
- Farrowings and litters cannot be created manually — record a farrowing from a breeding so the litter is generated atomically.
- Feed stock is derived from the transaction ledger; stock-out larger than available stock is rejected.
- A litter can be weaned, have deaths recorded, or have one piglet promoted to a gilt in the herd.

## Local setup

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item database/database.sqlite -ItemType File -Force
php artisan migrate:fresh --seed
php artisan serve
```

Open <http://localhost:8000> for the landing page, then sign in at `/admin`:

- **Email:** `farmer@example.com`
- **Password:** `password`

Or register a second account at `/admin/register` and confirm that it starts
empty — that is the multi-account isolation working.

Change the password after first login. Override the seeded account with
`ADMIN_NAME`, `ADMIN_EMAIL` and `ADMIN_PASSWORD`.

## Tests

```powershell
php artisan test
```

61 tests cover models, scopes, validation rules, Filament resources, dashboard
widgets, panel workflows and cross-account isolation.

## Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for Vercel + Supabase setup, environment
variables, migration workflow and troubleshooting.
