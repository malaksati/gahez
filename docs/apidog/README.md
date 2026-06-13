# Apidog import guide

Import the Gahez Akid API into [Apidog](https://www.apidog.com/) using either file below.

## Files

| File | Format | Best for |
|------|--------|----------|
| `gahez-api.openapi.yaml` | OpenAPI 3.0 | Full schema, Apidog auto-docs |
| `gahez-api.postman_collection.json` | Postman v2.1 | Folders, examples, token script |

## Import steps (OpenAPI)

1. Open Apidog → your project
2. **Import** → **OpenAPI/Swagger**
3. Select `docs/apidog/gahez-api.openapi.yaml`
4. Confirm base URL: `http://gahez.test/api/v1`
5. Set environment variable `token` after login (see Auth folder)

## Import steps (Postman collection)

1. **Import** → **Postman Collection**
2. Select `docs/apidog/gahez-api.postman_collection.json`
3. Collection variables are pre-set:
   - `baseUrl` — `http://gahez.test/api/v1`
   - `token` — filled automatically by **Login** request test script

## Demo accounts

| Role | Email | Password |
|------|-------|----------|
| Customer | `customer1@gmail.com` | `12345678` |
| Super admin | `super-admin@gmail.com` | `12345678` |

Run `php artisan db:seed` if accounts are missing.

## Auth header

```
Authorization: Bearer {{token}}
Accept: application/json
Accept-Language: en
```

## Multipart endpoints (tickets & support chats)

Use **form-data** body type when attaching files:

| Endpoint | Fields |
|----------|--------|
| `POST /tickets` | `type`, `subject`, `description`, `attachments[0]` (file) |
| `POST /tickets/{id}/messages` | `message`, `attachments[0]` (file) |
| `POST /support-chats` | `subject`, `message`, `attachments[0]` (file) |
| `POST /support-chats/{id}/messages` | `message`, `attachments[0]` (file) |

Both field names work: **`attachments[0]`** (recommended) or **`attachment[0]`**.

Allowed file types: jpeg, png, jpg, gif, webp, pdf, doc, docx (max 5 MB).

Requires `php artisan storage:link` for attachment URLs to work in responses.

## Cart & checkout tips

- Send `quantity` and `variant_id` in **JSON body** for `PUT /cart/{product_id}`
- Prefer `PUT /cart/items/{cartItemId}` with `{ "quantity": N }` for updates
- Simple products: omit `variant_id` or pass `null`
- `POST /orders` requires `shipping_day`; use `is_fast_shipping: true` for same-day delivery (today only)

## Updating the collection

When routes change in `routes/v1/api.php`, update both OpenAPI and Postman files manually, or regenerate with Scribe in the future.

See also [../API.md](../API.md) and [../guides/project-progress.md](../guides/project-progress.md).
