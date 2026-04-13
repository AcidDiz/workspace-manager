# Workshop domain (current implementation)

This document describes **what exists in the codebase today**. Features that are only planned (for example self-service enrolment) are listed at the end—they must not be read as already shipped.

## Purpose

- Represent **scheduled workshops** with a real **time interval** (`starts_at`, `ends_at`) and a **capacity** integer.
- Classify workshops with **`workshop_categories`** and optional **`workshop_category_id`** on each workshop.
- Represent **enrolments** as a **first-class model** (`WorkshopRegistration`) with an explicit **status** (`confirmed`, `waiting_list`), not an anonymous many-to-many pivot.
- Provide **deterministic demo data** via seeders and **authenticated** Inertia flows: employees **browse** workshops; **admins** (`workshops.manage`) use the **admin** area for listing (table, filters, sort) and **full CRUD** (create, edit, delete). **Employees** default to **upcoming** workshops only; **admins** default to **all** on the index.

For **HTTP routes, query parameters, and Inertia response props** for the workshop index pages, see [`../api/workshops.md`](../api/workshops.md).

## Source-of-truth file map

| Path                                                                            | Responsibility                                                                                                                                   |
| ------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ |
| `database/migrations/2026_04_12_094425_create_workshop_categories_table.php`    | Creates `workshop_categories` (name, timestamps).                                                                                                |
| `database/migrations/2026_04_12_094502_create_workshops_table.php`              | Creates `workshops` with interval, `capacity`, nullable `workshop_category_id`, `created_by` FK to `users`, index on `starts_at`.                |
| `database/migrations/2026_04_12_094503_create_workshop_registrations_table.php` | Creates `workshop_registrations` with FKs and **unique** `(workshop_id, user_id)`.                                                               |
| `app/Enums/Workshop/WorkshopRegistrationStatusEnum.php`                         | Backed enum: `confirmed`, `waiting_list` (`WorkshopRegistration` cast + scopes).                                                                  |
| `app/Enums/Workshop/WorkshopStatusEnum.php`                                     | Backed enum: `all`, `upcoming`, `closed` for the workshops index `status` query; `label()` + `filterSelectOptions()` for filter UIs.                 |
| `app/Models/WorkshopCategory.php`                                               | `HasFactory`; workshops relation.                                                                                                                |
| `app/Models/Workshop.php`                                                       | `creator()`, `category()`, `registrations()`; `casts()` for `starts_at` / `ends_at`; `HasFactory`. Query scopes are provided by two traits (see below), not declared on the class body. |
| `app/Models/Scopes/Workshop/WorkshopFilterScopes.php`                           | Trait used by `Workshop`: `withIndexRelations` (`with category, creator`), `upcoming` / `closed` (by `starts_at` vs `now()`), `status` (maps `WorkshopStatusEnum` string values; `all` = no extra constraint), `filterCategoryId`, `searchTitle` (trimmed `LIKE`), `startsOn` (`whereDate`), `createdBy`. |
| `app/Models/Scopes/Workshop/WorkshopSortScopes.php`                             | Trait used by `Workshop`: `ordered` (`starts_at` asc), `indexOrder` (upcoming rows first, then closed, each by `starts_at` asc), `sortForAdminIndex` (admin-only sort keys: `title`, `starts_at`, `category.name`, `creator.name`, `timing_status`; related sorts use scalar subqueries; unknown sort falls back to `indexOrder`). |
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
| `app/Http/Requests/Workshops/ListWorkshopsIndexRequest.php`                     | Validates query filters and admin-only sorting params (`sort`, `direction`); `status` via `Rule::enum(WorkshopStatusEnum::class)`. Does not force defaults into the query string. |
| `app/Support/Filters/Workshops/WorkshopUserFilters.php`                         | Non-admin workshops index (`workshops.view` without `workshops.manage`): card grid query, **`index()`** returns `cardFilterFields` (filter bar shape, not table columns); effective `status` default **`upcoming`**. |
| `app/Support/Filters/Workshops/WorkshopAdminFilters.php`                        | Admin workshops index (`workshops.manage`): table query (sort, `created_by`, creator options), **`index()`** returns `workshopTableColumns` from `WorkshopTableColumns`; effective `status` default **`all`**. |
| `app/Http/Controllers/App/Workshops/WorkshopIndexController.php`                | Invokable: `GET /app/workshops`. If `workshops.manage`, **redirects** to `admin.workshops.index` with the same query string. Otherwise `WorkshopUserFilters::index()` → Inertia `app/workshops/Index` (`workshopList`, `filters`, `cardFilterFields` only). |
| `app/Http/Controllers/Admin/Workshops/WorkshopIndexController.php`              | Invokable: `GET /admin/workshops`. `WorkshopAdminFilters::index()` → Inertia `admin/workshops/Index` (`workshopList`, `filters`, `workshopTableColumns` only). |
| `app/Http/Controllers/Admin/Workshops/WorkshopCreateController.php`            | Invokable: `GET /admin/workshops/create`. Inertia `admin/workshops/Create` with `categories` (`id`, `name`). |
| `app/Http/Controllers/Admin/Workshops/WorkshopStoreController.php`             | Invokable: `POST /admin/workshops`. Persists a workshop; sets `created_by` to the current user; flash toast; redirect to `admin.workshops.index`. |
| `app/Http/Controllers/Admin/Workshops/WorkshopEditController.php`              | Invokable: `GET /admin/workshops/{workshop}/edit`. Inertia `admin/workshops/Edit` with `workshop` (form payload) and `categories`. |
| `app/Http/Controllers/Admin/Workshops/WorkshopUpdateController.php`            | Invokable: `PUT /admin/workshops/{workshop}`. Updates attributes from `UpdateWorkshopRequest`; flash toast; redirect to index. |
| `app/Http/Controllers/Admin/Workshops/WorkshopDestroyController.php`           | Invokable: `DELETE /admin/workshops/{workshop}`. Deletes the model; DB **cascade** removes `workshop_registrations`; flash toast; redirect to index. |
| `app/Http/Requests/Workshops/StoreWorkshopRequest.php`                         | Authorizes `create`; validates title, description, category, interval, capacity; normalises empty `workshop_category_id` to `null`. |
| `app/Http/Requests/Workshops/UpdateWorkshopRequest.php`                      | Authorizes `update` on route `workshop`; same field rules as store. |
| `app/Http/Resources/Workshop/WorkshopFormResource.php`                         | Form payload for edit: `datetime-local`-friendly `starts_at` / `ends_at` in `config('app.timezone')`, plus scalar fields. |
| `app/Http/Resources/Workshop/WorkshopListItemResource.php`                      | Serialises list rows: ISO 8601 datetimes, nested `category` / `creator`, `timing_status` (`upcoming` \| `closed`).                               |
| `app/Http/Resources/WorkshopCategory/WorkshopCategoryFilterSelectOptionResource.php` | `{ value, label }` for category `<select>` options; used by `WorkshopTableColumns` and `WorkshopUserFilters` (card filter defs).                  |
| `app/Http/Resources/User/FilterSelectOptionResource.php`                        | `{ value, label }` for creator filter options in `WorkshopTableColumns` (admin table).                                                            |
| `app/Support/Tables/WorkshopTableColumns.php`                                   | Admin index **table** metadata only: **`adminTable($categories, $creators)`** — columns (title, category.name, starts_at, creator.name, timing_status, **`_actions`** with `cast_type` `actions`, not filterable/sortable). The shared `Table` Vue component passes **filterable** columns into `FiltersBar`. Status option labels match the filter query (`all` = “Upcoming and closed”, etc.). |
| `app/Http/Middleware/HandleInertiaRequests.php`                                 | Shares `auth.workshop_permissions` (`view`, `manage`) for conditional UI.                                                                        |
| `resources/js/pages/app/workshops/Index.vue`                                    | Employee Inertia page: cards + `FiltersBar` (query-string driven).                                                                                |
| `resources/js/pages/admin/workshops/Index.vue`                                  | Admin Inertia page: `Table` + sorting/filtering; **Create workshop** link; `#row-actions` slot with `ManageRowActions`.                           |
| `resources/js/pages/admin/workshops/Create.vue`                                 | Admin create form (Inertia `<Form>` POST to store).                                                                                                |
| `resources/js/pages/admin/workshops/Edit.vue`                                   | Admin edit form (Inertia `<Form>` PUT to update); breadcrumbs via `setLayoutProps`.                                                              |
| `resources/js/components/forms/WorkshopForm.vue`                              | Shared workshop fields: title, description, category select, `datetime-local` interval, capacity.                                                |
| `resources/js/components/tables/ManageRowActions.vue`                          | Reusable per-row **Edit** + **Delete** (opens `ConfirmDeleteDialog`); parent passes Wayfinder `*.form()` for delete.                               |
| `resources/js/components/dialogs/ConfirmDeleteDialog.vue`                       | Reusable confirm dialog + Inertia `<Form>`; title, description, and form attributes from parent.                                                  |
| `resources/js/components/tables/Table.vue`                                      | Generic index table + embedded `FiltersBar`; slot `#row-actions` for `cast_type === 'actions'` cells.                                             |
| `resources/js/components/tables/FiltersBar.vue`                                 | Shared filter UI (query-string driven); props `fields` are **`FilterBarField`** (`param`, `label`, …). Table mode maps filterable `TableColumn` rows into that shape inside `Table.vue`. |
| `resources/js/components/cards/WorkshopCard.vue`                                | Employee card layout for a single workshop.                                                                                                      |
| `resources/js/types/models/workshop.ts`                                         | `WorkshopListItem`, `WorkshopPermissions`, `WorkshopCategoryOption`, `WorkshopFormPayload`.                                                        |
| `resources/js/components/AppSidebar.vue`, `AppHeader.vue`                       | Main nav: **Workshops** link only when `workshop_permissions.view` is true; routes to `/admin/*` when `workshop_permissions.manage` is true.     |

## Index query pipeline (`WorkshopUserFilters` / `WorkshopAdminFilters`)

`WorkshopUserFilters::index()` and `WorkshopAdminFilters::index()` each turn validated query input into the `Workshop` collection plus **page-specific** Inertia props (no shared mega-payload).

1. **Effective status** — `requestedStatus` from `validated['status']` (may be null). If null: **user** path uses `upcoming`; **admin** path uses `all`.
2. **Eloquent chain** (scopes on `Workshop`):
   - `withIndexRelations()` — eager `category`, `creator`.
   - `status($effectiveStatus)` — `upcoming` / `closed` narrow by `starts_at`; `all` leaves the query unchanged.
   - `filterCategoryId`, `searchTitle`, `startsOn` — optional filters from the query string.
   - **Admin only (`WorkshopAdminFilters`):** `createdBy` when `created_by` is present; `sortForAdminIndex($sort, $direction)` when `sort` is present (direction only with an explicit sort). If `sort` is empty/unknown, `sortForAdminIndex` falls back per `WorkshopSortScopes`.
   - **User only (`WorkshopUserFilters`):** `indexOrder()` after filters (no admin sort).
3. **Supporting queries** — `WorkshopCategory` ordered by name on both paths; **admin only**, distinct `created_by` → `User` rows for the “Created by” filter options.
4. **Response shape** — `filters` echoes the **requested** `status` (not the effective default), plus other validated keys. **App** response adds `cardFilterFields` only; **admin** adds `workshopTableColumns` only.

## Design decisions

1. **Interval columns** — `starts_at` and `ends_at` support future overlap checks and reminders without inferring duration from a single timestamp. DB-level `CHECK` constraints for ordering or positive capacity are **not** used in this Laravel schema version; rules are enforced in application code and tests.
2. **Registration as a model** — Supports status, reporting, and waitlists without overloading a pivot table.
3. **Spatie permissions** — `workshops.view` is granted to both `admin` and `employee`; `workshops.manage` to `admin` only. The workshop index is split into two routes: **`GET /app/workshops`** (`app.workshops.index`, requires `can:viewAny`) for the employee browsing UI and **`GET /admin/workshops`** (`admin.workshops.index`, requires `can:create`) for the admin table UI. UI reads `auth.workshop_permissions` so navigation and CTAs stay aligned with the server.
4. **Json API resources** — `WorkshopListItemResource` shapes Inertia `workshopList`; `JsonResource::withoutWrapping()` is set in `AppServiceProvider` so collections resolve to plain arrays.
5. **Query-string filters** — optional `status` (`all` \| `upcoming` \| `closed`; UI label for `all` is “Upcoming and closed”), optional `category_id`, `title` (substring), `starts_on` (date), and for admins `created_by`. When `status` is omitted, the server applies an **effective default** based on role (employee → `upcoming`, admin → `all`) but the UI keeps the select on a neutral placeholder (`Select Status`) until the user chooses a value.
6. **Sorting (admin table only)** — query params `sort` and `direction` (`asc` \| `desc`) are validated only for `workshops.manage`. The backend applies ordering through `Workshop::sortForAdminIndex()`: empty / unknown `sort` falls back to `indexOrder()`. Sorting by related attributes (`category.name`, `creator.name`) uses **scalar subqueries** in `orderBy` to avoid join duplication. `timing_status` ascending reuses `indexOrder()`; descending flips the upcoming-vs-closed partition then orders by `starts_at` desc.
7. **Admin CRUD = one invokable controller per HTTP route** — Under `App\Http\Controllers\Admin\Workshops\`, each named route maps to a single `__invoke` controller (`WorkshopCreateController`, `WorkshopStoreController`, `WorkshopEditController`, `WorkshopUpdateController`, `WorkshopDestroyController`). Validation lives in `StoreWorkshopRequest` / `UpdateWorkshopRequest`. There is **no** multi-method `AdminWorkshopController`.
8. **Deleting workshops** — The UI states that deleting removes enrolments. The database enforces this: `workshop_registrations.workshop_id` uses **`cascadeOnDelete()`** on the workshop. Admins may still **edit** workshops whose `starts_at` is already in the past (e.g. corrections); there is no extra server rule blocking that.

## Implemented user flow

1. User must be **authenticated** and **email verified** (middleware on the workshops route group).
2. User must be allowed **`viewAny`** on `Workshop` (i.e. `workshops.view` via policy). Otherwise the server responds **403 Forbidden**.
3. `GET /app/workshops` (`app.workshops.index`) returns Inertia `app/workshops/Index` (employee UI) for users **without** `workshops.manage` (admins with manage permission are redirected to step 4’s route first). Response includes:
    - **`workshopList`** — array from `WorkshopListItemResource`;
    - **`filters`** — active filter values echoed for the UI;
    - **`cardFilterFields`** — filter bar metadata for the card index (`param` + `label` + `input_type` + optional `options`; not table column definitions).
4. `GET /admin/workshops` (`admin.workshops.index`) returns Inertia `admin/workshops/Index` (admin UI) with:
    - **`workshopList`** — array from `WorkshopListItemResource`;
    - **`filters`** — active filter values echoed for the UI;
    - **`workshopTableColumns`** — non-empty (admin column defs + filter options, including the non-filterable **Actions** column).
5. Admins with `workshops.manage` may **`GET /admin/workshops/create`**, **`POST /admin/workshops`**, **`GET /admin/workshops/{workshop}/edit`**, **`PUT /admin/workshops/{workshop}`**, and **`DELETE /admin/workshops/{workshop}`** (see [`../api/workshops.md`](../api/workshops.md)). Success responses use **`Inertia::flash('toast', …)`** (same pattern as settings profile update). Employees receive **403** on those routes.

## Tests (Pest)

For how tests are organised and executed across the app, see [`tests.md`](tests.md).

| Test file                                     | Coverage                                                                                     |
| --------------------------------------------- | -------------------------------------------------------------------------------------------- |
| `tests/Feature/Domain/WorkshopDomainTest.php` | Eloquent relations, duplicate registration unique constraint, workshop query scopes (`upcoming`, `ordered`, …). |
| `tests/Feature/WorkshopIndexTest.php`         | Guest redirect; authenticated user with `workshops.view` sees Inertia shape (employee path). |
| `tests/Feature/WorkshopAuthorizationTest.php` | 403 without permission; policy; shared props; admin table mode and sorting via query string. |
| `tests/Feature/AdminWorkshopManagementTest.php` | Admin CRUD: store/update/destroy, validation failures, employee forbidden on admin mutations, Inertia create/edit pages, cascade delete of registrations. |
| `tests/Browser/AdminWorkshopBrowserTest.php` | After login: open create page; edit workshop from admin table; delete workshop via confirm dialog (assert row gone + model deleted). |
| `tests/Feature/AcademyDemoSeederTest.php`     | After `DatabaseSeeder`, roles, users, workshop and registration counts and states.           |
| `tests/Feature/SeededWorkshopsPageTest.php`   | After seed, demo workshop titles present in Inertia props for a demo admin user.             |

## Not implemented (planned / out of scope)

- Public **REST JSON API** for workshops (the app is web + session + Inertia).
- **Self-service enrolment or cancellation** from the UI.

Roadmap items may appear in workspace `docs/todo/` files if your checkout includes them; those files are not the canonical behaviour spec for this app module.
