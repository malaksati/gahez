# Frontend integration guide

---

- [App launch](#app-launch)
- [Register / login flow](#register-login-flow)
- [Authenticated API client](#authenticated-api-client)
- [Typical purchase flow](#typical-purchase-flow)
- [Theme usage](#theme-usage)
- [Queue / email](#queue-email)

<a name="app-launch"></a>
## App launch

```
GET /store/config
```

Apply `theme` colors, `font_family`, and layout modes. Cache locally.

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

1. `GET /store/config` — theme and branding
2. `GET /products`, `GET /categories/tree`, `GET /offers`
3. `POST /cart/{product_id}` — check `billable_quantity` / `subtotal` for offer pricing
4. `GET /cart/checkout-preview` — totals, gifts, free delivery
5. `GET /delivery-expected-time` — optional slot selection
6. `POST /addresses` (if none)
7. `POST /orders` — include optional `shift_id`, `gift_offer_id`, `item_notes`
8. `GET /notifications` for status updates

### Cart updates

Prefer `PUT /cart/items/{cartItemId}` with JSON `{ "quantity": N }` when updating existing lines.

### Support tickets & chats with files

Use `multipart/form-data` for:

- `POST /tickets` and `POST /tickets/{id}/messages`
- `POST /support-chats` and `POST /support-chats/{id}/messages`

Field names: `attachments[0]` or `attachment[0]` (both accepted). Same file type and 5 MB limits.

### Customer goals

`GET /goals` after login — show progress widgets in the app home or profile.

<a name="theme-usage"></a>
## Theme usage

| `category_layout` | UI suggestion |
|-------------------|---------------|
| `horizontal` | Horizontal scroll chips / carousel |
| `vertical` | Vertical list |

| `product_layout` | UI suggestion |
|------------------|---------------|
| `horizontal` | Row cards, image left |
| `vertical` | Grid or stacked cards |

<a name="queue-email"></a>
## Queue / email

Promotional and order notifications are stored in `notifications` table. Email is sent when the user has an email address. Run `php artisan queue:work` in production if notifications are queued.
