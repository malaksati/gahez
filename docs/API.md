# Gahez E-Commerce API

> Browsable version: visit `/docs` in the app (powered by LaRecipe).

REST API for the customer mobile/web app.

## Base URL

```
https://your-domain.com/api/v1
```

Local example (Laragon):

```
http://gahez.test/api/v1
```

---

## Authentication

Protected routes use **Laravel Sanctum** bearer tokens.

### Login

```http
POST /auth/login
Content-Type: application/json

{
  "login": "customer1@gmail.com",
  "password": "12345678"
}
```

`login` accepts **email** or **phone**.

**Response `200`:**

```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "user": {
      "id": 1,
      "name": "Customer1",
      "email": "customer1@gmail.com",
      "phone": "50001111",
      "birthdate": "1992-05-20",
      "image": "https://.../user_avatar.png",
      "is_active": true,
      "is_verified": true,
      "roles": ["user"]
    },
    "token": "1|xxxxxxxx",
    "token_type": "Bearer"
  }
}
```

### Authenticated requests

```http
Authorization: Bearer {token}
Accept: application/json
Accept-Language: en
```

### Logout

```http
POST /auth/logout
Authorization: Bearer {token}
```

---

## Headers

| Header | Required | Description |
|--------|----------|-------------|
| `Accept` | Recommended | `application/json` |
| `Content-Type` | For POST/PUT/PATCH | `application/json` |
| `Authorization` | Protected routes | `Bearer {token}` |
| `Accept-Language` | Optional | `en` or `ar` (translations) |

---

## Response conventions

### Success (typical)

```json
{
  "success": true,
  "message": "Optional message",
  "data": { }
}
```

Laravel API resources may return `{ "data": [...] }` with pagination `links` / `meta`.

### Validation error `422`

```json
{
  "message": "Validation failed.",
  "errors": {
    "address_id": ["Please select a delivery address."]
  }
}
```

### Not found `404`

```json
{
  "success": false,
  "message": "Order not found."
}
```

---

## Rate limits

| Group | Limit |
|-------|-------|
| Most authenticated routes | 60 requests / minute |
| Public catalog routes | 30 requests / minute |
| Login / register / password reset | Stricter per-route limits |

---

# Public endpoints (no auth)

## Store config & theme

Load once on app startup. Theme is set by **admin only**.

```http
GET /store/config
```

**Response:**

```json
{
  "success": true,
  "data": {
    "app_name": "Gahez",
    "currency": "KWD",
    "logo_url": "https://.../logo.png",
    "theme": {
      "primary_color": "#faad28",
      "secondary_color": "#f8a713",
      "category_layout": "horizontal",
      "product_layout": "vertical",
      "font_family": "Cairo"
    }
  }
}
```

| Theme field | Values |
|-------------|--------|
| `category_layout` | `horizontal`, `vertical` |
| `product_layout` | `horizontal`, `vertical` |
| `font_family` | `Cairo`, `Inter`, `Poppins`, `Roboto`, `Tajawal` |

---

## Auth — Register

```http
POST /auth/register
```

| Field | Type | Rules |
|-------|------|-------|
| `name` | string | required |
| `email` | string | required without `phone`, unique |
| `phone` | string | required without `email`, unique |
| `password` | string | required, confirmed |
| `password_confirmation` | string | required with password |
| `birthdate` | date | optional, before today (`Y-m-d`) |

User must **verify email or phone** before login.

---

## Auth — Verify

```http
POST /auth/verify-email
{ "email": "...", "code": "123456" }

POST /auth/verify-phone
{ "phone": "...", "code": "123456" }

POST /auth/resend-verification-code
{ "email": "..." }  // or phone
```

---

## Password reset

```http
POST /auth/reset-password/send-code
{ "email": "..." }  // or phone

POST /auth/reset-password/verify-code
{ "email": "...", "code": "123456" }

POST /auth/reset-password/set-new-password
{
  "email": "...",
  "code": "123456",
  "password": "NewPass1!",
  "password_confirmation": "NewPass1!"
}
```

---

## Catalog

### Categories

```http
GET /categories
GET /categories?paginate=1&per_page=15
GET /categories/tree
GET /categories/{id}
```

### Products

```http
GET /products
GET /products/{id}
GET /products/slug/{slug}
```

**Query filters:** `search`, `category_id`, `min_price`, `max_price`, `featured`, `is_new`, `type`, `sort`, `per_page`

### Brands, branches, sliders, offers

```http
GET /brands
GET /brands/{id}
GET /branches
GET /branches/{id}
GET /sliders
GET /offers
```

---

# Authenticated — Customer

## Profile

```http
GET /profile
PATCH /profile
```

**Update body (all optional):**

```json
{
  "name": "Sara",
  "email": "sara@example.com",
  "phone": "50001234",
  "birthdate": "1995-03-15",
  "password": "NewPass1!",
  "password_confirmation": "NewPass1!",
  "remove_image": true
}
```

`birthdate`: `Y-m-d` or `null` to clear.  
Profile image: `multipart/form-data` field `image`.

```http
GET /auth/user
```

Same user object as profile.

---

## Notifications

In-app inbox (offers, coupons, orders, etc.).

```http
GET /notifications?per_page=20
POST /notifications/{id}/read
POST /notifications/mark-all-read
```

**List response:**

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "type": "offer_promotion",
      "title": "New offer at Gahez",
      "message": "New offer: 15% off Apples",
      "data": {
        "offer_id": 1,
        "coupon_code": null
      },
      "read_at": null,
      "created_at": "2026-06-09T10:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 5,
    "unread_count": 3
  }
}
```

---

## Addresses

```http
GET /addresses
POST /addresses
GET /addresses/{id}
PUT /addresses/{id}
DELETE /addresses/{id}
```

**Create body:**

```json
{
  "name": "Home",
  "address": "Block 5, Street 12, Salmiya",
  "latitude": "29.3375",
  "longitude": "48.0758",
  "phone": "50001111",
  "city": "Salmiya",
  "state": "Hawalli",
  "is_default": true
}
```

---

## Cart

```http
GET /cart
GET /cart/checkout-preview
POST /cart/apply-coupon
POST /cart/{product_id}
PUT /cart/{product_id}
PATCH /cart/{product_id}
PUT /cart/items/{cart_item_id}
PATCH /cart/items/{cart_item_id}
DELETE /cart/{product_id}
DELETE /cart
```

### Add to cart

```http
POST /cart/12
Content-Type: application/json

{ "quantity": 2, "variant_id": 5 }
```

- `quantity` — optional, default `1`
- `variant_id` — **required** for variable products; omit or `null` for simple products

### Update quantity

By product (include `variant_id` when the line has a variant):

```http
PUT /cart/12
Content-Type: application/json

{ "quantity": 3, "variant_id": 5 }
```

By cart item ID (preferred — no `variant_id` needed):

```http
PUT /cart/items/42
Content-Type: application/json

{ "quantity": 3 }
```

Send quantity in the **JSON body**, not query parameters.

### Apply coupon

```http
POST /cart/apply-coupon
{ "code": "WELCOME10" }
```

### Cart line pricing (offers)

Each item in `GET /cart` includes offer-aware fields:

| Field | Description |
|-------|-------------|
| `billable_quantity` | Units charged (excludes free BOGO bonus units) |
| `bonus_quantity` | Extra free units from BOGO offers |
| `discounted_quantity` | Units receiving offer discount |
| `full_price_quantity` | Units at full price when `max_discounted_quantity` is exceeded |
| `max_discounted_quantity` | Offer cap per product, or `null` |
| `unit_price` | Effective average unit price after offers |
| `subtotal` | Line total |

Supports product offers (%, fixed, BOGO) and category-level BOGO offers.

### Cart index `meta`

Includes `subtotal`, `total_quantity`, `coupon`, `gift_offer`, `qualifies_for_free_delivery`, `order_discount`, etc.

---

## Checkout — Orders

```http
POST /orders
```

**Body:**

```json
{
  "address_id": 1,
  "payment_method": "cash_on_delivery",
  "coupon_code": "WELCOME10",
  "use_wallet": false,
  "notes": "Leave at door",
  "gift_offer_id": null,
  "gift_product_id": null,
  "item_notes": [
    { "product_id": 12, "variant_id": 5, "note": "Extra sauce" },
    { "product_id": 3, "variant_id": null, "note": "No ice" }
  ]
}
```

| Field | Description |
|-------|-------------|
| `address_id` | Required. Customer address. |
| `payment_method` | `cash_on_delivery` or `wallet` (optional at checkout; defaults apply) |
| `use_wallet` | Apply wallet balance (not with COD) |
| `gift_offer_id` / `gift_product_id` | Threshold gift selection |
| `item_notes` | Optional. Per-line notes matched by `product_id` + `variant_id` (max 500 chars each) |

Cart must not be empty. Creates order and **clears cart**.

```http
GET /orders
GET /orders?per_page=15&status=pending
GET /orders/{id}
POST /orders/{id}/cancel
POST /orders/{id}/pay
POST /orders/{id}/reorder
POST /orders/{id}/refund-request
POST /orders/{id}/rate
```

### Pay order

Pay a pending order with wallet (when payment method is wallet):

```http
POST /orders/{id}/pay
{ "payment_method": "wallet" }
```

---

## Wishlist

```http
GET /wishlist
POST /wishlist/{product_id}
```

Toggle add/remove.

---

## Wallet

```http
GET /wallet/history
```

---

## Product feedback

```http
POST /products/{id}/rate
POST /products/{id}/report
```

---

## Support tickets

```http
GET /tickets
POST /tickets
GET /tickets/{id}
PUT /tickets/{id}
POST /tickets/{id}/messages
```

### Create ticket (with attachments)

```http
POST /tickets
Content-Type: multipart/form-data
```

| Field | Type | Rules |
|-------|------|-------|
| `subject` | string | required, max 255 |
| `description` | string | required |
| `attachments[0]` | file | optional |
| `attachment[0]` | file | optional (alias) |

**Allowed types:** jpeg, png, jpg, gif, webp, pdf, doc, docx — max **5 MB** each.

Files are stored under `storage/app/public/tickets/`. Response includes full URLs:

```json
{
  "data": {
    "id": 1,
    "subject": "Order issue",
    "attachments": [
      "http://gahez.test/storage/tickets/abc123.jpg"
    ],
    "messages": [
      {
        "message": "Order issue description",
        "attachments": ["http://..."]
      }
    ]
  }
}
```

### Reply to ticket

```http
POST /tickets/{id}/messages
Content-Type: multipart/form-data
```

| Field | Type | Rules |
|-------|------|-------|
| `message` | string | required |
| `attachments[0]` | file | optional |
| `attachment[0]` | file | optional (alias) |

Admin panel (`/admin/tickets/{id}`) supports the same attachment fields on reply.

> Requires `php artisan storage:link` so `/storage/...` URLs are accessible.

---

## Refund requests

```http
GET /refund-requests
```

---

# Frontend integration guide

### 1. App launch

```
GET /store/config
```

Apply `theme` colors, `font_family`, and layout modes. Cache locally.

### 2. Register / login flow

```
POST /auth/register → verify → POST /auth/login → save token
```

### 3. Authenticated API client

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

### 4. Typical purchase flow

1. `GET /products`, `GET /categories/tree`
2. `POST /cart/{product_id}`
3. `GET /cart/checkout-preview`
4. `POST /addresses` (if none)
5. `POST /orders`
6. `GET /notifications` for status updates

### 5. Theme usage

| `category_layout` | UI suggestion |
|-------------------|---------------|
| `horizontal` | Horizontal scroll chips / carousel |
| `vertical` | Vertical list |

| `product_layout` | UI suggestion |
|------------------|---------------|
| `horizontal` | Row cards, image left |
| `vertical` | Grid or stacked cards |

### 6. Queue / email

Promotional and order notifications are stored in `notifications` table. Email is sent when the user has an email address. Run `php artisan queue:work` in production if notifications are queued.

---

# Postman / testing

**Demo customer** (after `php artisan db:seed`):

| Field | Value |
|-------|-------|
| Email | `customer1@gmail.com` |
| Password | `12345678` |

Import the Apidog/Postman collection from `docs/apidog/gahez-api.postman_collection.json`.

---

# Changelog (recent)

| Feature | Notes |
|---------|-------|
| Store theme (admin) | `GET /store/config` |
| Customer notifications | `GET /notifications` |
| Offer/coupon promos | Admin bell button → customer inbox |
| Birthdate on profile | `PATCH /profile` `birthdate` field |
| Offers in cart | BOGO, `max_discounted_quantity`, category BOGO line pricing |
| Cart item update | `PUT/PATCH /cart/items/{id}` with JSON body |
| Ticket attachments | Multipart `attachments[0]` or `attachment[0]` |
| Refund requests (admin) | Accept/reject on `/admin/order-refund-requests` |
| Admin live notifications | Polls `/admin/notifications/feed` |
| Payment methods | `cash_on_delivery` and `wallet` only |

---

# Admin panel (summary)

Session auth at `/admin`. Permission-gated routes in `routes/v1/admin.php`.

| Module | Permission | Highlights |
|--------|------------|------------|
| Orders | `manage orders` | Create, status, payment, invoice |
| Refund requests | `manage refunds` | Accept/reject on index |
| Tickets | `manage tickets` | Messages + attachments |
| Offers / coupons | respective | Notify-all-customers button |
| Notifications | all admins | Inbox + live feed toasts |

---

For issues or missing endpoints, check `routes/v1/api.php` and `routes/v1/admin.php` in the repository.
