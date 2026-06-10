# Public endpoints

No authentication required.

---

- [Store config & theme](#store-config-theme)
- [Register](#register)
- [Verify account](#verify-account)
- [Password reset](#password-reset)
- [Catalog](#catalog)
- [Plans](#plans)

<a name="store-config-theme"></a>
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

<a name="register"></a>
## Register

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

<a name="verify-account"></a>
## Verify account

```http
POST /auth/verify-email
{ "email": "...", "code": "123456" }

POST /auth/verify-phone
{ "phone": "...", "code": "123456" }

POST /auth/resend-verification-code
{ "email": "..." }  // or phone
```

<a name="password-reset"></a>
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

<a name="catalog"></a>
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

<a name="plans"></a>
## Plans

```http
GET /plans
POST /plans/{plan}/subscribe
```
