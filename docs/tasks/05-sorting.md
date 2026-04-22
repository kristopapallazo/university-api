# 05 — Sorting on List Endpoints

> **Priority:** P2 — enhancement
> **Effort:** ~1h
> **Blocked by:** `04-pagination.md` must be merged first
> **Stack:** Laravel 12
> **Branch:** `<yourname>/sorting` (example: `serdar/sorting`)
> **Before you start:** read `docs/onboarding.md`. No migrations needed — one trait + 5 controllers.

---

## Goal

Add standardized sorting to all reference data list endpoints. Same query params, same behavior everywhere.

**Query params:**
```
?sortBy=name&sortOrder=asc
?sortBy=created_at&sortOrder=desc
```

- `sortBy` — field to sort by (must be in the controller's allowed list)
- `sortOrder` — `asc` or `desc` — default `asc`
- If `sortBy` is not provided → no sorting, natural DB order

---

## Workflow (mandatory)

1. Pull latest `main` **after 04-pagination is merged**: `git checkout main && git pull`
2. Create branch: `<yourname>/sorting`
3. Implement S0 first, then S1 endpoints in order
4. One commit per step
5. Run `make fix` before each commit, `make ci` before pushing
6. Open PR against `main`, request review from `kristopapallazo`
7. Link this doc in the PR description

---

## S0 — Create the Sortable trait (do this first)

**File to create:** `app/Http/Traits/Sortable.php`

```php
<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Sortable
{
    protected function applySorting(Builder $query, Request $request, array $allowedFields): Builder
    {
        $sortBy    = $request->query('sortBy');
        $sortOrder = in_array(strtolower($request->query('sortOrder', 'asc')), ['asc', 'desc'])
            ? strtolower($request->query('sortOrder', 'asc'))
            : 'asc';

        if ($sortBy && in_array($sortBy, $allowedFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query;
    }
}
```

**Key rules built into the trait:**
- `sortBy` is validated against a whitelist — prevents SQL injection
- `sortOrder` only accepts `asc`/`desc` — anything else defaults to `asc`
- If `sortBy` is not in the whitelist → silently ignored, no error

---

## S1 — Apply to all list endpoints

Add `use Sortable;` to each controller and wrap the query before paginating.

**Pattern:**
```php
use App\Http\Traits\Sortable;

class FacultyController extends Controller
{
    use Sortable;

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('perPage', 15), 100);

        $faculties = $this->applySorting(
            Faculty::query(),
            $request,
            ['FAK_EM', 'FAK_ID'] // ← allowed sort fields for this endpoint
        )->paginate($perPage);

        return (new PaginatedCollection($faculties))->response();
    }
}
```

### S1-A — FacultyController
**File:** `app/Http/Controllers/FacultyController.php`
**Allowed sort fields:** `FAK_EM` (name), `FAK_ID`

### S1-B — DepartmentController
**File:** `app/Http/Controllers/DepartmentController.php`
**Allowed sort fields:** `DEP_EM` (name), `DEP_ID`

### S1-C — ProgramStudimController
**File:** `app/Http/Controllers/ProgramStudimController.php`
**Allowed sort fields:** `PROG_EM` (name), `PROG_LLOJI` (type: Bachelor/Master/2-vjecare), `PROG_ID`

### S1-D — LendaController
**File:** `app/Http/Controllers/LendaController.php`
**Allowed sort fields:** `LEND_EM` (name), `LEND_KOD` (code), `LEND_ID`

### S1-E — PedagogController
**File:** `app/Http/Controllers/PedagogController.php`
**Allowed sort fields:** `PED_EMER` (first name), `PED_MBIEMER` (last name), `PED_ID`

---

## Acceptance criteria

- [ ] `GET /api/v1/faculties?sortBy=FAK_EM&sortOrder=asc` returns sorted results
- [ ] `GET /api/v1/faculties?sortBy=FAK_EM&sortOrder=desc` returns reverse order
- [ ] `GET /api/v1/faculties?sortBy=invalid_field` is silently ignored, no error
- [ ] `GET /api/v1/faculties?sortOrder=asc` without `sortBy` returns natural order
- [ ] All 5 endpoints support sorting
- [ ] `make ci` passes
