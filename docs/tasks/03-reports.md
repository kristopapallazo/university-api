# Reports — Task Plan

> **Priority:** këto endpoint-e janë read-only dhe përdorin tabela ekzistuese — nuk nevojiten migrime të reja.
> Ndërtoji para Notifications sepse nuk kanë rrezik dhe zhbllokojnë direkt portalin e studentit.
>
> **Para se të fillosh:** lexo `docs/onboarding.md` dhe përfundo detyrat në `docs/tasks/phase-2.md`.

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

## Prerequisite — Lidhja User → Student / Pedagog

Endpoint-et e Reports marrin `STU_ID` ose `PED_ID` nga useri i kyçur.
Kjo kërkon relacion në modelin `User`:

```php
// app/Models/User.php
public function student(): HasOne
{
    return $this->hasOne(Student::class, 'STU_EMAIL', 'email');
}

public function pedagog(): HasOne
{
    return $this->hasOne(Pedagog::class, 'PED_EMAIL', 'email');
}
```

> Emaili është pika e lidhjes: `users.email` = `STUDENT.STU_EMAIL` = `PEDAGOG.PED_EMAIL`.
> Shto këtë në `User.php` **para** se të fillosh R1.

---

## R1 — Student: My Grades

**Endpoint:** `GET /api/v1/student/grades`

Kthen të gjitha notat për studentin e kyçur.
Student ID merret nga `auth()->user()->student->STU_ID` — asnjëherë nga URL ose query params.

**Response shape:**

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

**Tabela te lidhura:** `NOTA` → `PROVIM` → `SEKSION` → `LENDA`

**Files to create:**

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
    ```
4. `app/Http/Resources/GradeResource.php` — ekspozo sipas shape-it lart
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
6. Route nën `auth:sanctum` + `role:student` në `routes/api.php`

**Acceptance:**

- Student token → 200 me notat e tij
- Pedagog/admin token → 403
- Studenti sheh vetëm notat e tij — asnjëherë të studentëve të tjerë

---

## R2 — Student: My Invoices

**Endpoint:** `GET /api/v1/student/invoices`

Kthen të gjitha faturat për studentin e kyçur.

**Response shape:**

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

**Tabela:** `FATURE` (filter by `STU_ID` nga `auth()->user()`)

**Files to create:**

1. `app/Models/Fature.php`
    ```php
    protected $table = 'FATURE';
    protected $primaryKey = 'FAT_ID';
    ```
2. `app/Http/Resources/FatureResource.php`
3. `app/Http/Controllers/Student/FatureController.php` — vetëm `index()`
4. Route nën `auth:sanctum` + `role:student`

**Acceptance:**

- Faturat janë të student-it aktual — asnjëherë të tjerëve
- Status kthehet si string siç është në DB: `E paguar` / `E papaguar` / `E vonuar`

---

## R3 — Pedagog: Grades for a Section

**Endpoint:** `GET /api/v1/pedagog/sections/{sectionId}/grades`

Kthen notat e studentëve për një seksion të caktuar, vetëm nëse pedagogi i kyçur e jep atë seksion (`PED_ID` match).

**Response shape:**

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

**Tabela te lidhura:** `SEKSION` (verify PED_ID) → `PROVIM` → `NOTA` → `STUDENT`

**Files to create:**

1. `app/Http/Controllers/Pedagog/SectionGradeController.php`
    - Verify `$section->PED_ID === auth()->user()->pedagog->PED_ID` — kthe 403 nëse nuk përputhet
2. Route nën `auth:sanctum` + `role:pedagog`

> Ripërdor `GradeResource` nga R1 — shtoji key-in `student` për këtë endpoint.

**Acceptance:**

- Pedagog sheh notat vetëm për seksionet e tij — 403 për seksionet e të tjerëve
- Student token → 403

---

## Rregulla për të tre detyrat

- Studenti/pedagogi sheh **vetëm të dhënat e tij** — ky është kontrolli më i rëndësishëm
- Asnjë `$request->validate()` brenda kontrollerave — vetëm `FormRequest`
- Output vetëm nëpër Resource klasa
- `make fix` + `make analyse` para çdo commit-i
