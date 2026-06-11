# Project progress

Gahez is a Laravel 13 e-commerce platform with an **admin panel** and **customer API**.

**Stack:** PHP 8.3, Sanctum, Spatie Permission, Spatie Translatable, Maatwebsite Excel, Redis-ready queues.

**Last updated:** June 2026

---

## Architecture overview

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│  Customer app   │────▶│  /api/v1         │────▶│  Services       │
│  (mobile/web)   │     │  Sanctum auth    │     │  app/V1/        │
└─────────────────┘     └──────────────────┘     └─────────────────┘
┌─────────────────┐              │                        │
│  Driver app     │──────────────┘                        ▼
└─────────────────┘                              ┌─────────────────┐
┌─────────────────┐     ┌──────────────────┐     │  Models / DB    │
│  Admin panel    │────▶│  /admin          │────▶│  Migrations     │
│  Session auth   │     │  Spatie perms    │     └─────────────────┘
└─────────────────┘     └──────────────────┘
```

| Surface | URL prefix | Auth |
|---------|------------|------|
| Customer & driver API | `/api/v1` | Sanctum bearer token |
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
| Sliders | ✅ | ✅ public read |
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
| Checkout preview (totals, zone, gifts) | ✅ |
| Apply coupon to cart | ✅ |
| Wishlist toggle | ✅ |

Cart items expose `billable_quantity`, `bonus_quantity`, `discounted_quantity`, `full_price_quantity`, and `subtotal` with offers applied.

**Key files:** `CartItemController`, `CartItemService`, `OfferService`, `CouponService`, `WishlistController`

---

### 4. Orders & payments

| Feature | Admin | API |
|---------|-------|-----|
| Place order (clears cart) | Manual create | ✅ |
| Order list / detail / invoice | ✅ | ✅ |
| Status & payment status updates | ✅ | — |
| Cancel, pay, reorder | — | ✅ |
| Refund requests — **accept/reject on index** | ✅ | ✅ submit |
| Order & delivery ratings | — | ✅ |
| Wallet payment | — | ✅ |
| COD marked paid on delivered | ✅ auto | — |

**Key files:** `OrderController`, `OrderService`, `OrderRefundRequestController`, `DeliveryAssignmentService`

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

### 6. Delivery

| Feature | Admin | API |
|---------|-------|-----|
| Delivery zones (fees, geo) | ✅ | — |
| Drivers CRUD, wallet, shift assign | ✅ | — |
| Shifts CRUD | ✅ | ✅ driver subscribe |
| Auto / manual assignment | ✅ | — |
| Driver assignments (in-transit, delivered) | — | ✅ |
| Become-a-driver application — accept/reject | ✅ | ✅ |
| Delivery expected time slots | — | ✅ |
| Google Maps directions (optional) | ✅ | — |

**Key files:** `DeliveryAssignmentService`, `ZoneService`, `ShiftService`, `AssignmentController`

---

### 7. Notifications

| Feature | Admin | API |
|---------|-------|-----|
| In-app notification inbox | ✅ | ✅ |
| **Live admin feed** (500ms poll + toasts) | ✅ | — |
| Mark read / mark all read | ✅ | ✅ |
| Order lifecycle alerts (assigned, in-transit, delivered) | ✅ | ✅ |
| Offer/coupon promotions | Trigger | ✅ |
| Birthday rewards (scheduled) | Settings | ✅ |
| Email (when user has email) | — | ✅ |

**Key files:** `NotificationController`, `NotificationService`, `resources/js/admin/live-notifications.js`, `app/Notifications/*`

> Push notifications (FCM/APNs) are **not** implemented. Database + email only.

---

### 8. Settings & store theme

| Feature | Admin | API |
|---------|-------|-----|
| App name, currency, logo | ✅ | ✅ `GET /store/config` |
| Store theme (colors, layouts, font) | ✅ | ✅ |
| Security settings | ✅ | — |
| Help page content | ✅ | — |

**Key files:** `ThemeController`, `StoreConfigController`, `SettingService`

---

### 9. Plans & wallet

| Feature | Admin | API |
|---------|-------|-----|
| Subscription plans | ✅ | ✅ list |
| Plan subscribe | — | ✅ |
| Customer wallet history | — | ✅ |
| Driver wallet history | Admin | ✅ |

**Key files:** `PlanController`, `WalletTransactionController`, `PointService`

---

### 10. Support & quality

| Feature | Admin | API |
|---------|-------|-----|
| **Support chats** (real-time threads) | ✅ assign, status, reply | ✅ CRUD + messages |
| Support tickets + messages | ✅ | ✅ |
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
| Feature & unit tests (~28 test files) | ✅ |
| API documentation (`/docs`, `docs/API.md`) | ✅ |
| Apidog / OpenAPI collection | ✅ `docs/apidog/` |

**Demo accounts** (password `12345678`):

| Role | Email |
|------|-------|
| Super admin | `super-admin@gmail.com` |
| Admin | `admin@gmail.com` |
| Customer | `customer1@gmail.com` |
| Driver | `driver1@gmail.com` |

---

## Recent milestones (2026)

| Area | Delivered |
|------|-----------|
| **Offers in cart** | BOGO bonus qty, billable vs discounted units, category BOGO pricing, checkout gifts |
| **Cart API** | `PUT/PATCH /cart/items/{cartItem}`, JSON body for quantity updates |
| **Refund requests** | Admin accept/reject on index; wallet refund on approve |
| **Ticket attachments** | Multipart upload API + admin; `UploadStorage` for Windows/Laragon |
| **Admin live notifications** | Feed polling + toast popups (replaces SSE) |
| **Delivery** | COD paid on delivered, zone shipping fee fallback, shift subscribe API |
| **Catalog ordering** | `sort_order` on products & categories (blank auto-appends) |
| **Delivery module** | Zones, shifts, assignments, driver API, ratings |
| **Notifications** | Customer inbox API, offer/coupon broadcasts |
| **Store theme** | Admin-only theme → `GET /store/config` |
| **Birthday rewards** | Birthdate on profile, scheduled coupons/gifts |
| **LaRecipe docs** | Browsable `/docs` API reference |
| **Import/export** | Queued Excel transfer for catalog entities |
| **Support chats** | Customer API + admin inbox; multipart attachments |
| **Admin UX** | Theme/locale pill toggles; warm `gahez-50` light surfaces; dark orange/brown palette |
| **Arabic numerals** | `format_local_number`, `@num`, Arabic-Indic digits in admin + `trans()` |
| **Product list sort** | Name A–Z / Z–A filter in admin |

---

## Planned / optional next steps

- Push notifications (FCM/APNs) — not implemented
- Auto-generated OpenAPI from code (Scribe) — manual collection in `docs/apidog/`
- Google Maps API key in `config/services.php` — optional; haversine fallback when unset

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
