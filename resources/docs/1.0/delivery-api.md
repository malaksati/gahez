# Delivery driver API

Requires `delivery.user` middleware (user with an approved delivery profile).

Prefix: `/delivery`

---

- [Assignments](#assignments)
- [Shifts](#shifts)
- [Wallet](#wallet)

<a name="assignments"></a>
## Assignments

```http
GET /delivery/assignments
GET /delivery/assignments/{id}
POST /delivery/assignments/{id}/in-transit
POST /delivery/assignments/{id}/delivered
```

### Flow

1. Admin assigns order to driver (auto or manual)
2. Driver receives notification
3. `POST .../in-transit` when leaving for delivery
4. `POST .../delivered` when completed

Marking **delivered** on COD orders updates payment status to **paid** automatically.

Assignment includes order details, customer address, and zone shipping fee.

<a name="shifts"></a>
## Shifts

```http
GET /delivery/shifts
POST /delivery/shifts/{id}/subscribe
```

- `GET /delivery/shifts` — upcoming shifts the driver can subscribe to
- `POST /delivery/shifts/{id}/subscribe` — join a shift (capacity rules apply)

Customers select delivery slots at checkout via `GET /delivery-expected-time` and pass `shift_id` on `POST /orders`.

<a name="wallet"></a>
## Wallet

```http
GET /delivery/wallet/history
```

Driver earnings and wallet transactions. Admin can also view/adjust driver wallet from `/admin/deliveries`.
