# Reports — Task Plan

> **Stack:** Laravel 12, Sanctum, SQLite (local) / MySQL (Railway)
> **Before you start:** read `docs/onboarding.md` and `docs/auth-plan.md` for full context.
> **Priority:** these endpoints are read-only and reuse existing tables — no new migrations are needed. Build them before Notifications because they carry no risk and directly unblock the student portal.

---

## Workflow (mandatory)

1. Pull latest `main`: `git checkout main && git pull`
2. Create branch: `<yourname>/reports` (example: `evelyn/reports`)
3. Implement R0 (prerequisite) **first**, then R1 → R2 → R3 in order
4. Commit per task (one commit per R#, small and reviewable)
5. Run `make fix` before committing, `make ci` before pushing
6. Open PR against `main`, request review from `kristopapallazo`
7. Link this doc in the PR description

---

## Data flow

```
STUDENT
  └── NOTA (grades)
        └── PROVIM (exam: type + date)
              └── SEKSION (section: day + time)
                    └── LENDA (course name)

STUDENT
  └── FATURE (invoices: amount + status + date)
```

---

## R0 — Prerequisite: link `User` to `Student` / `Pedagog`

The Reports endpoints need `STU_ID` or `PED_ID` from the authenticated user. This requires two relations on the `User` model. Do this **before** R1.

**File to edit:** `app/Models/User.php`

```php
use App\Models\Student;
use App\Models\Pedagog;
use Illuminate\Database\Eloquent\Relations\HasOne;

public function student(): HasOne
{
    return $this->hasOne(Student::class, 'STU_EMAIL', 'email');
}

public function pedagog(): HasOne
{
    return $this->hasOne(Pedagog::class, 'PED_EMAIL', 'email');
}
```

> The join key is email: `users.email` = `STUDENT.STU_EMAIL` = `PEDAGOG.PED_EMAIL`.

**Acceptance:**

- `auth()->user()->student` returns the matching `Student` or `null`
- `auth()->user()->pedagog` returns the matching `Pedagog` or `null`
- Feature test: create a user + matching student, call the relation, assert it resolves to the right `STU_ID`

---

## R1 — Student: My Grades

**Endpoint:** `GET /api/v1/student/grades`
**Auth:** `auth:sanctum` + `role:student`

Returns all grades for the authenticated student. `STU_ID` is pulled from `auth()->user()->student->STU_ID` — **never** from the URL, query params, or request body.

### Response shape

```json
{
    "data": [
        {
            "gradeId": 1,
            "value": 8.5,
            "date": "2026-01-15",
            "examType": "Final",
            "examDate": "2026-01-14",
            "course": "Bazat e Programimit"
        }
    ],
    "message": "Notat u morën me sukses.",
    "status": 200
}
```

### Field mapping (DB column → JSON key)

| DB column                  | JSON key     | Notes                                |
| -------------------------- | ------------ | ------------------------------------ |
| `NOTA.NOTA_ID`             | `gradeId`    |                                      |
| `NOTA.NOTA_VLERA`          | `value`      | float                                |
| `NOTA.NOTA_DAT`            | `date`       | ISO date (`Y-m-d`)                   |
| `PROVIM.PROV_LLOJI`        | `examType`   | e.g. "Final", "Seminar"              |
| `PROVIM.PROV_DAT`          | `examDate`   | ISO date                             |
| `LENDA.LEND_EMER`          | `course`     | course name                          |

> If DB column names differ from the above, use the real ones — this table is the source of truth the dev aligns to.

### Tables traversed

`NOTA` → `PROVIM` → `SEKSION` → `LENDA`

### Files to create

1. `app/Models/Nota.php`
    ```php
    protected $table = 'NOTA';
    protected $primaryKey = 'NOTA_ID';
    // belongsTo Provim (FK: PROV_ID)
    ```
2. `app/Models/Provim.php`
    ```php
    protected $table = 'PROVIM';
    protected $primaryKey = 'PROV_ID';
    // belongsTo Seksion (FK: SEK_ID)
    ```
3. `app/Models/Seksion.php`
    ```php
    protected $table = 'SEKSION';
    protected $primaryKey = 'SEK_ID';
    // belongsTo Lenda (FK: LEND_ID)
    // belongsTo Pedagog (FK: PED_ID) ← needed for R3
    ```
4. `app/Http/Resources/GradeResource.php` — maps using the table above
5. `app/Http/Controllers/Student/GradeController.php`
    ```php
    public function index(): JsonResponse
    {
        $studentId = auth()->user()->student->STU_ID;
        $grades = Nota::with('provim.seksion.lenda')
            ->where('STU_ID', $studentId)
            ->orderByDesc('NOTA_DAT')
            ->get();
        return $this->success(GradeResource::collection($grades), 'Notat u morën me sukses.');
    }
    ```
6. Register route in `routes/api.php` under `auth:sanctum` + `role:student`

### Acceptance

- Student token → 200 with their own grades, sorted by `NOTA_DAT` desc
- Pedagog/admin token → 403
- Student with no grades → 200 with `"data": []`
- Student sees **only their own grades** — never another student's

### Required tests (`tests/Feature/Student/GradeIndexTest.php`)

- `test_student_sees_only_own_grades` — create 2 students with grades, assert only current user's show up
- `test_pedagog_gets_403`
- `test_admin_gets_403`
- `test_unauthenticated_gets_401`
- `test_empty_grades_returns_empty_array`

---

## R2 — Student: My Invoices

**Endpoint:** `GET /api/v1/student/invoices`
**Auth:** `auth:sanctum` + `role:student`

Returns all invoices for the authenticated student.

### Response shape

```json
{
    "data": [
        {
            "invoiceId": 3,
            "amount": 25000.0,
            "status": "E papaguar",
            "issuedDate": "2026-09-01",
            "description": "Tarifë vjetore 2025-2026"
        }
    ],
    "message": "Faturat u morën me sukses.",
    "status": 200
}
```

### Field mapping (DB column → JSON key)

| DB column            | JSON key       | Notes                                       |
| -------------------- | -------------- | ------------------------------------------- |
| `FATURE.FAT_ID`      | `invoiceId`    |                                             |
| `FATURE.FAT_SHUMA`   | `amount`       | float                                       |
| `FATURE.FAT_STATUS`  | `status`       | `E paguar` / `E papaguar` / `E vonuar` — return as-is |
| `FATURE.FAT_DAT`     | `issuedDate`   | ISO date                                    |
| `FATURE.FAT_PERSHKR` | `description`  | string                                      |

### Tables

`FATURE` (filter by `STU_ID` from `auth()->user()->student->STU_ID`)

### Files to create

1. `app/Models/Fature.php`
    ```php
    protected $table = 'FATURE';
    protected $primaryKey = 'FAT_ID';
    ```
2. `app/Http/Resources/FatureResource.php`
3. `app/Http/Controllers/Student/FatureController.php` — only `index()`
4. Register route under `auth:sanctum` + `role:student`

### Acceptance

- Invoices belong to the current student — never another student's
- `status` returned exactly as stored in DB
- Sorted by `FAT_DAT` desc

### Required tests (`tests/Feature/Student/FatureIndexTest.php`)

- `test_student_sees_only_own_invoices`
- `test_pedagog_gets_403`
- `test_admin_gets_403`
- `test_unauthenticated_gets_401`
- `test_status_strings_returned_as_is` — assert exact match against "E papaguar" etc.

---

## R3 — Pedagog: Grades for a Section

**Endpoint:** `GET /api/v1/pedagog/sections/{sectionId}/grades`
**Auth:** `auth:sanctum` + `role:pedagog`

Returns grades for all students in a given section — **only** if the authenticated pedagog teaches that section (`SEKSION.PED_ID` must match `auth()->user()->pedagog->PED_ID`).

### Response shape

```json
{
    "data": [
        {
            "gradeId": 1,
            "value": 9.0,
            "date": "2026-01-15",
            "examType": "Final",
            "student": {
                "id": 12,
                "firstName": "Arta",
                "lastName": "Hoxha",
                "matriculationNumber": "2021001234"
            }
        }
    ],
    "message": "Notat u morën me sukses.",
    "status": 200
}
```

### Field mapping (DB column → JSON key)

| DB column              | JSON key              |
| ---------------------- | --------------------- |
| `NOTA.NOTA_ID`         | `gradeId`             |
| `NOTA.NOTA_VLERA`      | `value`               |
| `NOTA.NOTA_DAT`        | `date`                |
| `PROVIM.PROV_LLOJI`    | `examType`            |
| `STUDENT.STU_ID`       | `student.id`          |
| `STUDENT.STU_EMER`     | `student.firstName`   |
| `STUDENT.STU_MBIEMER`  | `student.lastName`    |
| `STUDENT.STU_MATRIK`   | `student.matriculationNumber` |

### Tables traversed

`SEKSION` (verify `PED_ID`) → `PROVIM` → `NOTA` → `STUDENT`

### Files to create

1. **New resource** `app/Http/Resources/SectionGradeResource.php`
   - Do **not** reuse `GradeResource` from R1 — R3 exposes a nested `student` block that R1 must not leak. Keep them separate.
2. `app/Http/Controllers/Pedagog/SectionGradeController.php`
    - Load section, check `$section->PED_ID === auth()->user()->pedagog->PED_ID` — return 403 with message `"Nuk keni leje për këtë seksion."` if not
    - Return 404 if section doesn't exist (let route model binding handle it)
3. Register route under `auth:sanctum` + `role:pedagog`

### Acceptance

- Pedagog sees grades only for sections they teach
- Pedagog hitting another pedagog's section → 403
- Student token → 403
- Non-existent `sectionId` → 404
- Section with no grades yet → 200 with `"data": []`

### Required tests (`tests/Feature/Pedagog/SectionGradeIndexTest.php`)

- `test_pedagog_sees_grades_for_own_section`
- `test_pedagog_gets_403_for_other_pedagog_section`
- `test_student_gets_403`
- `test_admin_gets_403`
- `test_unauthenticated_gets_401`
- `test_nonexistent_section_returns_404`

---

## Conventions (must follow)

- Input → `FormRequest` in `app/Http/Requests/` — never `$request->validate()` inline
- Output → always via a `Resource` class in `app/Http/Resources/`
- Response envelope → always `{ data, message, status }` (use the `ApiResponse` trait)
- User-facing messages → **Albanian only**
- Never accept `STU_ID`, `PED_ID`, or `role` from client input — always derive from `auth()->user()`
- One `Resource` per endpoint shape — do not overload a single Resource with conditional fields
- Before commit: `make fix`
- Before push: `make ci`

---

## Definition of Done (for the whole PR)

- [ ] R0 relations added to `User` model with passing test
- [ ] R1, R2, R3 endpoints return the exact response shapes above
- [ ] All required tests (listed per task) pass locally via `make ci`
- [ ] Scribe docs regenerated (`make docs`) and each endpoint is visible at `/docs`
- [ ] No `STU_ID` / `PED_ID` / `role` read from request input anywhere
- [ ] All user-facing messages in Albanian
- [ ] PR opened against `main`, linked to this doc, review requested from `kristopapallazo`
