# Running tests guide

Gahez uses **PHPUnit 12** with Laravel’s `php artisan test` runner. Tests use an **in-memory SQLite** database — no MySQL setup required.

---

## Quick commands

```bash
# Recommended (clears config cache first)
composer test

# Equivalent
php artisan test

# Direct PHPUnit
vendor/bin/phpunit

# Single file
php artisan test tests/Feature/CartCheckoutApiTest.php

# Single method
php artisan test --filter=test_customer_can_checkout

# Unit suite only
php artisan test --testsuite=Unit

# Feature suite only
php artisan test --testsuite=Feature
```

---

## Test environment

Configured in `phpunit.xml`:

| Setting | Value |
|---------|-------|
| `APP_ENV` | `testing` |
| `DB_CONNECTION` | `sqlite` |
| `DB_DATABASE` | `:memory:` |
| `QUEUE_CONNECTION` | `sync` |
| `CACHE_STORE` | `array` |
| `MAIL_MAILER` | `array` |

Tests **do not** use your `.env` database. Migrations run automatically when a test uses `RefreshDatabase`.

---

## Test suites

### Feature (`tests/Feature/`)

Integration tests against HTTP API and app flows:

| File | Covers |
|------|--------|
| `CartCheckoutApiTest.php` | Add to cart, checkout, empty cart, out-of-zone |
| `OfferCartTest.php` | Offer/cart interactions, BOGO pricing |
| `ProfileApiTest.php` | Profile show/update, birthdate |
| `StoreConfigApiTest.php` | `GET /store/config` theme payload |
| `DeliveryAssignmentTest.php` | Driver in-transit/delivered, COD payment |
| `BecomeDeliveryApiTest.php` | Become-a-driver application API |

### Unit (`tests/Unit/`)

Service and helper logic without full HTTP stack:

| File | Covers |
|------|--------|
| `OfferServiceTest.php` | Offer types, BOGO, category BOGO, max discounted qty |
| `BirthdayRewardServiceTest.php` | Birthday coupon/gift rewards |
| `SuperAdminAuthorizationTest.php` | Super-admin permissions |
| `CustomerBroadcastServiceTest.php` | Offer/coupon customer notifications |
| `CategorySortOrderTest.php` | Category `sort_order` auto-append |
| `AdminUserServiceTest.php` | Admin user business rules |
| `AnalyticsReportServiceTest.php` | Report calculations |
| `Import*Test.php` | Spreadsheet import parsers |

---

## Common patterns in tests

**Database tests** — use `RefreshDatabase`:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyTest extends TestCase
{
    use RefreshDatabase;
}
```

**API auth** — Sanctum acting as user:

```php
use Laravel\Sanctum\Sanctum;

Sanctum::actingAs($user);
$this->postJson('/api/v1/orders', [...]);
```

**Fixtures** — `tests/Support/CreatesOfferFixtures.php` builds brand/category/product/offer with `en`/`ar` translations.

**No seeders in tests** — tests create their own data via factories; demo accounts from `db:seed` are for manual/Apidog testing only.

---

## Before running tests

```bash
composer install
cp .env.example .env   # if fresh clone
php artisan key:generate
```

No `php artisan migrate` needed for tests (in-memory DB migrates per test class).

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Config cached wrong values | `php artisan config:clear` (or use `composer test`) |
| Permission errors in admin tests | Create roles inline or use `RoleSeeder` in test setup |
| `DB_URL` conflicts | `phpunit.xml` sets `DB_URL=` empty — ensure no override |

---

## CI / production checklist

- Run `composer test` on every PR
- Keep Feature tests for critical flows: auth, cart, checkout, store config
- Add Unit tests when changing services (`app/V1/Services/`)
