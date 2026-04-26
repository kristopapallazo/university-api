# 09 — Program & Pedagog CRUD (Admin)

> **Backlog ref:** BE-09 (partial)
> **Priority:** P3 — depends on task 08 being merged first
> **Effort:** ~2h
> **Stack:** Laravel 11, Sanctum, `role:admin` middleware
> **Branch:** `<yourname>/program-pedagog-crud` (example: `ornela/program-pedagog-crud`)
> **Before you start:** read `docs/onboarding.md`. No migrations needed.

---

## Goal

Add admin CRUD (create / update / delete) for `ProgramStudim` (study programs) and `Pedagog` (pedagogues).

Unlike task 08 where routes already existed, here you must:
1. Add write routes to `routes/api.php` (inside the existing `role:admin` group)
2. Add `store`, `update`, and `destroy` methods to both controllers

---

## Workflow

1. Pull latest `main` after task 08 is merged: `git checkout main && git pull`
2. Create branch: `<yourname>/program-pedagog-crud`
3. Implement A then B in order
4. One commit per section (`program-crud`, `pedagog-crud`)
5. Run `make fix` before each commit, `make ci` before pushing
6. Open PR against `main`, request review from `kristopapallazo`
7. Link this doc in the PR description

---

## Reference — shared patterns

Copy the ApiResponse + validation pattern from `FacultyController` (task 08). Same rules:
- `$request->validate([...])` inline — no Form Requests
- Albanian message strings everywhere
- `findOrFail` for show/update/destroy — do not catch the 404 manually, Laravel handles it

---

## Step 0 — Add routes to api.php

Open `routes/api.php`. Inside the `Route::middleware('role:admin')->group(function () { ... })` block, add:

```php
// Programs (admin write)
Route::post('/programs', [ProgramStudimController::class, 'store']);
Route::put('/programs/{id}', [ProgramStudimController::class, 'update']);
Route::delete('/programs/{id}', [ProgramStudimController::class, 'destroy']);

// Pedagogues (admin write)
Route::post('/pedagogues', [PedagogController::class, 'store']);
Route::put('/pedagogues/{id}', [PedagogController::class, 'update']);
Route::delete('/pedagogues/{id}', [PedagogController::class, 'destroy']);
```

The read routes (`GET /programs`, `GET /programs/{id}`, etc.) already exist above that block — do not move or duplicate them.

---

## A — ProgramStudimController

**File:** `app/Http/Controllers/ProgramStudimController.php`
**Model:** `ProgramStudim` | **Table:** `PROGRAM_STUDIM` | **Resource:** `ProgramStudimResource`

**Valid levels:** `Bachelor`, `Master`, `Doktorature`

### A-1 — store

**Route:** `POST /api/v1/programs`

```php
public function store(Request $request): JsonResponse
{
    $data = $request->validate([
        'name'         => 'required|string|max:255',
        'level'        => 'required|in:Bachelor,Master,Doktorature',
        'credits'      => 'required|integer|min:1',
        'departmentId' => 'required|integer|exists:DEPARTAMENT,DEP_ID',
    ]);

    $program = ProgramStudim::create([
        'PROG_EM'  => $data['name'],
        'PROG_NIV' => $data['level'],
        'PROG_KRD' => $data['credits'],
        'DEP_ID'   => $data['departmentId'],
    ]);

    return $this->success(new ProgramStudimResource($program), 'Programi i studimit u krijua me sukses.', 201);
}
```

### A-2 — update

**Route:** `PUT /api/v1/programs/{id}`

```php
public function update(Request $request, int $id): JsonResponse
{
    $program = ProgramStudim::findOrFail($id);

    $data = $request->validate([
        'name'         => 'sometimes|required|string|max:255',
        'level'        => 'sometimes|required|in:Bachelor,Master,Doktorature',
        'credits'      => 'sometimes|required|integer|min:1',
        'departmentId' => 'sometimes|required|integer|exists:DEPARTAMENT,DEP_ID',
    ]);

    $program->update([
        'PROG_EM'  => $data['name']         ?? $program->PROG_EM,
        'PROG_NIV' => $data['level']        ?? $program->PROG_NIV,
        'PROG_KRD' => $data['credits']      ?? $program->PROG_KRD,
        'DEP_ID'   => $data['departmentId'] ?? $program->DEP_ID,
    ]);

    return $this->success(new ProgramStudimResource($program->fresh()), 'Programi i studimit u përditësua me sukses.');
}
```

### A-3 — destroy

**Route:** `DELETE /api/v1/programs/{id}`

> Deleting a program with enrolled students will fail via FK constraint — let the DB enforce it.

```php
public function destroy(int $id): JsonResponse
{
    $program = ProgramStudim::findOrFail($id);
    $program->delete();

    return $this->success(null, 'Programi i studimit u fshi me sukses.');
}
```

---

## B — PedagogController

**File:** `app/Http/Controllers/PedagogController.php`
**Model:** `Pedagog` | **Table:** `PEDAGOG` | **Resource:** `PedagogResource`

**Model columns:**

| Column           | Mapped from      | Type   | Notes                    |
| ---------------- | ---------------- | ------ | ------------------------ |
| `PED_EM`         | `firstName`      | string | required                 |
| `PED_MB`         | `lastName`       | string | required                 |
| `PED_GJINI`      | `gender`         | string | required — `M` or `F`   |
| `PED_TITULLI`    | `title`          | string | required (e.g. Prof. Dr.)|
| `PED_EMAIL`      | `email`          | string | required, unique in table|
| `PED_TEL`        | `phone`          | string | nullable                 |
| `PED_DTL`        | `birthDate`      | date   | nullable                 |
| `PED_DT_PUNESIM` | `hireDate`       | date   | nullable                 |
| `DEP_ID`         | `departmentId`   | int    | required, FK → DEPARTAMENT|

### B-1 — store

**Route:** `POST /api/v1/pedagogues`

```php
public function store(Request $request): JsonResponse
{
    $data = $request->validate([
        'firstName'    => 'required|string|max:100',
        'lastName'     => 'required|string|max:100',
        'gender'       => 'required|in:M,F',
        'title'        => 'required|string|max:100',
        'email'        => 'required|email|unique:PEDAGOG,PED_EMAIL',
        'phone'        => 'nullable|string|max:20',
        'birthDate'    => 'nullable|date',
        'hireDate'     => 'nullable|date',
        'departmentId' => 'required|integer|exists:DEPARTAMENT,DEP_ID',
    ]);

    $pedagog = Pedagog::create([
        'PED_EM'         => $data['firstName'],
        'PED_MB'         => $data['lastName'],
        'PED_GJINI'      => $data['gender'],
        'PED_TITULLI'    => $data['title'],
        'PED_EMAIL'      => $data['email'],
        'PED_TEL'        => $data['phone'] ?? null,
        'PED_DTL'        => $data['birthDate'] ?? null,
        'PED_DT_PUNESIM' => $data['hireDate'] ?? null,
        'DEP_ID'         => $data['departmentId'],
    ]);

    return $this->success(new PedagogResource($pedagog), 'Pedagogu u krijua me sukses.', 201);
}
```

### B-2 — update

**Route:** `PUT /api/v1/pedagogues/{id}`

```php
public function update(Request $request, int $id): JsonResponse
{
    $pedagog = Pedagog::findOrFail($id);

    $data = $request->validate([
        'firstName'    => 'sometimes|required|string|max:100',
        'lastName'     => 'sometimes|required|string|max:100',
        'gender'       => 'sometimes|required|in:M,F',
        'title'        => 'sometimes|required|string|max:100',
        'email'        => 'sometimes|required|email|unique:PEDAGOG,PED_EMAIL,' . $id . ',PED_ID',
        'phone'        => 'nullable|string|max:20',
        'birthDate'    => 'nullable|date',
        'hireDate'     => 'nullable|date',
        'departmentId' => 'sometimes|required|integer|exists:DEPARTAMENT,DEP_ID',
    ]);

    $pedagog->update([
        'PED_EM'         => $data['firstName']    ?? $pedagog->PED_EM,
        'PED_MB'         => $data['lastName']     ?? $pedagog->PED_MB,
        'PED_GJINI'      => $data['gender']       ?? $pedagog->PED_GJINI,
        'PED_TITULLI'    => $data['title']        ?? $pedagog->PED_TITULLI,
        'PED_EMAIL'      => $data['email']        ?? $pedagog->PED_EMAIL,
        'PED_TEL'        => array_key_exists('phone', $data)     ? $data['phone']     : $pedagog->PED_TEL,
        'PED_DTL'        => array_key_exists('birthDate', $data) ? $data['birthDate'] : $pedagog->PED_DTL,
        'PED_DT_PUNESIM' => array_key_exists('hireDate', $data)  ? $data['hireDate']  : $pedagog->PED_DT_PUNESIM,
        'DEP_ID'         => $data['departmentId'] ?? $pedagog->DEP_ID,
    ]);

    return $this->success(new PedagogResource($pedagog->fresh()), 'Pedagogu u përditësua me sukses.');
}
```

### B-3 — destroy

**Route:** `DELETE /api/v1/pedagogues/{id}`

> A pedagog assigned to active sections or serving as a faculty dean / department head will fail via FK — let the DB enforce it.

```php
public function destroy(int $id): JsonResponse
{
    $pedagog = Pedagog::findOrFail($id);
    $pedagog->delete();

    return $this->success(null, 'Pedagogu u fshi me sukses.');
}
```

---

## Acceptance criteria

- [ ] `POST /api/v1/programs` creates a program, returns `201`
- [ ] `PUT /api/v1/programs/{id}` partial update works (sending only `credits` changes only `credits`)
- [ ] `DELETE /api/v1/programs/{id}` returns `200` with `data: null`
- [ ] `POST /api/v1/programs` with `level: "PhD"` returns `422`
- [ ] `POST /api/v1/pedagogues` creates a pedagog, returns `201`
- [ ] `POST /api/v1/pedagogues` with a duplicate email returns `422`
- [ ] `PUT /api/v1/pedagogues/{id}` with own email does not trigger unique validation error
- [ ] All 6 routes return `403` without the `admin` role
- [ ] `make ci` passes
