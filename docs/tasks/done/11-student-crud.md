# 11 — Student CRUD (Admin)

> **Backlog ref:** BE-10
> **Priority:** P3 — no blockers
> **Effort:** ~2.5h
> **Stack:** Laravel 11, Sanctum, `role:admin` middleware
> **Branch:** `<yourname>/student-crud` (example: `ornela/student-crud`)
> **Before you start:** read `docs/onboarding.md`. No migrations needed.

---

## Goal

Give admins the ability to list, view, create, update, and deactivate students.

Creating a student requires **two rows**: one in `STUDENT` (profile data) and one in `users` (auth account). The link between them is the email — `User::student()` is a `hasOne` keyed on `STU_EMAIL = email`. Students authenticate via Google OAuth only — no password is set when the admin creates the account.

Deleting a student who has grades, invoices, or registrations will fail via FK constraint. For that reason, **delete is implemented as a status change** (`STU_STATUS = 'Ç'regjistruar'`), not a hard delete. This is safe and reversible.

---

## Workflow

1. Pull latest `main`: `git checkout main && git pull`
2. Create branch: `<yourname>/student-crud`
3. Create the resource file first, then the controller, then routes, then test
4. Single commit: `student-crud`
5. Run `make fix` before committing, `make ci` before pushing
6. Open PR against `main`, request review from `kristopapallazo`
7. Link this doc in the PR description

---

## Step 0 — Create StudentResource

**File to create:** `app/Http/Resources/StudentResource.php`

```php
<?php

namespace App\Http\Resources;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Student */
class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->STU_ID,
            'firstName'      => $this->STU_EM,
            'lastName'       => $this->STU_MB,
            'fathersName'    => $this->STU_ATESI,
            'gender'         => $this->STU_GJINI,
            'birthDate'      => $this->STU_DTL?->toDateString(),
            'matriculation'  => $this->STU_NR_MATRIKULL,
            'email'          => $this->STU_EMAIL,
            'phone'          => $this->STU_TEL,
            'enrolledAt'     => $this->STU_DAT_REGJISTRIM?->toDateString(),
            'status'         => $this->STU_STATUS,
            'dormRoomId'     => $this->DHOM_ID,
        ];
    }
}
```

---

## Step 1 — Create the Admin StudentController

**File to create:** `app/Http/Controllers/Admin/StudentController.php`

Look at `app/Http/Controllers/Admin/NjoftimController.php` for the Admin namespace pattern.

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginatedCollection;
use App\Http\Resources\StudentResource;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\Sortable;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    use ApiResponse, Sortable;
```

### index

List all students with pagination and optional `status` filter.

```php
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('perPage', 15), 100);

        $query = Student::query();

        if ($request->filled('status')) {
            $query->where('STU_STATUS', $request->query('status'));
        }

        $students = $this->applySorting($query, $request, ['STU_MB', 'STU_EM', 'STU_ID'])
            ->paginate($perPage);

        return (new PaginatedCollection($students->through(fn ($s) => new StudentResource($s))))->response();
    }
```

### show

```php
    public function show(int $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        return $this->success(new StudentResource($student), 'Studenti u mor me sukses.');
    }
```

### store

Creates both the `STUDENT` row and the `users` auth account in a single transaction.

```php
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'firstName'     => 'required|string|max:100',
            'lastName'      => 'required|string|max:100',
            'fathersName'   => 'nullable|string|max:100',
            'gender'        => 'required|in:M,F',
            'birthDate'     => 'required|date',
            'matriculation' => 'required|string|max:20|unique:STUDENT,STU_NR_MATRIKULL',
            'email'         => 'required|email|unique:STUDENT,STU_EMAIL|unique:users,email',
            'phone'         => 'nullable|string|max:20',
            'enrolledAt'    => 'nullable|date',
            'dormRoomId'    => 'nullable|integer|exists:DHOME,DHOM_ID',
        ]);

        $student = DB::transaction(function () use ($data) {
            $student = Student::create([
                'STU_EM'            => $data['firstName'],
                'STU_MB'            => $data['lastName'],
                'STU_ATESI'         => $data['fathersName'] ?? null,
                'STU_GJINI'         => $data['gender'],
                'STU_DTL'           => $data['birthDate'],
                'STU_NR_MATRIKULL'  => $data['matriculation'],
                'STU_EMAIL'         => $data['email'],
                'STU_TEL'           => $data['phone'] ?? null,
                'STU_DAT_REGJISTRIM'=> $data['enrolledAt'] ?? now()->toDateString(),
                'STU_STATUS'        => 'Aktiv',
                'DHOM_ID'           => $data['dormRoomId'] ?? null,
            ]);

            User::create([
                'name'     => $data['firstName'] . ' ' . $data['lastName'],
                'email'    => $data['email'],
                'password' => null,
                'role'     => 'student',
                'provider' => 'google',
            ]);

            return $student;
        });

        return $this->success(new StudentResource($student), 'Studenti u regjistrua me sukses.', 201);
    }
```

### update

Updates profile fields on the `STUDENT` row only. Does not change the `users` row (email is the link — changing it would break auth, so it is intentionally excluded from update).

```php
    public function update(Request $request, int $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        $data = $request->validate([
            'firstName'   => 'sometimes|required|string|max:100',
            'lastName'    => 'sometimes|required|string|max:100',
            'fathersName' => 'nullable|string|max:100',
            'gender'      => 'sometimes|required|in:M,F',
            'birthDate'   => 'sometimes|required|date',
            'phone'       => 'nullable|string|max:20',
            'status'      => 'sometimes|required|in:Aktiv,Pezulluar,I diplomuar,Ç\'regjistruar',
            'dormRoomId'  => 'nullable|integer|exists:DHOME,DHOM_ID',
        ]);

        $student->update([
            'STU_EM'    => $data['firstName']  ?? $student->STU_EM,
            'STU_MB'    => $data['lastName']   ?? $student->STU_MB,
            'STU_ATESI' => array_key_exists('fathersName', $data) ? $data['fathersName'] : $student->STU_ATESI,
            'STU_GJINI' => $data['gender']     ?? $student->STU_GJINI,
            'STU_DTL'   => $data['birthDate']  ?? $student->STU_DTL,
            'STU_TEL'   => array_key_exists('phone', $data) ? $data['phone'] : $student->STU_TEL,
            'STU_STATUS'=> $data['status']     ?? $student->STU_STATUS,
            'DHOM_ID'   => array_key_exists('dormRoomId', $data) ? $data['dormRoomId'] : $student->DHOM_ID,
        ]);

        return $this->success(new StudentResource($student->fresh()), 'Studenti u përditësua me sukses.');
    }
```

### destroy

Sets `STU_STATUS` to `'Ç'regjistruar'` — does not hard-delete because the student may have grades, invoices, or registrations linked via FK.

```php
    public function destroy(int $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        $student->update(['STU_STATUS' => "Ç'regjistruar"]);

        return $this->success(null, 'Studenti u çregjistrua me sukses.');
    }
}
```

---

## Step 2 — Add routes to api.php

Add the import at the top of `routes/api.php`:

```php
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
```

Then, inside the `Route::middleware('role:admin')->group(...)` block, add:

```php
// Students (admin CRUD)
Route::get('/admin/students', [AdminStudentController::class, 'index']);
Route::get('/admin/students/{id}', [AdminStudentController::class, 'show']);
Route::post('/admin/students', [AdminStudentController::class, 'store']);
Route::put('/admin/students/{id}', [AdminStudentController::class, 'update']);
Route::delete('/admin/students/{id}', [AdminStudentController::class, 'destroy']);
```

Note: these are under `/admin/students` (not `/students`) to keep admin writes clearly separated from any future student-facing read endpoint.

---

## Acceptance criteria

- [ ] `GET /api/v1/admin/students` returns paginated list
- [ ] `GET /api/v1/admin/students?status=Aktiv` filters correctly
- [ ] `POST /api/v1/admin/students` creates student + user row in one transaction; returns `201`
- [ ] If the transaction fails mid-way (e.g. duplicate email in users), neither row is persisted
- [ ] `POST /api/v1/admin/students` with duplicate `matriculation` or `email` returns `422`
- [ ] `PUT /api/v1/admin/students/{id}` does not accept or change `email`
- [ ] `DELETE /api/v1/admin/students/{id}` sets status to `Ç'regjistruar`, does not remove the row
- [ ] All 5 routes return `403` without the `admin` role
- [ ] `make ci` passes
