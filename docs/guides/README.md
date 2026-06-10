# Developer guides

Markdown guides for the Gahez project. Browsable copies are also at **`/docs`** (LaRecipe reads from `resources/docs/1.0/`).

> **Keep in sync:** When updating a guide, update both `docs/guides/` and `resources/docs/1.0/` copies where they mirror each other.

| Guide | File | Topics |
|-------|------|--------|
| Project progress | [project-progress.md](project-progress.md) | Modules, features, recent milestones |
| Running tests | [running-tests.md](running-tests.md) | `composer test`, PHPUnit, suites |
| Translation | [translation.md](translation.md) | EN/AR, `messages.php`, Spatie translatable |
| Permissions | [permissions.md](permissions.md) | Spatie roles, admin permissions |
| Queue & import | [queue-import-setup.md](queue-import-setup.md) | `queue:work`, Excel import/export |

## API reference

| Resource | Path |
|----------|------|
| Full API (single file) | [../API.md](../API.md) |
| LaRecipe pages | `resources/docs/1.0/` (overview, customer-api, delivery-api, …) |

## API testing (Apidog)

See [../apidog/README.md](../apidog/README.md) for OpenAPI and Postman collection import.
