# Shipping & checkout

Weekday-based shipping for customer checkout. Fees and cart limits are configured in the admin panel (**Settings → Checkout and shipping**).

---

- [Checkout preview](#checkout-preview)
- [Place order](#place-order)
- [Rules](#rules)

<a name="checkout-preview"></a>
## Checkout preview

```http
GET /cart/checkout-preview
```

Also included in `GET /cart` response `meta` (same fields).

**`shipping` object:**

| Field | Description |
|-------|-------------|
| `base_fee` | Configured standard shipping fee |
| `fast_shipping_extra_fee` | Extra fee for same-day fast shipping |
| `free_delivery_applied` | `true` when cart qualifies for free delivery |
| `weekdays` | Weekdays allowed for **standard** shipping (excludes today) |
| `options` | `standard` and `fast` options with `weekdays` and `total_fee` |

**`cart_limits` object:** `min_line_count`, `min_subtotal`, `can_checkout`, `meets_line_minimum`, `meets_subtotal_minimum`.

**Gift / free delivery:** `gift_offer`, `free_delivery_threshold`, `qualifies_for_free_delivery`.

<a name="place-order"></a>
## Place order

```http
POST /orders
```

```json
{
  "address_id": 1,
  "shipping_day": "thursday",
  "is_fast_shipping": false,
  "payment_method": "cash_on_delivery"
}
```

| Field | Rules |
|-------|-------|
| `shipping_day` | Required — `monday` … `sunday` |
| `is_fast_shipping` | Optional boolean — default `false` |

Order responses include `shipping_day`, `is_fast_shipping`, `total_shipping`, and `fast_shipping_fee`.

<a name="rules"></a>
## Rules

| Mode | `shipping_day` | Fee |
|------|----------------|-----|
| Standard (`is_fast_shipping: false`) | Any weekday **except today** | `base_fee` |
| Fast (`is_fast_shipping: true`) | **Today only** | `base_fee` + `fast_shipping_extra_fee` |

When the cart qualifies for free delivery (active `free_delivery` offer threshold met, or coupon grants free delivery), shipping fees are waived.

> There is no `/delivery/*` driver API in `routes/v1/api.php`. Delivery operations are handled through the admin panel order workflow.
