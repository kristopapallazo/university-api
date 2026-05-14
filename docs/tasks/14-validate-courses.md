# 14 — Validate Courses (Department exists)

> **Backlog ref:** nouncheck — apply validation to course endpoints
> **Priority:** P1 — depends on task 12
> **Effort:** ~1h
> **Stack:** Laravel 11, ValidationService (from task 12)
> **Branch:** `<yourname>/validate-courses` (example: `ornela/validate-courses`)
> **Before you start:** Task 12 must be merged. Verify `ValidationService` is available in your branch.

---

## Goal

Add entity validation to `LendaController` (courses) so that:

- `POST /courses` validates that the `departmentId` exists before creating a course
- `PUT /courses/{id}` validates that the `departmentId` exists before updating

If validation fails, a `404 EntityNotFoundException` is returned.

---

## Workflow

1. `git checkout main && git pull` (pull task 12)
2. `git checkout -b <yourname>/validate-courses`
3. Update `LendaController` → add validation to `store()` and `update()`
4. Test manually with curl or Postman
5. `make fix`, `make ci` before pushing
6. Open PR against `main`

---

## Step 1 — Update LendaController

**File:** `app/Http/Controllers/LendaController.php`

Add the ValidationService injection and add validation to `store()` and `update()` methods:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Models\Lenda;
use App\Services\ValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LendaController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ValidationService $validation) {}

    public function index(): JsonResponse
    {
        $courses = Lenda::all();
        return $this->success($courses, 'OK');
    }

    public function show(int $id): JsonResponse
    {
        $course = Lenda::findOrFail($id);
        return $this->success($course, 'OK');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'code'          => 'required|string|max:50',
            'departmentId'  => 'required|integer',
        ]);

        // Validate that the department exists
        $this->validation->validateDepartmentExists($data['departmentId']);

        $course = Lenda::create([
            'LEND_EMER'  => $data['name'],
            'LEND_KOD'   => $data['code'],
            'DEP_ID'     => $data['departmentId'],
        ]);

        return $this->success($course, 'Lenda u krijua me sukses.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $course = Lenda::findOrFail($id);

        $data = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'code'          => 'sometimes|string|max:50',
            'departmentId'  => 'sometimes|integer',
        ]);

        // Validate that the department exists (only if departmentId is being updated)
        if (isset($data['departmentId'])) {
            $this->validation->validateDepartmentExists($data['departmentId']);
        }

        $updateData = [];
        if (isset($data['name'])) {
            $updateData['LEND_EMER'] = $data['name'];
        }
        if (isset($data['code'])) {
            $updateData['LEND_KOD'] = $data['code'];
        }
        if (isset($data['departmentId'])) {
            $updateData['DEP_ID'] = $data['departmentId'];
        }

        $course->update($updateData);

        return $this->success($course, 'Lenda u përditësua me sukses.');
    }

    public function destroy(int $id): JsonResponse
    {
        $course = Lenda::findOrFail($id);
        $course->delete();

        return $this->success(null, 'Lenda u fshi me sukses.');
    }
}
```

---

## Step 2 — Add validateDepartmentExists to ValidationService

**File:** `app/Services/ValidationService.php`

Add this method to the existing ValidationService (from task 12):

```php
/**
 * Validate that a department exists by ID.
 *
 * @throws EntityNotFoundException if not found
 */
public function validateDepartmentExists(int $departmentId): bool
{
    if (!Department::find($departmentId)) {
        throw new EntityNotFoundException('Departament', $departmentId);
    }

    return true;
}
```

Don't forget to import the Department model at the top:

```php
use App\Models\Department;
```

---

## Manual smoke test

After `make dev`, test the controller:

```bash
# Test 1: Create a course with invalid departmentId
curl -X POST http://localhost:8000/api/v1/courses \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-token>" \
  -d '{"name":"Algorithms","code":"CS101","departmentId":99999}'

# Expected: 404 EntityNotFoundException

# Test 2: Create a course with valid departmentId
DEPT_ID=$(curl -s http://localhost:8000/api/v1/departments \
  -H "Authorization: Bearer <your-token>" | jq '.data[0].id')

curl -X POST http://localhost:8000/api/v1/courses \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-token>" \
  -d "{\"name\":\"Web Development\",\"code\":\"CS201\",\"departmentId\":$DEPT_ID}"

# Expected: 201 success response

# Test 3: Update a course with invalid departmentId
curl -X PUT http://localhost:8000/api/v1/courses/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-token>" \
  -d '{"departmentId":99999}'

# Expected: 404 EntityNotFoundException
```

---

## Acceptance criteria

- [ ] `LendaController@store()` calls `$this->validation->validateDepartmentExists()`
- [ ] `LendaController@update()` calls validation only if `departmentId` is being updated
- [ ] `ValidationService` has `validateDepartmentExists()` method
- [ ] POST/PUT with invalid `departmentId` returns 404 with `EntityNotFoundException` message
- [ ] POST/PUT with valid `departmentId` succeeds and returns 201/200 with course data
- [ ] Both `name` and `code` are validated as required
- [ ] Manual smoke tests pass
- [ ] `make ci` passes
