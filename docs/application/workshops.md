# Workshop domain (current implementation)

This document describes **what exists in the codebase today**. Features that are only planned (self-service enrolment, admin CRUD flows beyond the current index/table) are listed at the end—they must not be read as already shipped.

## Purpose

- Represent **scheduled workshops** with a real **time interval** (`starts_at`, `ends_at`) and a **capacity** integer.
- Classify workshops with **`workshop_categories`** and optional **`workshop_category_id`** on each workshop.
- Represent **enrolments** as a **first-class model** (`WorkshopRegistration`) with an explicit **status** (`confirmed`, `waiting_list`), not an anonymous many-to-many pivot.
- Provide **deterministic demo data** via seeders and a **read-only, authenticated** Inertia page listing workshops for users who may **view** workshops. **Employees** default to **upcoming** workshops only; **admins** (`workshops.manage`) default to **all** and see a **table** with extra filters and sorting.

## Source-of-truth file map

| Path                                                                            | Responsibility                                                                                                                                   |
| ------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ |
| `database/migrations/2026_04_12_094425_create_workshop_categories_table.php`    | Creates `workshop_categories` (name, timestamps).                                                                                                |
| `database/migrations/2026_04_12_094502_create_workshops_table.php`              | Creates `workshops` with interval, `capacity`, nullable `workshop_category_id`, `created_by` FK to `users`, index on `starts_at`.                |
| `database/migrations/2026_04_12_094503_create_workshop_registrations_table.php` | Creates `workshop_registrations` with FKs and **unique** `(workshop_id, user_id)`.                                                               |
| `app/Enums/WorkshopRegistrationStatus.php`                                      | Backed enum: `confirmed`, `waiting_list`.                                                                                                        |
| `app/Models/WorkshopCategory.php`                                               | `HasFactory`; workshops relation.                                                                                                                |
| `app/Models/Workshop.php`                                                       | `creator()`, `category()`, `registrations()`; scopes `future()`, `past()`, `ordered()`, `indexOrder()`; `HasFactory`.                            |
| `app/Models/WorkshopRegistration.php`                                           | `workshop()`, `user()`; scopes `confirmed()`, `waitingList()`; enum cast on `status`; `HasFactory`.                                              |
| `app/Models/User.php`                                                           | `createdWorkshops()`, `workshopRegistrations()`; Spatie `HasRoles`.                                                                              |
| `database/factories/WorkshopCategoryFactory.php`                                | Category factory.                                                                                                                                |
| `database/factories/WorkshopFactory.php`                                        | Factory with `upcoming()` state; sets category when present.                                                                                     |
| `database/factories/WorkshopRegistrationFactory.php`                            | Factory with `confirmed()` / `waitingList()` states.                                                                                             |
| `database/seeders/WorkshopCategorySeeder.php`                                   | Seeds curriculum-style categories (Laravel, frontend, data, testing, auth, async, platform, product domain, team practices).                     |
| `database/seeders/RolePermissionSeeder.php`                                     | Spatie roles `admin`, `employee`; permissions `workshops.view`, `workshops.manage`; assigns permissions to roles.                                |
| `database/seeders/AcademyDemoSeeder.php`                                        | Demo admin and employees, many workshops (mix upcoming/closed), registrations, category assignment.                                              |
| `database/seeders/DatabaseSeeder.php`                                           | Calls `RolePermissionSeeder`, `WorkshopCategorySeeder`, then `AcademyDemoSeeder`.                                                                |
| `app/Policies/WorkshopPolicy.php`                                               | `viewAny` / `view` require `workshops.view`; mutations require `workshops.manage`.                                                               |
| `app/Http/Requests/Workshops/ListWorkshopsIndexRequest.php`                     | Validates query filters and admin-only sorting params (`sort`, `direction`). Does not force defaults into the query string.                      |
| `app/Support/Filters/Workshops/BuildWorkshopIndexData.php`                      | Builds the index query (filters + ordering + admin sorting), loads select options, and returns Inertia-ready props (`filters`, columns, fields). |
| `app/Http/Controllers/App/Workshops/WorkshopIndexController.php`                | App-area list page (`/app/workshops`); delegates query construction and renders the employee Inertia page. Redirects admins to the admin area.   |
| `app/Http/Controllers/Admin/Workshops/WorkshopIndexController.php`              | Admin-area list page (`/admin/workshops`); delegates query construction and renders the admin Inertia page (table mode).                         |
| `app/Http/Resources/Workshop/WorkshopListItemResource.php`                      | Serialises list rows: ISO 8601 datetimes, nested `category` / `creator`, `timing_status` (`upcoming` \| `closed`).                               |
| `app/Support/Tables/WorkshopTableColumns.php`                                   | Column/filter metadata for admin table vs employee filter bar.                                                                                   |
| `app/Http/Middleware/HandleInertiaRequests.php`                                 | Shares `auth.workshop_permissions` (`view`, `manage`) for conditional UI.                                                                        |
| `resources/js/pages/app/workshops/Index.vue`                                    | Employee Inertia page: cards + `WorkshopsFilterBar` (query-string driven).                                                                       |
| `resources/js/pages/admin/workshops/Index.vue`                                  | Admin Inertia page: table + sorting/filtering metadata from backend.                                                                             |
| `resources/js/components/tables/WorkshopsIndexTable.vue`                        | Admin table (TanStack-style columns from backend).                                                                                               |
| `resources/js/components/tables/WorkshopsFilterBar.vue`                         | Shared filter UI (query-string driven). Accepts an `indexUrl()` callback so it can work in both app/admin areas.                                 |
| `resources/js/components/cards/WorkshopCard.vue`                                | Employee card layout for a single workshop.                                                                                                      |
| `resources/js/types/models/workshop.ts`                                         | `WorkshopListItem`, `WorkshopPermissions` typings.                                                                                               |
| `resources/js/components/AppSidebar.vue`, `AppHeader.vue`                       | Main nav: **Workshops** link only when `workshop_permissions.view` is true; routes to `/admin/*` when `workshop_permissions.manage` is true.     |

## Design decisions

1. **Interval columns** — `starts_at` and `ends_at` support future overlap checks and reminders without inferring duration from a single timestamp. DB-level `CHECK` constraints for ordering or positive capacity are **not** used in this Laravel schema version; rules are enforced in application code and tests.
2. **Registration as a model** — Supports status, reporting, and waitlists without overloading a pivot table.
3. **Spatie permissions** — `workshops.view` is granted to both `admin` and `employee`; `workshops.manage` to `admin` only. The workshop index is split into two routes: **`GET /app/workshops`** (`app.workshops.index`, requires `can:viewAny`) for the employee browsing UI and **`GET /admin/workshops`** (`admin.workshops.index`, requires `can:create`) for the admin table UI. UI reads `auth.workshop_permissions` so navigation and CTAs stay aligned with the server.
4. **Json API resources** — `WorkshopListItemResource` shapes Inertia `workshopList`; `JsonResource::withoutWrapping()` is set in `AppServiceProvider` so collections resolve to plain arrays.
5. **Query-string filters** — optional `status` (`all` \| `upcoming` \| `closed`; UI label for `all` is “Upcoming and closed”), optional `category_id`, `title` (substring), `starts_on` (date), and for admins `created_by`. When `status` is omitted, the server applies an **effective default** based on role (employee → `upcoming`, admin → `all`) but the UI keeps the select on a neutral placeholder (`Select Status`) until the user chooses a value.
6. **Sorting (admin table only)** — query params `sort` and `direction` (`asc` \| `desc`) are accepted only for `workshops.manage`. Clicking a sortable table header toggles `asc` → `desc` → “no explicit sort”, which falls back to `indexOrder()` (upcoming first, then closed).

## Implemented user flow

1. User must be **authenticated** and **email verified** (middleware on the workshops route group).
2. User must be allowed **`viewAny`** on `Workshop` (i.e. `workshops.view` via policy). Otherwise the server responds **403 Forbidden**.
3. `GET /app/workshops` (`app.workshops.index`) returns Inertia `app/workshops/Index` (employee UI) with:
    - **`workshopList`** — array from `WorkshopListItemResource`;
    - **`filters`** — active filter values echoed for the UI;
    - **`employeeFilterFields`** — non-empty in card mode (simpler filter field defs).
4. `GET /admin/workshops` (`admin.workshops.index`) returns Inertia `admin/workshops/Index` (admin UI) with:
    - **`workshopList`** — array from `WorkshopListItemResource`;
    - **`filters`** — active filter values echoed for the UI;
    - **`showWorkshopTable`** — true;
    - **`workshopTableColumns`** — non-empty (admin column defs + filter options).

## Tests (Pest)

For how tests are organised and executed across the app, see [`tests.md`](tests.md).

| Test file                                     | Coverage                                                                                     |
| --------------------------------------------- | -------------------------------------------------------------------------------------------- |
| `tests/Feature/Domain/WorkshopDomainTest.php` | Eloquent relations, duplicate registration unique constraint, scopes.                        |
| `tests/Feature/WorkshopIndexTest.php`         | Guest redirect; authenticated user with `workshops.view` sees Inertia shape (employee path). |
| `tests/Feature/WorkshopAuthorizationTest.php` | 403 without permission; policy; shared props; admin table mode and sorting via query string. |
| `tests/Feature/AcademyDemoSeederTest.php`     | After `DatabaseSeeder`, roles, users, workshop and registration counts and states.           |
| `tests/Feature/SeededWorkshopsPageTest.php`   | After seed, demo workshop titles present in Inertia props for a demo admin user.             |

## Not implemented (planned / out of scope)

- Public **REST JSON API** for workshops (the app is web + session + Inertia).
- **Create / update / delete** workshops from the UI (the “Create workshop” control is a non-functional placeholder for admins).
- **Self-service enrolment or cancellation** from the UI.

Roadmap items may appear in workspace `docs/todo/` files if your checkout includes them; those files are not the canonical behaviour spec for this app module.
