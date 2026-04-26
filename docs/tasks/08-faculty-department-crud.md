# 08 — Faculty & Department CRUD (Admin)

> **Backlog ref:** BE-07
> **Priority:** P3 — no blockers, routes already wired
> **Effort:** ~1.5h
> **Stack:** Laravel 11, Sanctum, `role:admin` middleware
> **Branch:** `<yourname>/faculty-department-crud` (example: `ornela/faculty-department-crud`)
> **Before you start:** read `docs/onboarding.md`. No migrations needed — controllers only.

---

## Goal

Implement `store`, `update`, and `destroy` on `FacultyController` and `DepartmentController`.

The routes already exist in `routes/api.php` under `middleware('role:admin')` and currently return `501`. You are replacing those stub bodies with real logic. Do not touch the routes file.

**All 6 methods follow the same 3-step pattern:**
1. Validate the request
2. Create / update / delete the model
3. Return using the `ApiResponse` trait

---

## Workflow

1. Pull latest `main`: `git checkout main && git pull`
2. Create branch: `<yourname>/faculty-department-crud`
3. Implement A then B in order — A establishes the pattern, B repeats it
4. One commit per controller (`faculty-store-update-destroy`, `department-store-update-destroy`)
5. Run `make fix` before each commit, `make ci` before pushing
6. Open PR against `main`, request review from `kristopapallazo`
7. Link this doc in the PR description

---

## Reference — shared patterns

**ApiResponse trait** (`app/Http/Traits/ApiResponse.php`):
```php
$this->success($data, 'message', 201);   // created
$this->success($data, 'message', 200);   // updated / deleted
$this->error('message', 404);            // not found (handled by findOrFail automatically)
```

**Validation** — use `$request->validate([...])` directly inside the method. Do not create Form Requests for this task.

**Albanian messages** — all `message` strings must be in Albanian.

---

## A — FacultyController

**File:** `app/Http/Controllers/FacultyController.php`
**Model:** `Faculty` | **Table:** `FAKULTET` | **Resource:** `FacultyResource`

### A-1 — store

**Route:** `POST /api/v1/faculties`

```php
public function store(Request $request): JsonResponse
{
    $data = $request->validate([
        'name'   => 'required|string|max:255',
        'deanId' => 'nullable|integer|exists:PEDAGOG,PED_ID',
    ]);

    $faculty = Faculty::create([
        'FAK_EM' => $data['name'],
        'PED_ID' => $data['deanId'] ?? null,
    ]);

    return $this->success(new FacultyResource($faculty), 'Fakulteti u krijua me sukses.', 201);
}
```

### A-2 — update

**Route:** `PUT /api/v1/faculties/{id}`

```php
public function update(Request $request, int $id): JsonResponse
{
    $faculty = Faculty::findOrFail($id);

    $data = $request->validate([
        'name'   => 'sometimes|required|string|max:255',
        'deanId' => 'nullable|integer|exists:PEDAGOG,PED_ID',
    ]);

    $faculty->update([
        'FAK_EM' => $data['name'] ?? $faculty->FAK_EM,
        'PED_ID' => array_key_exists('deanId', $data) ? $data['deanId'] : $faculty->PED_ID,
    ]);

    return $this->success(new FacultyResource($faculty->fresh()), 'Fakulteti u përditësua me sukses.');
}
```

### A-3 — destroy

**Route:** `DELETE /api/v1/faculties/{id}`

> Deleting a faculty that has departments will fail due to the FK constraint the DB enforces. Let the DB throw — do not add manual checks here.

```php
public function destroy(int $id): JsonResponse
{
    $faculty = Faculty::findOrFail($id);
    $faculty->delete();

    return $this->success(null, 'Fakulteti u fshi me sukses.');
}
```

---

## B — DepartmentController

**File:** `app/Http/Controllers/DepartmentController.php`
**Model:** `Department` | **Table:** `DEPARTAMENT` | **Resource:** `DepartmentResource`

### B-1 — store

**Route:** `POST /api/v1/departments`

```php
public function store(Request $request): JsonResponse
{
    $data = $request->validate([
        'name'      => 'required|string|max:255',
        'facultyId' => 'required|integer|exists:FAKULTET,FAK_ID',
        'headId'    => 'nullable|integer|exists:PEDAGOG,PED_ID',
    ]);

    $department = Department::create([
        'DEP_EM' => $data['name'],
        'FAK_ID' => $data['facultyId'],
        'PED_ID' => $data['headId'] ?? null,
    ]);

    return $this->success(new DepartmentResource($department), 'Departamenti u krijua me sukses.', 201);
}
```

### B-2 — update

**Route:** `PUT /api/v1/departments/{id}`

```php
public function update(Request $request, int $id): JsonResponse
{
    $department = Department::findOrFail($id);

    $data = $request->validate([
        'name'      => 'sometimes|required|string|max:255',
        'facultyId' => 'sometimes|required|integer|exists:FAKULTET,FAK_ID',
        'headId'    => 'nullable|integer|exists:PEDAGOG,PED_ID',
    ]);

    $department->update([
        'DEP_EM' => $data['name']      ?? $department->DEP_EM,
        'FAK_ID' => $data['facultyId'] ?? $department->FAK_ID,
        'PED_ID' => array_key_exists('headId', $data) ? $data['headId'] : $department->PED_ID,
    ]);

    return $this->success(new DepartmentResource($department->fresh()), 'Departamenti u përditësua me sukses.');
}
```

### B-3 — destroy

**Route:** `DELETE /api/v1/departments/{id}`

> Same as Faculty: let the DB enforce FK constraints, no manual checks needed.

```php
public function destroy(int $id): JsonResponse
{
    $department = Department::findOrFail($id);
    $department->delete();

    return $this->success(null, 'Departamenti u fshi me sukses.');
}
```

---

## Acceptance criteria

- [ ] `POST /api/v1/faculties` creates a faculty, returns `201` with the new resource
- [ ] `PUT /api/v1/faculties/{id}` updates fields that are sent, ignores missing ones
- [ ] `DELETE /api/v1/faculties/{id}` deletes, returns `200` with `data: null`
- [ ] `POST /api/v1/faculties` with an invalid `deanId` returns `422`
- [ ] All 3 faculty routes return `403` when called without the `admin` role
- [ ] Same checks pass for all 3 department routes
- [ ] `make ci` passes
