# 15 — Create Admin Invoice Controller (with full validation)

> **Backlog ref:** nouncheck — create FatureController for admin invoice management
> **Priority:** P1 — depends on task 12
> **Effort:** ~3h
> **Stack:** Laravel 11, ValidationService (from task 12)
> **Branch:** `<yourname>/admin-fature-controller` (example: `ornela/admin-fature-controller`)
> **Before you start:** Task 12 must be merged. Verify `ValidationService` is available. Read the Fature model structure carefully.

---

## Goal

Create a new `Admin\FatureController` to allow admins to create and manage student invoices with **full validation**:

- `POST /admin/invoices` creates a new invoice with validation:
  - Student exists (STU_ID)
  - Amount > 0 (FAT_SHUMA)
  - Status is one of: 'E papaguar', 'E paguar', 'E vonuar'
  - Date is valid (FAT_DAT_LESHIM)
- `PUT /admin/invoices/{id}` updates an invoice with the same validations (for fields being updated)
- `GET /admin/invoices` lists all invoices
- `GET /admin/invoices/{id}` shows a single invoice
- `DELETE /admin/invoices/{id}` deletes an invoice

---

## Workflow

1. `git checkout main && git pull` (pull task 12)
2. `git checkout -b <yourname>/admin-fature-controller`
3. Create `Admin\FatureController` with all CRUD methods
4. Add validation methods to `ValidationService` (validateStudentExists)
5. Wire routes into `api.php`
6. Test manually with curl or Postman
7. `make fix`, `make ci` before pushing
8. Open PR against `main`

---

## Step 1 — Add validateStudentExists to ValidationService

**File:** `app/Services/ValidationService.php`

Add this method to the existing ValidationService (from task 12):

```php
/**
 * Validate that a student exists by ID.
 *
 * @throws EntityNotFoundException if not found
 */
public function validateStudentExists(int $studentId): bool
{
    if (!Student::find($studentId)) {
        throw new EntityNotFoundException('Student', $studentId);
    }

    return true;
}
```

---

## Step 2 — Create Admin\FatureController

**File:** `app/Http/Controllers/Admin/FatureController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Fature;
use App\Services\ValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FatureController extends Controller
{
    use ApiResponse;

    // Valid invoice statuses
    private const VALID_STATUSES = ['E papaguar', 'E paguar', 'E vonuar'];

    public function __construct(private readonly ValidationService $validation) {}

    /**
     * List all invoices (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('perPage', 20), 100);

        $invoices = Fature::paginate($perPage);

        return $this->success($invoices, 'OK');
    }

    /**
     * Get a single invoice by ID.
     */
    public function show(int $id): JsonResponse
    {
        $invoice = Fature::findOrFail($id);

        return $this->success($invoice, 'OK');
    }

    /**
     * Create a new invoice.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'studentId'   => 'required|integer',
            'amount'      => 'required|numeric|min:0.01',
            'status'      => 'required|string|in:' . implode(',', self::VALID_STATUSES),
            'description' => 'nullable|string|max:200',
            'issueDate'   => 'required|date',
        ]);

        // Validate that the student exists
        $this->validation->validateStudentExists($data['studentId']);

        // Map request field names to model field names
        $invoice = Fature::create([
            'STU_ID'          => $data['studentId'],
            'FAT_SHUMA'       => $data['amount'],
            'FAT_STATUSI'     => $data['status'],
            'FAT_PERSHKRIM'   => $data['description'] ?? null,
            'FAT_DAT_LESHIM'  => $data['issueDate'],
        ]);

        return $this->success($invoice, 'Fatura u krijua me sukses.', 201);
    }

    /**
     * Update an existing invoice.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $invoice = Fature::findOrFail($id);

        $data = $request->validate([
            'studentId'   => 'sometimes|integer',
            'amount'      => 'sometimes|numeric|min:0.01',
            'status'      => 'sometimes|string|in:' . implode(',', self::VALID_STATUSES),
            'description' => 'nullable|string|max:200',
            'issueDate'   => 'sometimes|date',
        ]);

        // Validate that the student exists (only if studentId is being updated)
        if (isset($data['studentId'])) {
            $this->validation->validateStudentExists($data['studentId']);
        }

        // Map and update
        $updateData = [];
        if (isset($data['studentId'])) {
            $updateData['STU_ID'] = $data['studentId'];
        }
        if (isset($data['amount'])) {
            $updateData['FAT_SHUMA'] = $data['amount'];
        }
        if (isset($data['status'])) {
            $updateData['FAT_STATUSI'] = $data['status'];
        }
        if (isset($data['description'])) {
            $updateData['FAT_PERSHKRIM'] = $data['description'];
        }
        if (isset($data['issueDate'])) {
            $updateData['FAT_DAT_LESHIM'] = $data['issueDate'];
        }

        $invoice->update($updateData);

        return $this->success($invoice, 'Fatura u përditësua me sukses.');
    }

    /**
     * Delete an invoice.
     */
    public function destroy(int $id): JsonResponse
    {
        $invoice = Fature::findOrFail($id);
        $invoice->delete();

        return $this->success(null, 'Fatura u fshi me sukses.');
    }
}
```

---

## Step 3 — Add Routes to api.php

**File:** `routes/api.php`

In the admin middleware group (after the existing admin routes), add:

```php
// Invoices (admin write)
Route::post('/admin/invoices', [AdminFatureController::class, 'store']);
Route::get('/admin/invoices', [AdminFatureController::class, 'index']);
Route::get('/admin/invoices/{id}', [AdminFatureController::class, 'show']);
Route::put('/admin/invoices/{id}', [AdminFatureController::class, 'update']);
Route::delete('/admin/invoices/{id}', [AdminFatureController::class, 'destroy']);
```

At the top of the file, add the import:

```php
use App\Http\Controllers\Admin\FatureController as AdminFatureController;
```

---

## Manual smoke test

After `make dev`, test the controller:

```bash
# Get a valid student ID
STUDENT_ID=$(curl -s http://localhost:8000/api/v1/admin/students \
  -H "Authorization: Bearer <your-token>" | jq '.data[0].id')

# Test 1: Create an invoice with invalid studentId
curl -X POST http://localhost:8000/api/v1/admin/invoices \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-token>" \
  -d '{
    "studentId": 99999,
    "amount": 100.00,
    "status": "E papaguar",
    "issueDate": "2026-05-14"
  }'

# Expected: 404 EntityNotFoundException

# Test 2: Create an invoice with valid data
curl -X POST http://localhost:8000/api/v1/admin/invoices \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-token>" \
  -d "{
    \"studentId\": $STUDENT_ID,
    \"amount\": 150.50,
    \"status\": \"E papaguar\",
    \"description\": \"Tuition fee semester 1\",
    \"issueDate\": \"2026-05-14\"
  }"

# Expected: 201 success, returns invoice with all fields

# Test 3: Create an invoice with invalid amount (< 0)
curl -X POST http://localhost:8000/api/v1/admin/invoices \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-token>" \
  -d "{
    \"studentId\": $STUDENT_ID,
    \"amount\": -50.00,
    \"status\": \"E papaguar\",
    \"issueDate\": \"2026-05-14\"
  }"

# Expected: 422 validation error (amount must be > 0)

# Test 4: Create an invoice with invalid status
curl -X POST http://localhost:8000/api/v1/admin/invoices \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-token>" \
  -d "{
    \"studentId\": $STUDENT_ID,
    \"amount\": 100.00,
    \"status\": \"Invalid Status\",
    \"issueDate\": \"2026-05-14\"
  }"

# Expected: 422 validation error (status must be one of the valid values)

# Test 5: Get all invoices
curl -s http://localhost:8000/api/v1/admin/invoices \
  -H "Authorization: Bearer <your-token>" | jq '.data | length'

# Expected: number of invoices created

# Test 6: Update an invoice
INVOICE_ID=$(curl -s http://localhost:8000/api/v1/admin/invoices \
  -H "Authorization: Bearer <your-token>" | jq '.data[0].FAT_ID')

curl -X PUT http://localhost:8000/api/v1/admin/invoices/$INVOICE_ID \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-token>" \
  -d '{"status":"E paguar"}'

# Expected: 200 success, invoice now has status 'E paguar'

# Test 7: Delete an invoice
curl -X DELETE http://localhost:8000/api/v1/admin/invoices/$INVOICE_ID \
  -H "Authorization: Bearer <your-token>"

# Expected: 200 success, invoice is deleted
```

---

## Acceptance criteria

- [ ] `Admin\FatureController` exists with all 5 methods (index, show, store, update, destroy)
- [ ] `ValidationService` has `validateStudentExists()` method
- [ ] `POST /admin/invoices` validates:
  - Student exists
  - Amount > 0
  - Status is one of valid statuses
  - Date is valid
- [ ] `PUT /admin/invoices/{id}` validates only fields being updated
- [ ] Invalid student_id returns 404 with `EntityNotFoundException`
- [ ] Invalid amount (< 0) returns 422 validation error
- [ ] Invalid status returns 422 validation error
- [ ] Invalid date returns 422 validation error
- [ ] GET endpoints return proper paginated/single invoice data
- [ ] DELETE endpoint cascades properly
- [ ] Routes are wired in `api.php` with proper middleware (admin only)
- [ ] Manual smoke tests pass
- [ ] `make ci` passes
