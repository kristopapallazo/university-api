# API Patterns & Conventions

Standard patterns for building REST API endpoints in this project. Follow these conventions for consistency across all endpoints.

---

## List Endpoints

All list endpoints follow the same pattern: pagination + optional sorting.

### Response Shape

```json
{
  "data": [
    { "id": 1, "name": "..." },
    { "id": 2, "name": "..." }
  ],
  "pagination": {
    "current": 1,
    "pageSize": 15,
    "total": 45
  },
  "message": "OK",
  "status": 200
}
```

**Must always include:**
- `data` — array of resource items
- `pagination` — metadata about pagination
- `message` — "OK" for success
- `status` — HTTP status code (200)

**Do NOT include:**
- Laravel's default `links` and `meta` fields (suppress via `PaginatedCollection`)

### Implementation Pattern

```php
use App\Http\Traits\Sortable;
use App\Http\Resources\PaginatedCollection;

class ExampleController extends Controller
{
    use Sortable;

    public function index(Request $request): JsonResponse
    {
        // 1. Pagination param (default 15, max 100)
        $perPage = min((int) $request->query('perPage', 15), 100);

        // 2. Build query
        $query = ExampleModel::query();

        // 3. Apply filters (optional)
        if ($request->filled('filterId')) {
            $query->where('filter_id', $request->integer('filterId'));
        }

        // 4. Apply sorting (optional)
        $items = $this->applySorting($query, $request, ['field_1', 'field_2', 'field_3'])
            ->paginate($perPage);

        // 5. Return paginated collection
        return (new PaginatedCollection($items->through(fn ($item) => new ExampleResource($item))))->response();
    }
}
```

---

## Pagination

**File:** `app/Http/Resources/PaginatedCollection.php`

Handles all paginated responses. Never inline pagination shape manually — always use this class.

### Query Parameters

| Param | Type | Default | Notes |
|-------|------|---------|-------|
| `page` | int | 1 | Current page |
| `perPage` | int | 15 | Items per page, max 100 (clamped silently) |

### Examples

```
GET /api/v1/faculties
→ page=1, perPage=15 (defaults)

GET /api/v1/faculties?page=2&perPage=10
→ page=2, 10 items per page

GET /api/v1/faculties?perPage=200
→ clamped to 100, no error
```

---

## Sorting

**File:** `app/Http/Traits/Sortable.php`

Optional sorting on list endpoints. Use the `Sortable` trait to safely apply sorting with whitelist validation.

### Query Parameters

| Param | Type | Values | Notes |
|-------|------|--------|-------|
| `sortBy` | string | whitelist | Field to sort by — must be in the controller's allowed list |
| `sortOrder` | string | `asc`, `desc` | Order, defaults to `asc` |

### Behavior

- **Valid `sortBy`** — applies `ORDER BY field ASC/DESC`
- **Invalid `sortBy`** — silently ignored, natural DB order
- **Invalid `sortOrder`** — defaults to `asc`
- **No `sortBy` provided** — no sorting, natural DB order (undefined)

### Examples

```
GET /api/v1/faculties?sortBy=name&sortOrder=asc
→ ORDER BY name ASC

GET /api/v1/faculties?sortBy=name&sortOrder=desc
→ ORDER BY name DESC

GET /api/v1/faculties?sortBy=invalid_field
→ silently ignored, natural DB order

GET /api/v1/faculties
→ no sortBy, natural DB order (FE must send sortBy if stable order needed)
```

### Whitelist Per Controller

| Controller | Allowed Fields |
|---|---|
| FacultyController | `FAK_EM` (name), `FAK_ID` |
| DepartmentController | `DEP_EM` (name), `DEP_ID` |
| ProgramStudimController | `PROG_EM` (name), `PROG_LLOJI` (type), `PROG_ID` |
| LendaController | `LEND_EM` (name), `LEND_KOD` (code), `LEND_ID` |
| PedagogController | `PED_EMER` (first name), `PED_MBIEMER` (last name), `PED_ID` |

### Implementation

```php
use App\Http\Traits\Sortable;

class ExampleController extends Controller
{
    use Sortable;

    public function index(Request $request): JsonResponse
    {
        // ...
        $items = $this->applySorting(
            ExampleModel::query(),
            $request,
            ['field_1', 'field_2', 'field_3']  // ← whitelist
        )->paginate($perPage);
        // ...
    }
}
```

---

## Security

### SQL Injection Prevention

Both `Sortable` and `PaginatedCollection` prevent SQL injection:

- **Sortable:** whitelist validation — only whitelisted fields can be sorted
- **Pagination:** built-in Laravel protection — perPage is cast to int

Never pass user input directly to `orderBy()` or `limit()` without validation.

---

## References

- [04 — Pagination on List Endpoints](tasks/04-pagination.md)
- [05 — Sorting on List Endpoints](tasks/05-sorting.md)
