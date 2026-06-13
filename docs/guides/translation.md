# Translation guide

Gahez Akid supports **English (`en`)** and **Arabic (`ar`)** in three layers:

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

**Admin / landing language switch:**

Pill toggle in the header (or landing nav): **EN | AR** with UK / Egypt flags.  
Equivalent route:

```
GET /locale/ar
GET /locale/en
```

Sets session + cookie, then redirects back.

**Admin / landing theme:**

Sun/moon pill toggle stores preference in `localStorage.theme` and sets `data-bs-theme` on `<html>` (`light` or `dark`).

---

## Arabic-Indic digits (admin UI)

When locale is Arabic (`ar`), numeric displays in the admin panel use **Arabic-Indic digits** (٠–٩) for prices, counts, and formatted numbers.

| Helper / Blade | Usage |
|----------------|-------|
| `format_local_number($n)` | Format number with locale separators + digits |
| `localize_digits($string)` | Convert Western digits in a string |
| `@num($value)` | Blade directive for formatted numbers |
| `@digits($value)` | Blade directive for digit conversion only |

API `trans()` / `__()` messages also localize digits via `LocalizingTranslator`.  
API JSON numeric fields remain Western digits for client parsing unless you format on the client with `Accept-Language: ar`.

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

Arabic sets `dir="rtl"` on `<html>`. Admin panel and landing page use logical CSS for toggles and navigation. Light theme surfaces use warm **`gahez-50`** cream backgrounds (no pure white). For **LaRecipe docs RTL**, optional package: `binarytorch/larecipe-rtl`.

---

## Checklist for new features

- [ ] Add `messages.php` keys in **both** `en` and `ar`
- [ ] If new translatable model fields, add to model `$translatable` array
- [ ] API resource uses `LocalizesTranslatableAttributes` if applicable
- [ ] Test with `Accept-Language: ar` on API endpoints
