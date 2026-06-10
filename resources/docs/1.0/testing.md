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

**Delivery driver**

| Field | Value |
|-------|-------|
| Email | `driver1@gmail.com` |
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
| Store theme (admin) | `GET /store/config` |
| Customer notifications | `GET /notifications` |
| Offer/coupon promos | Admin bell → customer inbox |
| Birthdate on profile | `PATCH /profile` `birthdate` |
| Offers in cart | BOGO, `max_discounted_quantity`, category BOGO |
| Cart item update | `PUT/PATCH /cart/items/{id}` |
| Ticket attachments | Multipart `attachments[0]` or `attachment[0]` |
| Refund requests (admin) | Accept/reject on index |
| Delivery shifts API | `GET /delivery/shifts`, subscribe |
| Admin live notifications | Feed polling + toasts |
| Product/category sort order | Admin `sort_order` (blank auto-appends) |

---

For issues or missing endpoints, check `routes/v1/api.php` and `routes/v1/admin.php` in the repository.
