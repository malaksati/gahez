# Queue & import setup guide

Covers background jobs (notifications, spreadsheet import/export) and **Maatwebsite Excel** data transfer for catalog entities.

---

## File uploads

Public uploads (products, tickets, settings logo, avatars) use the `public` disk at `storage/app/public/`.

```bash
php artisan storage:link
```

Required for ticket attachment URLs and product images to resolve at `/storage/...`.

---

## Queue configuration

### `.env` keys

```env
QUEUE_CONNECTION=database

# Import/export: true = run immediately (no worker)
# false = background jobs (requires queue:work)
DATA_TRANSFER_SYNC=true
```

Optional (`config/queue.php`):

```env
DB_QUEUE_TABLE=jobs
DB_QUEUE_RETRY_AFTER=90
REDIS_QUEUE_CONNECTION=default   # if using redis driver
```

### Database tables

Migrations create `jobs`, `job_batches`, `failed_jobs`.

### Start the worker

Required when `QUEUE_CONNECTION` is not `sync` **and** you queue notifications or async imports.

**Queued notifications include:** `OrderStatusUpdatedNotification` (database + mail + WhatsApp when enabled). Without a worker, customers will not receive inbox or WhatsApp updates after admin order status changes.

**WhatsApp (optional):** set `WHATSAPP_ENABLED=true` in `.env`. Pilot uses the `dummy` provider (logs only). Requires queue worker + verified customer phone when `WHATSAPP_REQUIRE_VERIFIED_PHONE=true`.

```bash
php artisan queue:work

# Production (supervisor example)
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

**Dev script** (`composer dev`) already runs `queue:listen` alongside the server.

---

## What uses the queue

| Job / notification | Queued? | Notes |
|--------------------|---------|-------|
| Spreadsheet import/export | Yes* | *Sync when `DATA_TRANSFER_SYNC=true` |
| Order status notifications | Yes | Customer + admin |
| Offer/coupon promo broadcasts | Yes | `CustomerBroadcastService` |
| Birthday reward notifications | Yes | Daily `birthdays:send-rewards` |
| New order / ticket admin alerts | No | Sent synchronously |

Jobs live in `app/Jobs/DataTransfer/`. Notifications in `app/Notifications/`.

---

## Import / export (data transfer)

### Supported entities

| Entity | Admin path | Template |
|--------|------------|----------|
| Categories | `/admin/categories` | Download from import page |
| Products | `/admin/products` | Download from import page |
| Variants | `/admin/variants` | Download from import page |
| Variant options | `/admin/variant-options` | Download from import page |

### File formats

`.xlsx`, `.xls`, `.csv` — max **20 MB** (`ImportSpreadsheetRequest`).

### Workflow

1. Admin → entity list → **Import / Export**
2. **Export** — downloads current data or generates async batch
3. **Import** — upload spreadsheet; creates `DataTransferBatch` record
4. Track batch status on batch detail page (`pending` → `processing` → `completed` / `failed`)
5. Download error/report file if failed rows exist

### Configuration (`config/data-transfer.php`)

| Env key | Default | Description |
|---------|---------|-------------|
| `DATA_TRANSFER_SYNC` | `true` | `true` = inline; `false` = queue worker |
| `DATA_TRANSFER_CHUNK_SIZE` | `250` | Rows per chunk |
| `DATA_TRANSFER_SKIP_MISSING_RELATIONS` | `true` | Skip unresolved FKs vs fail row |
| `DATA_TRANSFER_SKIP_EXISTING` | `true` | Skip duplicates by slug/SKU/name |
| `DATA_TRANSFER_DEFAULT_BRAND_ID` | null | Fallback brand when import has no brand |

### Core files

| File | Role |
|------|------|
| `app/V1/Services/DataTransferService.php` | Batch + dispatch |
| `app/V1/DataTransfer/Imports/*` | Row importers |
| `app/V1/DataTransfer/Exports/*` | Excel exporters |
| `app/Models/DataTransferBatch.php` | Batch status tracking |

---

## Recommended setups

### Local development (simple)

```env
QUEUE_CONNECTION=sync
DATA_TRANSFER_SYNC=true
```

No worker needed. Imports run in the HTTP request.

### Local development (test queues)

```env
QUEUE_CONNECTION=database
DATA_TRANSFER_SYNC=false
```

```bash
php artisan queue:work
```

### Production

```env
QUEUE_CONNECTION=database   # or redis
DATA_TRANSFER_SYNC=false
```

- Run `queue:work` under Supervisor/systemd
- Run scheduler for birthdays: `* * * * * php artisan schedule:run`
- Monitor `failed_jobs`: `php artisan queue:failed`

---

## Scheduler (related)

`routes/console.php`:

```bash
php artisan birthdays:send-rewards   # daily 08:00
```

Cron: `* * * * * cd /path && php artisan schedule:run`

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Imports hang | Set `DATA_TRANSFER_SYNC=true` or start `queue:work` |
| Notifications not sent | Check `QUEUE_CONNECTION`, run worker, check `failed_jobs` |
| Import batch stuck `processing` | Check logs, restart worker, inspect `DataTransferBatch` |
| Permission denied on import | User needs `manage categories/products/variants` |

---

## Reports export (not queued)

Admin **Reports** Excel/PDF downloads are **synchronous** via `ReportController` — separate from data transfer jobs.
