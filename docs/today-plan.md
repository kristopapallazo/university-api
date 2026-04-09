# Backend — Today's Tasks

> **Date:** 2026-04-09
> **Stack:** Laravel 11, Sanctum, MySQL (Railway)
> **Before you start:** read `docs/auth-plan.md` for full auth context.

---

## B1 — Role middleware (priority)

**Goal:** enforce role-based access control on protected routes.

**File to create:** `app/Http/Middleware/EnsureUserHasRole.php`

```php
// Accepts comma-separated roles: Route::middleware(['auth:sanctum', 'role:admin'])
// Returns 403 + Albanian message if user's role doesn't match
// Register alias 'role' in bootstrap/app.php
```

**Apply it immediately to:**

- `POST/PUT/DELETE /api/v1/faculties` → `role:admin` only
- `POST/PUT/DELETE /api/v1/departments` → `role:admin` only
- `GET` reads stay open to any authenticated user (`auth:sanctum` only)

**Acceptance:**

- Student token hitting `POST /api/v1/faculties` → 403
- Admin token hitting `POST /api/v1/faculties` → passes through
- Tests: feature test per role × per protected route asserting correct status code
- `make ci` green before PR

**PR checklist:** no `$request->validate()` inline, no `role` field accepted from any request body, Albanian error messages.

---

## B2 — Scribe docblock cleanup (small, unblocks FE)

**Goal:** the frontend store expects `access_token` but the API returns `token`. Make sure the real field names are clearly documented in Scribe so the FE team has no ambiguity.

**Files to update:**

- `app/Http/Controllers/AuthController.php` — verify `@response` blocks show `token`, not `access_token`
- `app/Http/Controllers/SocialAuthController.php` — same

**Run after:** `make docs` → open `http://localhost:8000/docs` → verify login and callback responses show the correct shape.

**Acceptance:** `/docs` shows `{ data: { user, token }, message, status }` for both login endpoints. No mention of `access_token` or `refresh_token`.

---

## Conventions reminder

- Input → `FormRequest` in `app/Http/Requests/`
- Output → `Resource` in `app/Http/Resources/`
- Response envelope → always `{ data, message, status }`
- Never accept `role` from client input
- All user-facing messages in Albanian
- `make fix` before committing, `make ci` before pushing
