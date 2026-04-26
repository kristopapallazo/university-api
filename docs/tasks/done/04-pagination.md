# 04 — Pagination on List Endpoints

> **Priority:** P2 — enhancement, no blockers
> **Effort:** ~1.5h
> **Stack:** Laravel 12, Sanctum
> **Branch:** `<yourname>/pagination` (example: `ornela/pagination`)
> **Before you start:** read `docs/onboarding.md`. No migrations needed — controllers and resources only.

---

## Goal

Add standardized pagination to all list endpoints. One shared response shape used everywhere so the frontend can handle all paginated lists the same way.

**Standardized pagination response:**
```json
{
  "data": [ ...items ],
  "pagination": {
    "current": 1,
    "pageSize": 15,
    "total": 45
  },
  "message": "OK",
  "status": 200
}
```

**Defaults (apply to all endpoints):**
- `page` → `1`
- `perPage` → `15`
- max allowed `perPage` → `100` (clamp silently, do not throw error)

---

## Workflow (mandatory)

1. Pull latest `main`: `git checkout main && git pull`
2. Create branch: `<yourname>/pagination`
3. Implement P0 first, then P1 endpoints in order
4. One commit per step (`P0`, `P1-faculties`, `P1-departments`, etc.)
5. Run `make fix` before each commit, `make ci` before pushing
6. Open PR against `main`, request review from `kristopapallazo`
7. Link this doc in the PR description

---

## P0 — Create the shared PaginatedCollection (do this first)

**File to create:** `app/Http/Resources/PaginatedCollection.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PaginatedCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        $paginator = $this->resource;

        return [
            'data'       => $this->collection,
            'pagination' => [
                'current'  => $paginator->currentPage(),
                'pageSize' => $paginator->perPage(),
                'total'    => $paginator->total(),
            ],
            'message' => 'OK',
            'status'  => 200,
        ];
    }
}
```

This is the only place the pagination shape is defined. Every controller below uses this class — never inline the shape manually.

---

## P1 — Apply to all list endpoints

Apply pagination to these 5 controllers. The pattern is identical for all — follow it exactly.

**Pattern:**
```php
public function index(Request $request): JsonResponse
{
    $perPage = min((int) $request->query('perPage', 15), 100);

    $items = ModelName::paginate($perPage);

    return (new PaginatedCollection($items))->response();
}
```

### P1-A — FacultyController
**File:** `app/Http/Controllers/FacultyController.php`
**Model:** `Faculty` | **Table:** `FAKULTET`

### P1-B — DepartmentController
**File:** `app/Http/Controllers/DepartmentController.php`
**Model:** `Department` | **Table:** `DEPARTAMENT`

### P1-C — ProgramStudimController
**File:** `app/Http/Controllers/ProgramStudimController.php`
**Model:** `ProgramStudim` | **Table:** `PROGRAM_STUDIM`

### P1-D — LendaController
**File:** `app/Http/Controllers/LendaController.php`
**Model:** `Lenda` | **Table:** `LENDA`

### P1-E — PedagogController
**File:** `app/Http/Controllers/PedagogController.php`
**Model:** `Pedagog` | **Table:** `PEDAGOG`

---

## Acceptance criteria

- [ ] `GET /api/v1/faculties` returns `data` + `pagination` object
- [ ] `GET /api/v1/faculties?page=2&perPage=5` returns correct page
- [ ] `GET /api/v1/faculties?perPage=200` is clamped to 100, no error
- [ ] Default `perPage` is 15 when not provided
- [ ] All 5 endpoints follow identical response shape
- [ ] `make ci` passes
