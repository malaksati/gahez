# Gahez Akid E-Commerce API

REST API for the customer mobile/web app.

---

- [Base URL](#base-url)
- [Quick start](#quick-start)

<a name="base-url"></a>
## Base URL

```
https://your-domain.com/api/v1
```

Local example (Laragon):

```
http://gahez.test/api/v1
```

<a name="quick-start"></a>
## Quick start

1. Load store config: `GET /store/config` (app name, currency, logo)
2. Register or login: `POST /auth/register` → verify → `POST /auth/login`
3. Send `Authorization: Bearer {token}` on protected routes
4. Optional: `Accept-Language: en` or `ar` for translated messages

**Server setup:** run `php artisan storage:link` so ticket attachments and product images return working URLs.

See **Authentication** for login details and **Frontend integration** for a JavaScript client example.

**Module overview:** [Project progress](/docs/1.0/project-progress)
