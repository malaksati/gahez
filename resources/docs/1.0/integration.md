# Frontend integration guide

---

- [App launch](#app-launch)
- [Register / login flow](#register-login-flow)
- [Authenticated API client](#authenticated-api-client)
- [Typical purchase flow](#typical-purchase-flow)
- [Queue / email](#queue-email)

<a name="app-launch"></a>
## App launch

```
GET /store/config
```

Cache `app_name`, `currency`, and `logo_url` locally.

<a name="register-login-flow"></a>
## Register / login flow

```
POST /auth/register → verify → POST /auth/login → save token
```

<a name="authenticated-api-client"></a>
## Authenticated API client

```javascript
const api = async (path, options = {}) => {
  const res = await fetch(`${BASE_URL}${path}`, {
    ...options,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'Accept-Language': 'ar',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...options.headers,
    },
  });
  return res.json();
};
```

<a name="typical-purchase-flow"></a>
## Typical purchase flow

1. `GET /store/config` — app name, currency, logo
2. `GET /products`, `GET /categories/tree`, `GET /offers`
3. `POST /cart/{product_id}` — check `billable_quantity` / `subtotal` for offer pricing
4. `GET /cart/checkout-preview` — totals, gifts, cart limits, shipping options
5. `POST /addresses` (if none)
6. `POST /orders` — `shipping_day` + optional `is_fast_shipping`, optional `gift_offer_id`, `item_notes`
7. `GET /notifications` for status updates

### Cart updates

Prefer `PUT /cart/items/{cartItemId}` with JSON `{ "quantity": N }` when updating existing lines.

### Shipping selection

From checkout preview `shipping.options`:

- **Standard** — pick a weekday from `options[0].weekdays` (not today)
- **Fast** — set `is_fast_shipping: true` and `shipping_day` to today's weekday from `options[1].weekdays`

### Support tickets & chats with files

Use `multipart/form-data` for:

- `POST /tickets` (include required `type`: `complaint` or `recommendation`)
- `POST /tickets/{id}/messages`
- `POST /support-chats` and `POST /support-chats/{id}/messages`

Field names: `attachments[0]` or `attachment[0]` (both accepted). Same file type and 5 MB limits.

### Customer goals

`GET /goals` after login — show progress widgets in the app home or profile.

<a name="queue-email"></a>
## Queue / email

Promotional and order notifications are stored in `notifications` table. Email is sent when the user has an email address. Run `php artisan queue:work` in production if notifications are queued.
