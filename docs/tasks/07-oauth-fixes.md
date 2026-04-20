# 07 — OAuth Security # 06 — OAuth Security & Callback Fix Callback Fix

> **Priority:** P2 — security + correctness
> **Effort:** ~1h
> **Stack:** Laravel 12, Sanctum, Socialite
> **Branch:** `<yourname>/oauth-fixes` (example: `evelyn/oauth-fixes`)
> **Before you start:** read `docs/auth-plan.md` — specifically the Google OAuth section.
> **Note:** The callback fix (O2) requires a frontend route `/auth/callback` to be ready. Coordinate with Kristo before merging O2.

---

## Goal

Two small but important fixes to the Google OAuth flow:

1. **Rate limit** the OAuth routes — currently unprotected, can be spammed
2. **Fix the callback response** — currently returns JSON to a browser redirect; the SPA never receives the token

---

## Workflow (mandatory)

1. Pull latest `main`: `git checkout main && git pull`
2. Create branch: `<yourname>/oauth-fixes`
3. Implement O1 first (safe, standalone), then O2
4. One commit per fix (`O1: throttle oauth routes`, `O2: fix callback redirect`)
5. Run `make fix` before each commit, `make ci` before pushing
6. Open PR against `main`, request review from `kristopapallazo`
7. Link this doc in the PR description

---

## O1 — Throttle Google OAuth routes

**File to edit:** `routes/api.php`

Find the two Google OAuth routes and add `throttle:10,1` middleware (10 requests per minute per IP):

```php
// Before
Route::get('/auth/google/redirect', [SocialAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [SocialAuthController::class, 'callback']);

// After
Route::middleware('throttle:10,1')->group(function () {
    Route::get('/auth/google/redirect', [SocialAuthController::class, 'redirect']);
    Route::get('/auth/google/callback', [SocialAuthController::class, 'callback']);
});
```

No other changes needed for O1.

---

## O2 — Fix callback response for SPA

**The problem:** Google redirects the browser to `/auth/google/callback`. The API currently returns a JSON response — but the browser just displays raw JSON. The React SPA never receives the token.

**The fix:** instead of returning JSON, redirect the browser back to the frontend with the token in the URL.

**File to edit:** `app/Http/Controllers/SocialAuthController.php`

Find the end of the `callback()` method where it currently returns JSON:

```php
// Before — returns JSON (browser can't use this)
return response()->json([
    'data'    => ['user' => new UserResource($user), 'token' => $token],
    'message' => 'OK',
    'status'  => 200,
]);
```

Replace with a redirect to the frontend:

```php
// After — redirects browser to SPA with token
$frontendUrl = config('app.frontend_url');

return redirect("{$frontendUrl}/auth/callback?token={$token}");
```

**Also add to `config/app.php`:**
```php
'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),
```

**Also add to `.env.example`:**
```env
FRONTEND_URL=http://localhost:5173
```

**On Railway (production):** set `FRONTEND_URL` to the live Vercel URL.

> The frontend needs a `/auth/callback` route that reads `?token` from the URL, saves it to the auth store, then redirects to `/student`. Kristo handles the FE side — coordinate before merging this.

---

## Acceptance criteria

- [ ] `GET /api/v1/auth/google/redirect` and `/callback` are throttled at 10 req/min
- [ ] OAuth callback redirects to `{FRONTEND_URL}/auth/callback?token=...` instead of returning JSON
- [ ] `FRONTEND_URL` is in `.env.example` and `config/app.php`
- [ ] `make ci` passes
