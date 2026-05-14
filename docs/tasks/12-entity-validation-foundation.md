# 12 — Entity Validation Foundation

> **Backlog ref:** nouncheck — entity validation infrastructure
> **Priority:** P1 — foundation for tasks 13–15
> **Effort:** ~2h
> **Stack:** Laravel 11, custom exceptions
> **Branch:** `<yourname>/entity-validation` (example: `ornela/entity-validation`)
> **Before you start:** Read the entire task spec carefully. This is foundational; get it right.

---

## Goal

Build a reusable validation service and exception handling infrastructure so that **subsequent tasks** (13–15) can validate entity existence across multiple endpoints with a **single, consistent pattern**.

When this task is done:

- A `ValidationService` exists with methods to check if entities (students, faculty, programs, etc.) exist in the DB
- An `EntityNotFoundException` exception is created with a consistent error response format
- Laravel's exception handler catches `EntityNotFoundException` and returns a proper JSON error response
- Other controllers can import and use this service immediately (tested in the next task)

**Do not** apply validation to any endpoint yet. That's tasks 13–15.

---

## Workflow

1. `git checkout main && git pull` (ensure main is up-to-date)
2. `git checkout -b <yourname>/entity-validation`
3. Build in order: exception → service → exception handler integration
4. Commit per logical step (`entity-exception`, `validation-service`, `exception-handler-update`)
5. `make fix` before each commit, `make ci` before pushing
6. Open PR against `main`, request review from `kristopapallazo`
7. Link this doc in the PR description

---

## Step 1 — Create EntityNotFoundException

**File:** `app/Exceptions/EntityNotFoundException.php`

```php
<?php

namespace App\Exceptions;

use Exception;

class EntityNotFoundException extends Exception
{
    public function __construct(
        public readonly string $entityType,
        public readonly string|int $identifier,
    ) {
        parent::__construct(
            message: "Entiteti '{$entityType}' me identifikues '{$identifier}' nuk u gjet.",
        );
    }

    /**
     * Return a JSON response for this exception.
     * Used by the exception handler.
     */
    public function render()
    {
        return response()->json(
            data: [
                'message' => $this->message,
                'status'  => 404,
                'entity'  => $this->entityType,
            ],
            status: 404,
        );
    }
}
```

---

## Step 2 — Create ValidationService

**File:** `app/Services/ValidationService.php`

This service provides methods to validate entity existence. Each method returns `true` if the entity exists, or throws `EntityNotFoundException` if not.

```php
<?php

namespace App\Services;

use App\Exceptions\EntityNotFoundException;
use App\Models\Faculty;
use App\Models\Pedagog;
use App\Models\Program;
use App\Models\Lenda;
use App\Models\Student;

class ValidationService
{
    /**
     * Validate that a faculty exists by ID.
     *
     * @throws EntityNotFoundException if not found
     */
    public function validateFacultyExists(int $facultyId): bool
    {
        if (!Faculty::find($facultyId)) {
            throw new EntityNotFoundException('Fakultet', $facultyId);
        }

        return true;
    }

    /**
     * Validate that a program (programi studim) exists by ID.
     *
     * @throws EntityNotFoundException if not found
     */
    public function validateProgramExists(int $programId): bool
    {
        if (!Program::find($programId)) {
            throw new EntityNotFoundException('Program Studim', $programId);
        }

        return true;
    }

    /**
     * Validate that a course (lende) exists by ID.
     *
     * @throws EntityNotFoundException if not found
     */
    public function validateCourseExists(int $courseId): bool
    {
        if (!Lenda::find($courseId)) {
            throw new EntityNotFoundException('Lende', $courseId);
        }

        return true;
    }

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

    /**
     * Validate that a pedagog (lecturer) exists by ID.
     *
     * @throws EntityNotFoundException if not found
     */
    public function validatePedagogExists(int $pedagogId): bool
    {
        if (!Pedagog::find($pedagogId)) {
            throw new EntityNotFoundException('Pedagog', $pedagogId);
        }

        return true;
    }
}
```

---

## Step 3 — Update Exception Handler

Open `app/Exceptions/Handler.php` and verify that `EntityNotFoundException` is registered for automatic JSON rendering.

Laravel should already auto-render exceptions to JSON if they define a `render()` method, but confirm the handler doesn't suppress it. Your `Handler.php` should look like this at the top:

```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Laravel will automatically call render() on exceptions that define it
        // No additional configuration needed for EntityNotFoundException
    }
}
```

If the handler has any custom exception handling that might interfere, remove it. The key is: **do not catch or suppress EntityNotFoundException**.

---

## Step 4 — Wire ValidationService into AppServiceProvider (Optional but Recommended)

To make injection easier, you can bind it in the container.

Open `app/Providers/AppServiceProvider.php`, add inside `register()`:

```php
use App\Services\ValidationService;

$this->app->singleton(ValidationService::class, function () {
    return new ValidationService();
});
```

This allows controllers to inject it:

```php
public function __construct(private readonly ValidationService $validation) {}
```

---

## Manual smoke test

After `make dev`, test the exception manually:

```bash
# Start a fresh Laravel shell (so you can test the service)
php artisan tinker

# Test that the service throws correctly
$service = app(\App\Services\ValidationService::class);
$service->validateFacultyExists(99999); # Should throw EntityNotFoundException
```

Expected output: An `EntityNotFoundException` with message in Albanian mentioning the faculty was not found.

---

## Acceptance criteria

- [ ] `EntityNotFoundException` exists in `app/Exceptions/`
- [ ] Exception has `entityType` and `identifier` properties
- [ ] Exception's `render()` method returns JSON with `message`, `status` (404), and `entity` fields
- [ ] `ValidationService` exists in `app/Services/`
- [ ] Service has methods: `validateFacultyExists()`, `validateProgramExists()`, `validateCourseExists()`, `validateStudentExists()`, `validatePedagogExists()`
- [ ] Each method throws `EntityNotFoundException` if entity not found
- [ ] Service is bound in `AppServiceProvider` (optional but recommended)
- [ ] Manual test in tinker confirms exception is thrown and has correct message
- [ ] `make ci` passes
