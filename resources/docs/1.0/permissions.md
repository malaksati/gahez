# Permissions guide

Admin panel access uses **Spatie Laravel Permission** (`spatie/laravel-permission`). The API uses **Sanctum** + role markers (`user`, `delivery`), not fine-grained Spatie permissions.

---

## Roles

| Role | Guard | Purpose |
|------|-------|---------|
| `super-admin` | `web` | Full access; bypasses all permission checks |
| `admin` | `web` | Admin panel; permissions assigned per user |
| `user` | `web` | Customer (API identity) |
| `delivery` | `web` | Delivery driver (API identity) |

Defined in `database/seeders/RoleSeeder.php`.

---

## Permissions (admin panel)

| Permission | Module |
|------------|--------|
| `view dashboard` | Dashboard |
| `manage categories` | Categories + import/export |
| `manage products` | Products + import/export |
| `manage brands` | Brands |
| `manage branches` | Branches |
| `manage variants` | Variants + options + import/export |
| `manage coupons` | Coupons |
| `manage offers` | Offers |
| `manage sliders` | Sliders |
| `manage orders` | Orders |
| `manage refunds` | Refund requests (index accept/reject + edit) |
| `view reports` | Analytics & reports |
| `manage ratings` | Product ratings |
| `manage product-reports` | Product reports |
| `manage tickets` | Support tickets |
| `manage support-chats` | Real-time support chat inbox |
| `manage settings` | Settings, theme, security |
| `manage admins` | Admin users |
| `manage customers` | Customers |
| `manage delivery` | Drivers, zones, shifts, assignments |

---

## Super-admin bypass

`app/Providers/AuthServiceProvider.php`:

```php
Gate::before(function ($user, $ability) {
    return $user->hasRole('super-admin') ? true : null;
});
```

Super-admin always passes `@@can`, `permission:` middleware, and policies.

---

## Route protection

**Admin entry** (`routes/v1/web.php`):

```
middleware: auth, role:admin|super-admin
prefix: /admin
```

**Per-module** (`routes/v1/admin.php`):

```php
Route::middleware('permission:manage products')->group(function () {
    // product routes
});
```

**Sidebar** (`resources/views/layouts/partials/sidebar.blade.php`) uses `@@can` / `@@canany` to hide unauthorized links.

**Notifications** (`/admin/notifications`, feed polling) are available to **all authenticated admins** without a separate permission.

---

## Assigning permissions

### Demo seed (`database/seeders/UserSeeder.php`)

| Account | Role | Permissions |
|---------|------|-------------|
| `super-admin@gmail.com` | super-admin | All |
| `admin@gmail.com` | admin | All (synced individually) |
| `customer1@gmail.com` | user | None |
| `driver1@gmail.com` | delivery | None |

Password: `12345678`

### New admin user

`AdminUserService` assigns `admin` role + `syncPermissions([...])` from the create/edit form.

Super-admin accounts **cannot** be deleted or have permissions stripped via admin UI.

### New customer / driver

- Customer registration → `assignRole('user')`
- Delivery profile creation → `assignRole('delivery')`

---

## API authorization

`routes/v1/api.php` — **no** Spatie `permission` middleware.

| Access | Mechanism |
|--------|-----------|
| Public catalog | No auth |
| Customer routes | `auth:sanctum` |
| Delivery routes | `auth:sanctum` + `delivery.user` middleware |

`EnsureDeliveryUser` checks the user has an active `Delivery` profile record.

---

## Middleware aliases

Registered in `bootstrap/app.php`:

| Alias | Class |
|-------|-------|
| `role` | `RoleMiddleware` |
| `permission` | `PermissionMiddleware` |
| `role_or_permission` | `RoleOrPermissionMiddleware` |
| `delivery.user` | `EnsureDeliveryUser` |

---

## Login redirect

After login, users with `super-admin` or `admin` role go to `/admin/dashboard`; others to home.

---

## Adding a new admin module

1. Add permission name in `RoleSeeder.php`
2. Run `php artisan db:seed --class=RoleSeeder` (or fresh migrate+seed)
3. Wrap routes in `permission:your permission` middleware
4. Add `@@can('your permission')` in sidebar
5. Grant permission to admin users via Admin Users UI

---

## Cache

Spatie caches permissions. After seeding or bulk changes:

```bash
php artisan permission:cache-reset
```

`RoleSeeder` calls `forgetCachedPermissions()` automatically.
