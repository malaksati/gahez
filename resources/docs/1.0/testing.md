# API testing & changelog

> PHPUnit guide: see [Running tests](/docs/1.0/running-tests). Apidog import: `docs/apidog/` in the repository.

---

- [Demo accounts](#demo-accounts)
- [Changelog](#changelog)

<a name="demo-accounts"></a>
## Demo accounts

After `php artisan db:seed`:

**Customer**

| Field | Value |
|-------|-------|
| Email | `customer1@gmail.com` |
| Password | `12345678` |

**Super admin** (for manual admin testing)

| Field | Value |
|-------|-------|
| Email | `super-admin@gmail.com` |
| Password | `12345678` |

<a name="changelog"></a>
## Changelog

| Feature | Notes |
|---------|-------|
| App branding | Default name **Gahez Akid** |
| Weekday shipping | `shipping_day`, `is_fast_shipping`, checkout preview |
| Free delivery | Active `free_delivery` offers only |
| Ticket types | `complaint`, `recommendation` |
| Admin notifications | Mark read on click; mark all; report/refund alerts |
| Orders admin | Edit pending/processing only; invoice shipping day |
| Store config | `GET /store/config` — app name, currency, logo |
| Customer notifications | `GET /notifications` |
| Offer/coupon promos | Admin bell → customer inbox |
| Birthdate on profile | `PATCH /profile` `birthdate` |
| Offers in cart | BOGO, `max_discounted_quantity`, category BOGO |
| Cart item update | `PUT/PATCH /cart/items/{id}` |
| Ticket attachments | Multipart `attachments[0]` or `attachment[0]` |
| Refund requests (admin) | Accept/reject on index |
| Admin live notifications | Feed polling + toasts |
| Product/category sort order | Admin `sort_order` (blank auto-appends) |
| Product name sort filter | Admin list `name_asc` / `name_desc` |
| Support chats API | `GET/POST /support-chats`, messages, attachments |
| Admin support chats UI | Assign, status, real-time thread (`manage support-chats`) |
| Goals API | `GET /goals` — customer progress |
| Theme & locale toggles | Pill switches in admin header + landing (`localStorage` theme) |
| Arabic-Indic digits | Admin UI + `trans()` when locale is `ar` |
| Warm light theme | `gahez-50` surfaces across admin, auth, landing |

---

For issues or missing endpoints, check `routes/v1/api.php` and `routes/v1/admin.php` in the repository.
