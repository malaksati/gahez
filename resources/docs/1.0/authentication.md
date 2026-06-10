# Authentication

Protected routes use **Laravel Sanctum** bearer tokens.

---

- [Login](#login)
- [Authenticated requests](#authenticated-requests)
- [Logout](#logout)
- [Headers](#headers)
- [Response conventions](#response-conventions)
- [Rate limits](#rate-limits)

<a name="login"></a>
## Login

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

<a name="authenticated-requests"></a>
## Authenticated requests

```http
Authorization: Bearer {token}
Accept: application/json
Accept-Language: en
```

<a name="logout"></a>
## Logout

```http
POST /auth/logout
Authorization: Bearer {token}
```

<a name="headers"></a>
## Headers

| Header | Required | Description |
|--------|----------|-------------|
| `Accept` | Recommended | `application/json` |
| `Content-Type` | For POST/PUT/PATCH | `application/json` |
| `Authorization` | Protected routes | `Bearer {token}` |
| `Accept-Language` | Optional | `en` or `ar` (translations) |

<a name="response-conventions"></a>
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

<a name="rate-limits"></a>
## Rate limits

| Group | Limit |
|-------|-------|
| Most authenticated routes | 60 requests / minute |
| Public catalog routes | 30 requests / minute |
| Login / register / password reset | Stricter per-route limits |
