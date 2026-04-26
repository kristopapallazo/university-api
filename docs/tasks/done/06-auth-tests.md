# 06 — Auth Controller Tests

> **Priority:** P2 — quality, no blockers
> **Effort:** ~1.5h
> **Stack:** Laravel 12, Sanctum, PHPUnit
> **Branch:** `<yourname>/auth-tests` (example: `serdar/auth-tests`)
> **Before you start:** read `docs/onboarding.md` and `docs/auth-plan.md`. No new features — testing only.

---

## Goal

The login, logout, and `/auth/me` endpoints have zero automated tests. This task writes full coverage so any regression is caught immediately by CI.

**One new file only:** `tests/Feature/AuthControllerTest.php`

No controllers, no models, no routes modified.

---

## Workflow (mandatory)

1. Pull latest `main`: `git checkout main && git pull`
2. Create branch: `<yourname>/auth-tests`
3. Create `tests/Feature/AuthControllerTest.php`
4. Run `php artisan test --filter AuthControllerTest` as you go to verify each test
5. Run `make ci` before pushing — all 8 tests must pass
6. Open PR against `main`, request review from `kristopapallazo`
7. Link this doc in the PR description

---

## Setup inside the test class

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $pedagog;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin   = User::factory()->create(['role' => 'admin',   'password' => bcrypt('password')]);
        $this->pedagog = User::factory()->create(['role' => 'pedagog', 'password' => bcrypt('password')]);
        $this->student = User::factory()->create(['role' => 'student', 'password' => null]);
    }
}
```

---

## Tests to implement

### T1 — Valid admin login → 200 + token
```php
public function test_admin_can_login_with_valid_credentials(): void
{
    $res = $this->postJson('/api/v1/auth/login', [
        'email'    => $this->admin->email,
        'password' => 'password',
    ]);

    $res->assertStatus(200)
        ->assertJsonStructure(['data' => ['token', 'user']]);
}
```

### T2 — Wrong password → 401
```php
public function test_login_fails_with_wrong_password(): void
{
    $res = $this->postJson('/api/v1/auth/login', [
        'email'    => $this->admin->email,
        'password' => 'wrong-password',
    ]);

    $res->assertStatus(401);
}
```

### T3 — Student blocked from email login → 403
```php
public function test_student_cannot_login_via_email(): void
{
    $res = $this->postJson('/api/v1/auth/login', [
        'email'    => $this->student->email,
        'password' => 'password',
    ]);

    $res->assertStatus(403);
}
```

### T4 — `/auth/me` with valid token → 200 + user data
```php
public function test_me_returns_authenticated_user(): void
{
    $res = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/auth/me');

    $res->assertStatus(200)
        ->assertJsonPath('data.email', $this->admin->email)
        ->assertJsonPath('data.role', 'admin');
}
```

### T5 — `/auth/me` without token → 401
```php
public function test_me_requires_authentication(): void
{
    $this->getJson('/api/v1/auth/me')->assertStatus(401);
}
```

### T6 — Logout revokes token → 200
```php
public function test_logout_succeeds(): void
{
    $res = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/v1/auth/logout');

    $res->assertStatus(200);
}
```

### T7 — After logout, `/auth/me` returns 401
```php
public function test_me_returns_401_after_logout(): void
{
    // Login to get a real token
    $loginRes = $this->postJson('/api/v1/auth/login', [
        'email'    => $this->admin->email,
        'password' => 'password',
    ]);
    $token = $loginRes->json('data.token');

    // Logout
    $this->withToken($token)->postJson('/api/v1/auth/logout');

    // /me should now be 401
    $this->withToken($token)->getJson('/api/v1/auth/me')->assertStatus(401);
}
```

### T8 — Pedagog can login → 200
```php
public function test_pedagog_can_login_with_valid_credentials(): void
{
    $res = $this->postJson('/api/v1/auth/login', [
        'email'    => $this->pedagog->email,
        'password' => 'password',
    ]);

    $res->assertStatus(200)
        ->assertJsonPath('data.user.role', 'pedagog');
}
```

---

## Acceptance criteria

- [ ] All 8 tests pass: `php artisan test --filter AuthControllerTest`
- [ ] No existing tests broken: `make test`
- [ ] `make ci` passes (lint + analyse + test)
