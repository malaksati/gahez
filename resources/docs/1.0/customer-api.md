# Customer API

Authenticated customer endpoints. Requires `Authorization: Bearer {token}`.

---

- [Profile](#profile)
- [Notifications](#notifications)
- [Addresses](#addresses)
- [Cart](#cart)
- [Orders & checkout](#orders-checkout)
- [Wishlist](#wishlist)
- [Wallet](#wallet)
- [Delivery time slots](#delivery-time-slots)
- [Product feedback](#product-feedback)
- [Support tickets](#support-tickets)
- [Support chats (real-time)](#support-chats)
- [Goals](#goals)
- [Become a delivery driver](#become-delivery-driver)
- [Refund requests](#refund-requests)

<a name="profile"></a>
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

<a name="notifications"></a>
## Notifications

In-app inbox (offers, coupons, orders, birthday rewards, etc.).

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

<a name="addresses"></a>
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

Address must fall inside an active **delivery zone** at checkout.

<a name="cart"></a>
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

By product:

```http
PUT /cart/12
{ "quantity": 3, "variant_id": 5 }
```

By cart item ID (preferred):

```http
PUT /cart/items/42
{ "quantity": 3 }
```

Send quantity in the **JSON body**, not query parameters.

### Apply coupon

```http
POST /cart/apply-coupon
{ "code": "WELCOME10" }
```

### Cart line pricing (offers)

Each cart item includes:

| Field | Description |
|-------|-------------|
| `billable_quantity` | Units charged |
| `bonus_quantity` | Free BOGO units |
| `discounted_quantity` | Units with offer discount |
| `full_price_quantity` | Units at full price past `max_discounted_quantity` |
| `unit_price` | Effective unit price |
| `subtotal` | Line total |

### Cart index `meta`

Includes `subtotal`, `total_quantity`, `coupon`, `gift_offer`, `qualifies_for_free_delivery`, `order_discount`, etc.

<a name="orders-checkout"></a>
## Orders & checkout

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
  "shift_id": 3,
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
| `address_id` | Required. Customer address inside a zone. |
| `payment_method` | e.g. `cash_on_delivery`, `wallet`, card methods |
| `use_wallet` | Apply wallet balance (not with COD) |
| `shift_id` | Optional delivery slot |
| `gift_offer_id` / `gift_product_id` | Threshold gift selection |
| `item_notes` | Optional. Per-line notes by `product_id` + `variant_id` (nullable, max 500 chars) |

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
POST /orders/{id}/rate-delivery
```

### Pay order

```http
POST /orders/{id}/pay
{ "payment_method": "wallet" }
```

<a name="wishlist"></a>
## Wishlist

```http
GET /wishlist
POST /wishlist/{product_id}
```

Toggle add/remove.

<a name="wallet"></a>
## Wallet

```http
GET /wallet/history
```

<a name="delivery-time-slots"></a>
## Delivery time slots

```http
GET /delivery-expected-time
```

Available shifts for checkout.

<a name="product-feedback"></a>
## Product feedback

```http
POST /products/{id}/rate
POST /products/{id}/report
```

<a name="support-tickets"></a>
## Support tickets

```http
GET /tickets
POST /tickets
GET /tickets/{id}
PUT /tickets/{id}
POST /tickets/{id}/messages
```

### Create ticket (multipart)

```http
POST /tickets
Content-Type: multipart/form-data
```

| Field | Type | Rules |
|-------|------|-------|
| `subject` | string | required |
| `description` | string | required |
| `attachments[0]` | file | optional |
| `attachment[0]` | file | optional alias |

Allowed: jpeg, png, jpg, gif, webp, pdf, doc, docx — max 5 MB each.

### Reply (multipart)

```http
POST /tickets/{id}/messages
Content-Type: multipart/form-data
```

| Field | Type | Rules |
|-------|------|-------|
| `message` | string | required |
| `attachments[0]` | file | optional |
| `attachment[0]` | file | optional alias |

Response includes `attachments` as full URLs on both ticket and message objects.

<a name="support-chats"></a>
## Support chats (real-time)

Lightweight chat threads for quick customer ↔ store messaging (separate from formal tickets).

```http
GET /support-chats?per_page=15
POST /support-chats
GET /support-chats/{id}
GET /support-chats/{id}/messages?per_page=30
POST /support-chats/{id}/messages
```

### Create chat

```http
POST /support-chats
Content-Type: application/json
```

```json
{
  "subject": "Order issue",
  "message": "My order is late"
}
```

Or **multipart/form-data** with optional `attachments[0]` / `attachment[0]` (same rules as tickets).

At least one of `message` or attachments is expected when opening a chat. `subject` is optional (max 255).

### Send message

```http
POST /support-chats/{id}/messages
```

| Field | Type | Rules |
|-------|------|-------|
| `message` | string | optional if attachments present |
| `attachments[0]` | file | optional |

Cannot message a **closed** chat (`403`). Admin replies from **Messages → Support chats** in the panel.

**Chat object:** `id`, `status`, `subject`, `last_message_at`, `latest_message`, `unread_messages_count`, `created_at`.

<a name="goals"></a>
## Goals

Gamification targets for the logged-in customer (progress toward rewards).

```http
GET /goals
```

Returns active goals with current progress for the user.

<a name="become-delivery-driver"></a>
## Become a delivery driver

```http
GET /become-delivery/status
POST /become-delivery
```

<a name="refund-requests"></a>
## Refund requests

```http
GET /refund-requests
```
