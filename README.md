# Gahez Akeed E-Commerce

Laravel 13 e-commerce platform with an **admin panel** and **customer REST API**.

**Stack:** PHP 8.3, Sanctum, Spatie Permission, Spatie Translatable, Maatwebsite Excel, Vite, Bootstrap 5.

---

## Surfaces

| Surface | URL | Auth |
|---------|-----|------|
| Admin panel | `/admin` | Session + `admin\|super-admin` |
| Customer API | `/api/v1` | Sanctum bearer token |
| API docs (LaRecipe) | `/docs` | Public |
| Public site | `/` | Guest |

---

## Quick setup

```bash
composer install
cp .env.example .env   # if missing
php artisan key:generate
php artisan migrate --force
php artisan storage:link   # required for uploads (tickets, products, settings)
npm install && npm run build
php artisan db:seed        # demo data
```

**Dev (server + queue + logs + Vite):**

```bash
composer dev
```

**Run tests:**

```bash
composer test
```

---

## Documentation

| Resource | Path |
|----------|------|
| Browsable API docs | `/docs` (LaRecipe) |
| Full API reference | [docs/API.md](docs/API.md) |
| Developer guides | [docs/guides/](docs/guides/) |
| Module overview | [docs/guides/project-progress.md](docs/guides/project-progress.md) |
| Apidog / OpenAPI | [docs/apidog/](docs/apidog/) |

**API base URL:** `/api/v1` — use `Authorization: Bearer {token}` and optional `Accept-Language: en|ar`.

---

## Demo accounts

Password for all: `12345678` (after `php artisan db:seed`)

| Role | Email |
|------|-------|
| Super admin | `super-admin@gmail.com` |
| Admin | `admin@gmail.com` |
| Customer | `customer1@gmail.com` |

---

## Key directories

| Path | Contents |
|------|----------|
| `app/V1/Http/Controllers/` | Admin + API controllers |
| `app/V1/Services/` | Business logic (~40 services) |
| `routes/v1/api.php` | Customer API routes |
| `routes/v1/admin.php` | Admin panel routes |
| `resources/docs/1.0/` | LaRecipe markdown source (keep in sync with `docs/guides/`) |
| `tests/` | PHPUnit Feature + Unit tests |

---

## Environment notes

| Variable | Purpose |
|----------|---------|
| `APP_NAME` | Default app name (`Gahez Akeed`); overridden by `settings.app_name` when seeded |
| `QUEUE_CONNECTION` | `database` by default; run `php artisan queue:work` in production |
| `DATA_TRANSFER_SYNC` | `true` = synchronous Excel import/export |
| `FILESYSTEM_DISK` | `local`; public uploads use `storage/app/public` |

Run `php artisan storage:link` so ticket attachments, product images, and logos are web-accessible at `/storage/...`.

---

## License

MIT (Laravel framework). See [LICENSE](LICENSE) if present.
