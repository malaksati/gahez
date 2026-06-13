# Project progress

Gahez Akid is a Laravel 13 e-commerce platform with an **admin panel** and **customer API**.

**Stack:** PHP 8.3, Sanctum, Spatie Permission, Spatie Translatable, Maatwebsite Excel, Redis-ready queues.

**Last updated:** June 2026

---

## Architecture overview

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│  Customer app   │────▶│  /api/v1         │────▶│  Services       │
│  (mobile/web)   │     │  Sanctum auth    │     │  app/V1/        │
└─────────────────┘     └──────────────────┘     └─────────────────┘
                                 │                        │
┌─────────────────┐              │                        ▼
│  Admin panel    │────▶│  /admin          │────▶│  Models / DB    │
│  Session auth   │     │  Spatie perms    │     │  Migrations     │
└─────────────────┘     └──────────────────┘     └─────────────────┘
```

| Surface | URL prefix | Auth |
|---------|------------|------|
| Customer API | `/api/v1` | Sanctum bearer token |
| Admin panel | `/admin` | Session + `admin\|super-admin` role |
| API docs (LaRecipe) | `/docs` | Public |
| Public site | `/` | Guest |

---

## Modules & features

### 1. Authentication & users

| Feature | Admin | API |
|---------|-------|-----|
| Register / login (email or phone) | — | ✅ |
| Email & phone verification | — | ✅ |
| Password reset (code flow) | — | ✅ |
| Profile (name, email, phone, image, birthdate) | Admin profile | ✅ `PATCH /profile` |
| Admin users CRUD | ✅ | — |
| Customers CRUD | ✅ | — |
| Role-based access (super-admin, admin) | ✅ | — |

**Key files:** `AuthController`, `ProfileController`, `AdminUserController`, `CustomerController`

---

### 2. Catalog

| Feature | Admin | API |
|---------|-------|-----|
| Categories (tree, CRUD, `sort_order`) | ✅ | ✅ public read |
| Products (simple & variable, SKU, images, `sort_order`) | ✅ | ✅ public read |
| Brands, branches | ✅ | ✅ public read |
| Variants & variant options | ✅ | — |
| Sliders (optional `type` filter) | ✅ | ✅ public read |
| Product import/export (Excel) | ✅ | — |
| Quick variant/option creation | ✅ | — |
| Product approve / featured / active toggles | ✅ | — |

**Key files:** `ProductController`, `CategoryController`, `DataTransferService`

---

### 3. Cart & checkout

| Feature | API |
|---------|-----|
| Add/update/remove cart items | ✅ |
| Update by cart item ID (`PUT/PATCH /cart/items/{id}`) | ✅ |
| Variable products require `variant_id` | ✅ |
| Offer-aware line pricing (%, fixed, BOGO, category BOGO) | ✅ |
| `max_discounted_quantity` — extra units at full price | ✅ |
| Checkout preview (`GET /cart/checkout-preview` + cart `meta`) | ✅ |
| Weekday shipping + fast shipping option | ✅ |
| Cart minimums (line count, subtotal) | ✅ |
| Free delivery from active `free_delivery` offers only | ✅ |
| Apply coupon to cart | ✅ |
| Wishlist toggle | ✅ |

Cart items expose `billable_quantity`, `bonus_quantity`, `discounted_quantity`, `full_price_quantity`, and `subtotal` with offers applied.

**Shipping rules:** Standard shipping excludes today; fast shipping is today only and adds the configured extra fee. `POST /orders` requires `shipping_day` and optional `is_fast_shipping`.

**Key files:** `CartItemController`, `CartItemService`, `CheckoutSettingsService`, `OfferService`, `CouponService`, `WishlistController`

---

### 4. Orders & payments

| Feature | Admin | API |
|---------|-------|-----|
| Place order (clears cart) | Manual create | ✅ |
| Order list / detail / invoice | ✅ | ✅ |
| Edit order (pending / processing only) | ✅ | — |
| Status & payment status updates (show page) | ✅ | — |
| Invoice shows shipping day + fast shipping | ✅ | — |
| Cancel, pay, reorder | — | ✅ |
| Refund requests — **accept/reject on index** | ✅ | ✅ submit |
| Order ratings | — | ✅ |
| Wallet payment | — | ✅ |
| COD marked paid on delivered | ✅ auto | — |

**Key files:** `OrderController`, `OrderService`, `OrderRefundRequestController`

---

### 5. Coupons & offers

| Feature | Admin | API |
|---------|-------|-----|
| Coupons CRUD | ✅ | — |
| Offers CRUD (%, fixed, BOGO, threshold gift, free delivery) | ✅ | ✅ public list |
| Notify all customers (running promo) | ✅ bell button | — |
| Birthday reward coupons | Settings | — |

**Key files:** `CouponController`, `OfferController`, `CustomerBroadcastService`, `BirthdayRewardService`, `OfferService`

---

### 6. Checkout & shipping settings

| Feature | Admin | API |
|---------|-------|-----|
| Standard shipping fee | ✅ Settings | ✅ preview |
| Fast shipping extra fee | ✅ Settings | ✅ preview |
| Cart minimum line count / subtotal | ✅ Settings | ✅ preview |
| Weekday selection at checkout | — | ✅ `shipping_day` |
| Free delivery threshold from offers | — | ✅ |

Configured in **Settings → Checkout and shipping**. No separate driver mobile API is registered in `routes/v1/api.php`.

**Key files:** `CheckoutSettingsService`, `SettingsController`, `SettingService`

---

### 7. Notifications

| Feature | Admin | API |
|---------|-------|-----|
| In-app notification inbox | ✅ | ✅ |
| **Live admin feed** (poll + toasts) | ✅ | — |
| Toast / dropdown click marks read + navigates | ✅ | — |
| Mark all as read (index page) | ✅ | ✅ |
| New order alert → order **show** page | ✅ | — |
| Product report submitted alert | ✅ (`manage product-reports`) | — |
| Refund request submitted alert | ✅ (`manage refunds`) | — |
| Offer/coupon promotions | Trigger | ✅ |
| Birthday rewards (scheduled) | Settings | ✅ |
| Email (when user has email) | — | ✅ |

**Key files:** `NotificationController`, `NotificationService`, `resources/js/admin/live-notifications.js`, `app/Notifications/*`

> Push notifications (FCM/APNs) are **not** implemented. Database + email only.

---

### 8. Settings & branding

| Feature | Admin | API |
|---------|-------|-----|
| App name, currency, logo | ✅ | ✅ `GET /store/config` |
| Checkout & shipping fees / cart limits | ✅ | ✅ (via cart preview) |
| Security settings | ✅ | — |
| Help page content | ✅ | — |

**Key files:** `StoreConfigController`, `SettingService`, `SettingsController`

---

### 9. Wallet & goals

| Feature | Admin | API |
|---------|-------|-----|
| Customer wallet history | — | ✅ |
| Customer goals (gamification) | ✅ | ✅ `GET /goals` |

**Key files:** `GoalController`, `WalletTransactionController`, `PointService`

---

### 10. Support & quality

| Feature | Admin | API |
|---------|-------|-----|
| **Support chats** (real-time threads) | ✅ assign, status, reply | ✅ CRUD + messages |
| Support tickets (`complaint` / `recommendation`) | ✅ filter + edit type | ✅ required `type` on create |
| **Ticket / chat file attachments** (multipart) | ✅ reply | ✅ create + reply |
| Product ratings | ✅ visibility | ✅ submit |
| Product reports | ✅ workflow | ✅ submit |
| Customer goals (gamification) | ✅ | ✅ `GET /goals` |

Attachments: jpeg, png, jpg, gif, webp, pdf, doc, docx (max 5 MB). Tickets → `tickets/`; support chats → `support/` paths via `UploadStorage`.

API accepts `attachments[0]` or `attachment[0]` field names.

**Key files:** `SupportChatController`, `SupportChatService`, `TicketController`, `TicketService`, `GoalController`

---

### 11. Reports & analytics

| Feature | Admin |
|---------|-------|
| Dashboard summary | ✅ |
| Earnings, product performance | ✅ |
| 9 report types (sales, stock, zones, customers…) | ✅ |
| Excel & PDF export | ✅ |

**Permission:** `view reports`  
**Key files:** `ReportController`, `AnalyticsReportService`

---

### 12. Data & demo

| Feature | Status |
|---------|--------|
| Full database seeders | ✅ |
| Feature & unit tests (~49 test files) | ✅ |
| API documentation (`/docs`, `docs/API.md`) | ✅ |
| Apidog / OpenAPI collection | ✅ `docs/apidog/` |

**Demo accounts** (password `12345678`):

| Role | Email |
|------|-------|
| Super admin | `super-admin@gmail.com` |
| Admin | `admin@gmail.com` |
| Customer | `customer1@gmail.com` |

---

## Recent milestones (2026)

| Area | Delivered |
|------|-----------|
| **App branding** | Default name **Gahez Akid** (`config`, settings, seeders) |
| **Weekday shipping** | `shipping_day`, `is_fast_shipping`, checkout preview shipping options |
| **Free delivery** | Only from active `free_delivery` offers (no hardcoded threshold) |
| **Cart limits** | Min line count / subtotal in settings + preview `meta` |
| **Tickets** | Types: `complaint`, `recommendation` (API + admin) |
| **Admin notifications** | Mark read on toast/dropdown click; mark all on index |
| **Admin alerts** | New product reports, new refund requests (permission-gated) |
| **Orders admin** | Edit only `pending` / `processing`; invoice shipping day + fast badge |
| **Offers in cart** | BOGO bonus qty, billable vs discounted units, category BOGO pricing |
| **Cart API** | `PUT/PATCH /cart/items/{cartItem}`, JSON body for quantity updates |
| **Refund requests** | Admin accept/reject on index; wallet refund on approve |
| **Ticket attachments** | Multipart upload API + admin; `UploadStorage` for Windows/Laragon |
| **Admin live notifications** | Feed polling + toast popups |
| **Support chats** | Customer API + admin inbox; multipart attachments |
| **Admin UX** | Theme/locale pill toggles; warm `gahez-50` light surfaces; dark orange/brown palette |
| **Arabic numerals** | `format_local_number`, `@@num`, Arabic-Indic digits in admin + `trans()` |
| **Product list sort** | Name A–Z / Z–A filter in admin |

---

## Planned / optional next steps

- Push notifications (FCM/APNs) — not implemented
- Auto-generated OpenAPI from code (Scribe) — manual collection in `docs/apidog/`
- Driver mobile API — not registered in current `routes/v1/api.php`

---

## Key directories

| Path | Contents |
|------|----------|
| `app/V1/Http/Controllers/` | Admin + API controllers |
| `app/V1/Services/` | Business logic (~40 services) |
| `routes/v1/api.php` | API route map |
| `routes/v1/admin.php` | Admin route map |
| `database/seeders/` | Demo data |
| `tests/` | PHPUnit Feature + Unit |
| `docs/` | API.md, guides, Apidog collection |
| `resources/docs/1.0/` | LaRecipe source (mirror of `docs/guides/` where applicable) |
