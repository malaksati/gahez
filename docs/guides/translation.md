# Translation guide

Gahez supports **English (`en`)** and **Arabic (`ar`)** in three layers:

1. **UI / API messages** — Laravel `lang/` files
2. **Database content** — Spatie Laravel Translatable on models
3. **Locale resolution** — `Accept-Language`, session, cookie, query

---

## Supported locales

`config/app.php`:

```php
'supported_locales' => ['en', 'ar'],
'fallback_locale' => 'en',
```

---

## How locale is chosen

Middleware: `app/Http/Middleware/SetLocaleFromRequest.php` (applied to **web** and **api**).

| Context | Priority |
|---------|----------|
| **API** (`/api/v1/*`) | Session → query `locale`/`lang` → `Accept-Language` → cookie → default |
| **Admin / web** | Session → cookie → query/`Accept-Language` → default |

**API example:**

```http
Accept-Language: ar
```

**Admin language switch:**

```
GET /locale/ar
```

Sets session + cookie, then redirects back.

---

## UI strings (admin panel & API messages)

### `lang/{locale}/messages.php`

Primary keyed translations (~1200+ keys):

```php
// lang/en/messages.php
'Save changes' => 'Save changes',

// lang/ar/messages.php
'Save changes' => 'حفظ التغييرات',
```

**Usage in Blade / controllers:**

```php
__('messages.Save changes')
```

### `lang/{locale}.json`

For bare-string `__()` calls (public pages, some middleware):

```json
// lang/en.json
"Home": "Home"

// lang/ar.json
"Home": "الرئيسية"
```

**Usage:**

```php
__('Home')
```

### Standard Laravel files

`lang/en/auth.php`, `validation.php`, `passwords.php`, `pagination.php` — English only; validation messages fall back to English for `ar` unless you add `lang/ar/validation.php`.

---

## Adding a new translation key

1. Add English key in `lang/en/messages.php`
2. Add Arabic value in `lang/ar/messages.php` (same key)
3. Use `__('messages.Your key')` in Blade or controllers
4. If using bare `__('Plain text')`, also add to `lang/en.json` and `lang/ar.json`

---

## Database content (Spatie Translatable)

Models store JSON per field, e.g. `{"en":"Apple","ar":"تفاح"}`.

**Translatable models** (`app/Models/`):

- `Product` — `name`, `description`
- `Category`, `Brand`, `Branch`
- `Variant`, `VariantOption`, `ProductVariant`, `ProductVariantValue`
- `Offer`, `Plan`

**Admin forms** — validate with `TranslatableRules` (`app/V1/Http/Requests/Rules/TranslatableRules.php`):

```
name.en — required
name.ar — required
```

**API responses** — `LocalizesTranslatableAttributes` trait on API resources returns the string for the **current locale** only.

---

## Admin vs API

| Area | Mechanism |
|------|-----------|
| Admin Blade sidebar, buttons, labels | `__('messages.*')` |
| API success/error messages | `__('messages.*')` with locale from middleware |
| API product/category names | Spatie translatable + resource localization |
| Store theme font | `Cairo`, `Tajawal` for Arabic-friendly UI |

---

## RTL (right-to-left)

Admin panel uses Bootstrap; Arabic layout depends on your theme/CSS. For **LaRecipe docs RTL**, optional package: `binarytorch/larecipe-rtl`.

---

## Checklist for new features

- [ ] Add `messages.php` keys in **both** `en` and `ar`
- [ ] If new translatable model fields, add to model `$translatable` array
- [ ] API resource uses `LocalizesTranslatableAttributes` if applicable
- [ ] Test with `Accept-Language: ar` on API endpoints
