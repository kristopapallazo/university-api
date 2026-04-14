# Authentication — Plan & Status

> **Scope:** end-to-end authentication for the UAMD portal across all three roles (`student`, `pedagog`, `admin`). This document is the single source of truth for what's done, what's broken, what's missing, and who does what next.
>
> **Last updated:** 2026-04-08
> **Owner:** Kristo

---

## 1. How auth works (the design)

The system uses **two parallel login paths** that converge on a single token type.

```
                    ┌──────────────────────────────────────┐
                    │         Laravel Sanctum tokens       │
                    │  (the only thing the API accepts on  │
                    │   protected routes — Bearer token)   │
                    └──────────────────────────────────────┘
                          ▲                          ▲
                          │                          │
            ┌─────────────┴───────────┐  ┌───────────┴────────────┐
            │  POST /auth/login       │  │  GET /auth/google/...  │
            │  email + password       │  │  Google OAuth (SSO)    │
            │                         │  │                        │
            │  • pedagog              │  │  • student only        │
            │  • admin                │  │  • @students.uamd.edu.al│
            └─────────────────────────┘  └────────────────────────┘
```

### Why two paths
- **Students** are issued institutional Google accounts (`@students.uamd.edu.al`) by the university. SSO removes password management entirely for the largest user group, and the email domain is a built-in identity check the university already enforces.
- **Pedagogs and admins** are smaller, controlled groups. They are not issued `@students.uamd.edu.al` addresses, so they can't use the Google flow. Email + password is created by an admin, not self-served.
- Both paths produce a **Sanctum personal access token** that the React SPA stores and sends as `Authorization: Bearer {token}` on every subsequent request. From the API's perspective after login, all roles look identical — the difference is only in the `role` column on the `users` table.

### The role column is sacred
- `users.role` is set **server-side only** at user creation. It is `student`, `pedagog`, or `admin`.
- **No endpoint accepts `role` as input.** Ever. Not on registration, not on profile updates. If a client could send `role`, anyone could become admin.
- Students get `role=student` automatically in the OAuth callback.
- Pedagogs are created by an admin via a (still-to-be-built) admin endpoint with `role=pedagog` hardcoded server-side.
- Admins are seeded manually via `database/seeders/AdminSeeder.php` — there is no admin self-registration.

### No public `/register` endpoint
This is intentional and must stay that way. The university controls who is a student (Google account provisioning), the admin controls who is a pedagog, and you control who is an admin (seeder). Adding a public registration endpoint would break the entire trust model.

---

## 2. What's done ✅

### 2.1 Email/password login (pedagog & admin)
- **Endpoint:** `POST /api/v1/auth/login`
- **Implementation:** [AuthController::login](../app/Http/Controllers/AuthController.php)
- **Validation:** [LoginRequest](../app/Http/Requests/Auth/LoginRequest.php)
- **Behaviour:**
  - Validates email + password via `Auth::attempt`
  - Rejects with 401 on bad credentials (Albanian message)
  - Rejects with 403 if a `student` row tries to use this endpoint (forces them to Google)
  - Returns `{ user, token }` on success
- **Rate limit:** 6 requests/minute per IP (configured in `routes/api.php`)
- **Tested in production:** ✅

### 2.2 Google OAuth — redirect
- **Endpoint:** `GET /api/v1/auth/google/redirect`
- **Implementation:** [SocialAuthController::redirect](../app/Http/Controllers/SocialAuthController.php)
- **Behaviour:**
  - Builds the Google consent URL with `hd=students.uamd.edu.al` hint (UI hint only — real check is server-side in the callback)
  - Uses `stateless()` because the SPA flow has no session cookies
  - Returns a 302 redirect to Google
- **Tested in production:** ✅

### 2.3 Google OAuth — callback
- **Endpoint:** `GET /api/v1/auth/google/callback`
- **Implementation:** [SocialAuthController::callback](../app/Http/Controllers/SocialAuthController.php)
- **Behaviour:**
  - Receives the `code` from Google, exchanges it for a user profile
  - **Server-side domain check:** rejects any email not ending in `@students.uamd.edu.al` (403)
  - `firstOrCreate` on email — new students are auto-provisioned with `role=student`, `provider=google`, `provider_id=<google sub>`, `password=null`
  - Returns `{ user, token }`
- **Tested in production:** ✅ Fixed 2026-04-09 — see section 4 for full fix history.

### 2.4 Authenticated session endpoints
- `GET /api/v1/auth/me` — returns the current user (any role) — ✅ working in prod
- `POST /api/v1/auth/logout` — revokes the current token only (not all tokens) — ✅ working in prod

### 2.5 Database & model
- `users` table has: `id`, `name`, `email`, `password (nullable)`, `role`, `provider`, `provider_id`, `avatar_url`, timestamps — ✅
- `personal_access_tokens` table from Sanctum — ✅
- Admin seeder reads `ADMIN_EMAIL` / `ADMIN_PASSWORD` from env — ✅

### 2.6 API conventions
- Uniform response envelope `{ data, message, status }` via `ApiResponse` trait — ✅
- All Albanian user-facing messages — ✅
- `UserResource` never leaks `password`, `provider_id`, or `remember_token` — ✅

---

## 3. What's NOT done ❌

### 3.1 Pedagog management endpoints (high priority)
Right now there is **no way to create a pedagog except by manual SQL**. Until this exists, the email/password login flow has no real users to authenticate.

Needed:
- `POST /api/v1/admin/pedagogs` — create pedagog (admin only)
- `GET /api/v1/admin/pedagogs` — list
- `PUT /api/v1/admin/pedagogs/{id}` — update (without password)
- `POST /api/v1/admin/pedagogs/{id}/reset-password` — admin-triggered password reset
- `DELETE /api/v1/admin/pedagogs/{id}` — soft delete

All of these must hardcode `role=pedagog` server-side and reject any `role` field in the request body.

### 3.2 Authorization middleware
We have **authentication** (who you are) but almost no **authorization** (what you can do). Right now any authenticated user can call any protected endpoint, regardless of role.

Needed:
- A `role` middleware: `Route::middleware(['auth:sanctum', 'role:admin'])->...`
- Apply it to admin-only routes (the pedagog management endpoints above, eventually grade entry endpoints, etc.)
- Apply `role:pedagog,admin` to grade-writing endpoints
- Apply `role:student` to student-self-service endpoints (own grades, own schedule)

Without this, a logged-in student could `POST /faculties` and create a faculty. We're protected by obscurity right now, not policy.

### 3.3 Password reset for pedagog/admin
- No "forgot password" flow exists.
- If a pedagog forgets their password, the only recovery today is admin SQL.
- Needed: `POST /auth/password/forgot` (sends email with reset token), `POST /auth/password/reset` (consumes token, sets new password). Standard Laravel flow, but UI strings must be Albanian.

### 3.4 Token expiration & refresh
- Sanctum is configured with `SANCTUM_TOKEN_EXPIRATION=1440` (24h) but **we never tested what happens when a token expires**. The SPA probably gets a 401 and dumps the user — fine for now, but worth verifying.
- No refresh token mechanism. Users will need to log in again every 24h. Acceptable for v1.

### 3.5 "Logout from all devices"
- `POST /auth/logout` only deletes the current token. There is no `POST /auth/logout-all` that revokes every token for the user.
- Not critical for v1, but should exist before production for any sensitive role.

### 3.6 Login attempt logging / lockout
- We rate-limit by IP (6/min) but we don't:
  - Log failed attempts to a table
  - Lock an account after N failed attempts
  - Notify the user/admin of suspicious activity
- For a university portal handling grades and invoices, basic lockout is expected.

### 3.7 Email verification for pedagogs
- When admin creates a pedagog, today we just store the password directly. There is no "verify your email" or "set your initial password via emailed link" flow.
- This is a v2 nice-to-have. For now, admin sets a temporary password and shares it out-of-band.

### 3.8 Frontend integration (separate repo)
Status of `university-app` is outside this doc, but the backend assumes the SPA will:
- Open `/auth/google/redirect` in a **new browser tab/window** (not via fetch — Google won't let you embed its consent screen)
- Receive the callback at the API host, then redirect back to the SPA with the token in a query param OR in postMessage
- **Currently undecided:** how exactly does the token get from API back to SPA after Google redirect? This is a design decision that needs to be made before students can actually log in. See section 5, risk #2.

---

## 4. The current production bug

**Symptom:** `/api/v1/auth/google/callback` is the only endpoint failing in production. All other 7 endpoints work.

**Status:** fix applied — code renamed to `GOOGLE_OAUTH_ID` / `GOOGLE_OAUTH_SECRET`, Railpack build issue resolved.

1. ~~Initial cause: `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` were empty in Railway env vars.~~
2. ~~Railpack build failure — auto-detected `GOOGLE_CLIENT_*` as build-time secrets.~~
3. ~~Workaround: renamed to `GOOGLE_OAUTH_ID` / `GOOGLE_OAUTH_SECRET`. `config/services.php` updated.~~ ✅
4. **Still pending:** verify `https://university-api-production.up.railway.app/api/v1/auth/google/callback` is in Google Cloud Console → Authorized Redirect URIs.
5. **Still pending:** OAuth consent screen publishing status — if set to "Testing", only manually added test users can log in.

---

## 5. Risks

### Risk 1 — `users.role` injection (CRITICAL)
**The fear:** any new endpoint that creates or updates a user accepts `role` in the request body, allowing privilege escalation.
**Mitigation:**
- All `FormRequest` classes that touch users must **omit** `role` from `rules()`.
- All controllers that create users must set `role` to a hardcoded literal, never from `$request->input('role')`.
- Code review checklist item: "Does this PR touch the `users` table? Does it accept `role` from input?"

### Risk 2 — Token delivery from OAuth callback to SPA (HIGH)
**The fear:** the simplest implementation is to redirect from `/auth/google/callback` to the SPA with `?token=xyz` in the query string. This puts the token in browser history, server logs, referrer headers, and any analytics tools the SPA loads.
**Options:**
- **(a) Query string + immediate replaceState** — easy, slightly leaky. Acceptable for v1.
- **(b) Short-lived one-time code** — callback returns a `code`, SPA exchanges it for the real token via `POST /auth/exchange`. Two round trips, much safer. The right answer for v2.
- **(c) Cookie-based session** — abandons Sanctum tokens entirely for the student flow. Big architectural change.

**Decision needed before students can log in for real.** Currently undecided.

### Risk 3 — `hd` parameter is not enough (MEDIUM)
**The fear:** the `hd=students.uamd.edu.al` hint passed to Google is **only a UI filter**. A user can manually edit the URL or use a different account picker and bypass it. We protect against this with the server-side `str_ends_with` check in the callback, but this check must never be removed or weakened.
**Mitigation:** add a unit test that verifies the callback returns 403 for any non-`@students.uamd.edu.al` email. If the test ever turns red, prod is one merge away from letting outsiders in.

### Risk 4 — Sanctum token leakage in logs (MEDIUM)
**The fear:** Laravel's default request logging may log Authorization headers. If `LOG_LEVEL=debug` is ever set in prod, tokens end up in log files.
**Mitigation:** confirm `LOG_LEVEL=error` in `.env.production` (it currently is, ✅). Add a logging middleware that strips `Authorization` headers before logging if we ever raise the level.

### Risk 5 — No admin recovery path (MEDIUM)
**The fear:** if the seeded admin account is locked out or its password is forgotten, there is no recovery — no second admin, no reset flow.
**Mitigation:**
- Seed at least **two** admin accounts in production
- Document the SQL recovery procedure in `docs/backend/auth-recovery.md` (to be written)
- Long term: build the password reset flow (section 3.3)

### Risk 6 — Google OAuth client secret rotation (LOW)
**The fear:** if the Google client secret leaks (e.g., committed to git, screenshotted), every student who has ever logged in can be impersonated until rotation.
**Mitigation:**
- Never commit `.env.production`. Currently gitignored, ✅.
- If a leak happens: rotate in Google Cloud Console, update Railway env, redeploy, force-logout all students by truncating `personal_access_tokens` for `role=student` users.

### Risk 7 — Junior contributors and the production DB
**The fear:** juniors point at the prod DB by default (per the new onboarding model). One bad query in tinker, one accidental `User::truncate()`, and prod is gone.
**Mitigation:**
- Makefile guards on `migrate`/`fresh` (✅ done)
- Strict rule: juniors don't touch the auth tables
- **Not yet done:** Railway read-mostly DB user (Layer 1). Reconsider this if the team grows.

---

## 6. Work assignments for the team

These are sized to be 1-2 day tasks for a junior with code review, or half-day for the lead.

### Junior 1 — Pedagog management (Section 3.1)
**Goal:** Build the admin endpoints to manage pedagog accounts.
**Why it matters:** Without this, no one can actually log in via the email/password flow except the seeded admin.
**Files to create:**
- `app/Http/Controllers/Admin/PedagogController.php`
- `app/Http/Requests/Admin/StorePedagogRequest.php` — must NOT include `role` in rules
- `app/Http/Requests/Admin/UpdatePedagogRequest.php`
- `app/Http/Resources/PedagogResource.php` (or reuse `UserResource` filtered)
- Routes in `routes/api.php` under a new `auth:sanctum + role:admin` group
**Acceptance:**
- Admin can create a pedagog with name + email + initial password
- `role` is hardcoded `'pedagog'` in the controller, never from request
- Returns the new pedagog (without password) wrapped in the standard response envelope
- Scribe docs regenerated and visible at `/docs`
**PR checklist:** lint + analyse + test all green; no `$request->validate()` inline; no `role` field accepted from client.

### Junior 2 — Role middleware (Section 3.2)
**Goal:** Build a `role` middleware and apply it to existing routes.
**Why it matters:** Right now any logged-in user can hit any protected endpoint. This is the foundation for everything role-based.
**Files to create:**
- `app/Http/Middleware/EnsureUserHasRole.php` — accepts comma-separated roles, returns 403 with Albanian message if user doesn't match
- Register the alias in `bootstrap/app.php`
- Apply to existing routes: faculties/departments writes (admin only), reads (any authenticated)
**Acceptance:**
- A `student` token cannot POST to `/faculties` (403)
- A `pedagog` token cannot DELETE a faculty
- An `admin` token can do anything
- Tests: at least one feature test per role × per protected endpoint, asserting the right status code

### Junior 3 (or lead) — Frontend OAuth token handoff (Risk 2)
**Goal:** Decide and document how the Google callback gets the token to the SPA.
**Why it matters:** Until this is decided, students literally cannot log in to the real frontend.
**Deliverable:** a short ADR (`docs/backend/adr-001-oauth-token-handoff.md`) recommending one of options (a)/(b)/(c) with reasoning, then implement option (a) for v1 with a clear TODO comment pointing at the ADR.
**Acceptance:**
- The callback either returns JSON (current) or redirects to a configurable SPA URL with the token. This is configurable via env (`FRONTEND_AUTH_CALLBACK_URL`).
- A manual end-to-end test from `university-app` works: click "Login with Google", land back in the SPA, see the user's name in the header.

### Lead (Kristo) — Google Console config + production fix (ONLY YOU — you own the account)

Nobody else can do these steps. Do them **before** assigning Junior 3 the OAuth handoff task, because their work depends on a working callback in prod.

#### Google Cloud Console checklist

1. **APIs & Services → Credentials → your OAuth 2.0 Client ID → Edit**
   - [ ] Application type is **"Web application"**
   - [ ] **Authorized redirect URIs** contain:
     - `https://university-api-production.up.railway.app/api/v1/auth/google/callback` (prod)
     - `http://localhost:8000/api/v1/auth/google/callback` (local dev — optional, add when needed)
   - [ ] No trailing slashes, no extra spaces, exact match

2. **APIs & Services → OAuth consent screen**
   - [ ] **Publishing status** — if it says "Testing", only manually added test users can log in. Everyone else sees "This app is blocked".
     - For now: add your own `@students.uamd.edu.al` test account so you can verify the flow works
     - Before real students use it: click **"Publish App"** (may trigger a Google verification review — takes 1-5 days for basic scopes like email/profile, usually auto-approved)
   - [ ] **Authorized domains** include `university-api-production.up.railway.app` (and eventually the frontend domain)
   - [ ] **Scopes** — only `openid`, `email`, `profile` (non-sensitive, no manual review needed)
   - [ ] **App name / logo / support email** — fill in something reasonable ("UAMD Portal"), Google shows this on the consent screen

3. **Railway dashboard**
   - [ ] Delete old `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` variables
   - [ ] Add `GOOGLE_OAUTH_ID` = your client ID
   - [ ] Add `GOOGLE_OAUTH_SECRET` = your client secret
   - [ ] Verify `GOOGLE_REDIRECT_URI` = `https://university-api-production.up.railway.app/api/v1/auth/google/callback`

4. **Code + deploy**
   - [ ] Push the `GOOGLE_OAUTH_*` rename commit (config/services.php + .env.example)
   - [ ] Verify Railway build succeeds (no more Railpack "secret not found" error)
   - [ ] Test: open `https://university-api-production.up.railway.app/api/v1/auth/google/redirect` in browser → Google consent → callback returns `{ user, token }`

5. **After it works**
   - [ ] Seed a second admin account in production (recovery path — Risk 5)
   - [ ] Write `docs/backend/auth-recovery.md` (one page: how to recover admin access if locked out)

#### Handoff to Junior 3
Once step 4 is green (callback returns a valid token in prod), tell Junior 3 to start the OAuth-to-SPA handoff task. Their work is backend+frontend — they need a working callback to test against. Don't assign it until it works.

### Anyone — Test coverage gap (Risk 3)
**Goal:** Add a feature test that asserts the callback rejects non-`@students.uamd.edu.al` emails.
**Why:** This is the single most important auth invariant. It must be untouchable.
**Effort:** 30 minutes. Small enough to slot between other tasks.

---

## 7. Out of scope (for now)

These are real concerns but explicitly deferred:
- Multi-factor authentication (TOTP, SMS, email codes)
- Social login providers other than Google (Microsoft, Apple, etc.)
- Single sign-on with the existing UAMD SSO if one exists
- Audit log of every login attempt
- GDPR-compliant data deletion flow ("delete my account")
- Login throttling based on user identity rather than IP
- Refresh tokens

Revisit when v1 is in real use and feedback comes in.

---

## 8. Open questions

1. **Does Google Workspace let us restrict the OAuth client to only `@students.uamd.edu.al` users at the Google level**, so the server check is defense-in-depth instead of the only check? Worth a 30-min investigation.
2. **Is there an existing UAMD identity provider** (LDAP, Active Directory, in-house SSO) we should integrate with instead of standalone email/password for pedagogs and admins? If yes, the email/password path becomes obsolete.
3. **Who owns the Google Cloud Console project?** If it's a personal account, it needs to be migrated to a UAMD-owned account before this goes live for real students.
4. **What is the source of truth for "who is a current student"?** If a student graduates or is expelled, how does their access get revoked? Currently nothing — once they're in the `users` table, they can log in forever. This is a v2 problem but worth flagging now.

---

## Appendix — Endpoint summary

| Method | Path | Auth | Roles | Status |
|---|---|---|---|---|
| POST | `/api/v1/auth/login` | Public | pedagog, admin | ✅ working |
| GET | `/api/v1/auth/google/redirect` | Public | student (intended) | ✅ working |
| GET | `/api/v1/auth/google/callback` | Public | student (intended) | ❌ broken in prod |
| GET | `/api/v1/auth/me` | Bearer | any | ✅ working |
| POST | `/api/v1/auth/logout` | Bearer | any | ✅ working |
| POST | `/api/v1/admin/pedagogs` | Bearer | admin | ❌ not built |
| ... | (more admin endpoints) | Bearer | admin | ❌ not built |
| POST | `/api/v1/auth/password/forgot` | Public | pedagog, admin | ❌ not built |
| POST | `/api/v1/auth/password/reset` | Public | pedagog, admin | ❌ not built |
| POST | `/api/v1/auth/logout-all` | Bearer | any | ❌ not built |
