# 13 — Validate Departments & Programs (Faculty exists)

> **Backlog ref:** nouncheck — apply validation to department and program endpoints
> **Priority:** P1 — depends on task 12
> **Effort:** ~2h
> **Stack:** Laravel 11, ValidationService (from task 12)
> **Branch:** `<yourname>/validate-depts-programs` (example: `ornela/validate-depts-programs`)
> **Before you start:** Task 12 must be merged. Verify `ValidationService` is available in your branch.

---

## Goal

Add entity validation to `DepartmentController` and `ProgramStudimController` so that:

- `POST /departments` validates that the `faculty_id` exists before creating a department
- `PUT /departments/{id}` validates that the `faculty_id` exists before updating
- `POST /programs` validates that the `faculty_id` exists before creating a program
- `PUT /programs/{id}` validates that the `faculty_id` exists before updating

If validation fails, a `404 EntityNotFoundException` is returned with a clear message.

---

## Workflow

1. `git checkout main && git pull` (pull task 12)
2. `git checkout -b <yourname>/validate-depts-programs`
3. Update `DepartmentController` → add validation to `store()` and `update()`
4. Update `ProgramStudimController` → add validation to `store()` and `update()`
5. Commit per logical step (`validate-departments`, `validate-programs`)
6. Test manually with curl or Postman
7. `make fix`, `make ci` before pushing
8. Open PR against `main`

---

## Step 1 — Update DepartmentController

**File:** `app/Http/Controllers/DepartmentController.php`

Add the ValidationService injection and add validation to `store()` and `update()` methods:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Models\Department;
use App\Services\ValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ValidationService $validation) {}

    public function index(): JsonResponse
    {
        $departments = Department::all();
        return $this->success($departments, 'OK');
    }

    public function show(int $id): JsonResponse
    {
        $department = Department::findOrFail($id);
        return $this->success($department, 'OK');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'facultyId'  => 'required|integer',
        ]);

        // Validate that the faculty exists
        $this->validation->validateFacultyExists($data['facultyId']);

        $department = Department::create([
            'DEP_EM' => $data['name'],
            'FAK_ID' => $data['facultyId'],
        ]);

        return $this->success($department, 'Departamenti u krijua me sukses.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $department = Department::findOrFail($id);

        $data = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'facultyId'  => 'sometimes|integer',
        ]);

        // Validate that the faculty exists (only if facultyId is being updated)
        if (isset($data['facultyId'])) {
            $this->validation->validateFacultyExists($data['facultyId']);
        }

        $updateData = [];
        if (isset($data['name'])) {
            $updateData['DEP_EM'] = $data['name'];
        }
        if (isset($data['facultyId'])) {
            $updateData['FAK_ID'] = $data['facultyId'];
        }

        $department->update($updateData);

        return $this->success($department, 'Departamenti u përditësua me sukses.');
    }

    public function destroy(int $id): JsonResponse
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return $this->success(null, 'Departamenti u fshi me sukses.');
    }
}
```

---

## Step 2 — Update ProgramStudimController

**File:** `app/Http/Controllers/ProgramStudimController.php`

Add the ValidationService injection and add validation to `store()` and `update()` methods:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Models\Program;
use App\Services\ValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramStudimController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ValidationService $validation) {}

    public function index(): JsonResponse
    {
        $programs = Program::all();
        return $this->success($programs, 'OK');
    }

    public function show(int $id): JsonResponse
    {
        $program = Program::findOrFail($id);
        return $this->success($program, 'OK');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'facultyId'  => 'required|integer',
            // add other program fields as needed
        ]);

        // Validate that the faculty exists
        $this->validation->validateFacultyExists($data['facultyId']);

        $program = Program::create([
            'PROG_EM' => $data['name'],
            'FAK_ID'  => $data['facultyId'],
        ]);

        return $this->success($program, 'Programi studim u krijua me sukses.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $program = Program::findOrFail($id);

        $data = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'facultyId'  => 'sometimes|integer',
        ]);

        // Validate that the faculty exists (only if facultyId is being updated)
        if (isset($data['facultyId'])) {
            $this->validation->validateFacultyExists($data['facultyId']);
        }

        $updateData = [];
        if (isset($data['name'])) {
            $updateData['PROG_EM'] = $data['name'];
        }
        if (isset($data['facultyId'])) {
            $updateData['FAK_ID'] = $data['facultyId'];
        }

        $program->update($updateData);

        return $this->success($program, 'Programi studim u përditësua me sukses.');
    }

    public function destroy(int $id): JsonResponse
    {
        $program = Program::findOrFail($id);
        $program->delete();

        return $this->success(null, 'Programi studim u fshi me sukses.');
    }
}
```

---

## Manual smoke test

After `make dev`, test both controllers:

```bash
# Test 1: Create a department with invalid facultyId
curl -X POST http://localhost:8000/api/v1/departments \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-token>" \
  -d '{"name":"Engineering","facultyId":99999}'

# Expected: 404 JSON response with EntityNotFoundException message

# Test 2: Create a department with valid faculty_id (get a real one first)
FACULTY_ID=$(curl -s http://localhost:8000/api/v1/faculties \
  -H "Authorization: Bearer <your-token>" | jq '.data[0].id')

curl -X POST http://localhost:8000/api/v1/departments \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-token>" \
  -d "{\"name\":\"Computer Science\",\"facultyId\":$FACULTY_ID}"

# Expected: 201 success response with created department

# Test 3: Update a department with invalid facultyId
curl -X PUT http://localhost:8000/api/v1/departments/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-token>" \
  -d '{"facultyId":99999}'

# Expected: 404 EntityNotFoundException

# Test 4: Create a program with invalid facultyId
curl -X POST http://localhost:8000/api/v1/programs \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-token>" \
  -d '{"name":"Bachelor","facultyId":99999}'

# Expected: 404 EntityNotFoundException

# Test 5: Create a program with valid faculty_id
curl -X POST http://localhost:8000/api/v1/programs \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-token>" \
  -d "{\"name\":\"Master\",\"facultyId\":$FACULTY_ID}"

# Expected: 201 success response
```

---

## Acceptance criteria

- [ ] `DepartmentController@store()` calls `$this->validation->validateFacultyExists()`
- [ ] `DepartmentController@update()` calls validation only if `faculty_id` is being updated
- [ ] `ProgramStudimController@store()` calls `$this->validation->validateFacultyExists()`
- [ ] `ProgramStudimController@update()` calls validation only if `faculty_id` is being updated
- [ ] POST/PUT with invalid `faculty_id` returns 404 with `EntityNotFoundException` message
- [ ] POST/PUT with valid `faculty_id` succeeds and returns 201/200 with entity data
- [ ] Both controllers properly validate request input (name, faculty_id, etc.)
- [ ] Manual smoke tests pass
- [ ] `make ci` passes
