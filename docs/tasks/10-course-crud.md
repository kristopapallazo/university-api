# 10 — Course (Lenda) CRUD (Admin)

> **Backlog ref:** BE-09 (courses portion)
> **Priority:** P3 — no blockers
> **Effort:** ~1h
> **Stack:** Laravel 11, Sanctum, `role:admin` middleware
> **Branch:** `<yourname>/course-crud` (example: `ornela/course-crud`)
> **Before you start:** read `docs/onboarding.md`. No migrations needed.

---

## Goal

Add admin CRUD (create / update / delete) for `Lenda` (courses/subjects).

`LendaController` currently has only `index` and `show`. You will add `store`, `update`, and `destroy`, and wire up the write routes in `routes/api.php`.

---

## Workflow

1. Pull latest `main` after task 09 is merged: `git checkout main && git pull`
2. Create branch: `<yourname>/course-crud`
3. Add routes first, then the controller methods
4. Single commit: `course-crud`
5. Run `make fix` before committing, `make ci` before pushing
6. Open PR against `main`, request review from `kristopapallazo`
7. Link this doc in the PR description

---

## Step 0 — Add routes to api.php

Open `routes/api.php`. Inside the `Route::middleware('role:admin')->group(...)` block, add:

```php
// Courses (admin write)
Route::post('/courses', [LendaController::class, 'store']);
Route::put('/courses/{id}', [LendaController::class, 'update']);
Route::delete('/courses/{id}', [LendaController::class, 'destroy']);
```

The read routes (`GET /courses`, `GET /courses/{id}`) already exist above that block — do not move them.

---

## Step 1 — Implement in LendaController

**File:** `app/Http/Controllers/LendaController.php`
**Model:** `Lenda` | **Table:** `LENDA` | **Resource:** `LendaResource`

**Column map:**

| DB column  | JSON field     | Notes                    |
| ---------- | -------------- | ------------------------ |
| `LEND_EMER`| `name`         | required string          |
| `LEND_KOD` | `code`         | required, unique in table|
| `DEP_ID`   | `departmentId` | required FK → DEPARTAMENT|

### store

**Route:** `POST /api/v1/courses`

```php
public function store(Request $request): JsonResponse
{
    $data = $request->validate([
        'name'         => 'required|string|max:150',
        'code'         => 'required|string|max:20|unique:LENDA,LEND_KOD',
        'departmentId' => 'required|integer|exists:DEPARTAMENT,DEP_ID',
    ]);

    $lenda = Lenda::create([
        'LEND_EMER' => $data['name'],
        'LEND_KOD'  => strtoupper($data['code']),
        'DEP_ID'    => $data['departmentId'],
    ]);

    return $this->success(new LendaResource($lenda), 'Lënda u krijua me sukses.', 201);
}
```

> `strtoupper` on `code` keeps codes consistent (e.g. `inf101` → `INF101`). The uniqueness check is case-insensitive in MySQL by default, so this just normalises the stored value.

### update

**Route:** `PUT /api/v1/courses/{id}`

```php
public function update(Request $request, int $id): JsonResponse
{
    $lenda = Lenda::findOrFail($id);

    $data = $request->validate([
        'name'         => 'sometimes|required|string|max:150',
        'code'         => 'sometimes|required|string|max:20|unique:LENDA,LEND_KOD,' . $id . ',LEND_ID',
        'departmentId' => 'sometimes|required|integer|exists:DEPARTAMENT,DEP_ID',
    ]);

    $lenda->update([
        'LEND_EMER' => $data['name']         ?? $lenda->LEND_EMER,
        'LEND_KOD'  => isset($data['code'])  ? strtoupper($data['code']) : $lenda->LEND_KOD,
        'DEP_ID'    => $data['departmentId'] ?? $lenda->DEP_ID,
    ]);

    return $this->success(new LendaResource($lenda->fresh()), 'Lënda u përditësua me sukses.');
}
```

### destroy

**Route:** `DELETE /api/v1/courses/{id}`

> A course referenced in curricula or active sections will fail via FK — let the DB enforce it.

```php
public function destroy(int $id): JsonResponse
{
    $lenda = Lenda::findOrFail($id);
    $lenda->delete();

    return $this->success(null, 'Lënda u fshi me sukses.');
}
```

---

## Acceptance criteria

- [ ] `POST /api/v1/courses` creates a course, returns `201` with the resource
- [ ] `POST /api/v1/courses` with an existing `code` returns `422`
- [ ] `POST /api/v1/courses` with code `"inf201"` stores it as `"INF201"`
- [ ] `PUT /api/v1/courses/{id}` with own code does not trigger unique error
- [ ] `PUT /api/v1/courses/{id}` sending only `name` changes only `name`
- [ ] `DELETE /api/v1/courses/{id}` returns `200` with `data: null`
- [ ] All 3 routes return `403` without the `admin` role
- [ ] `make ci` passes
