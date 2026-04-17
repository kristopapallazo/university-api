# Testing Guide

## Test Credentials

Run the seeder to get these users in your local DB:

```bash
php artisan db:seed --class=TestSeeder
```

| Role    | Email                          | Password   | Login method       |
| ------- | ------------------------------ | ---------- | ------------------ |
| Admin   | test.admin@uamd.edu.al         | Testtest1! | Email + password   |
| Pedagog | test.pedagog@uamd.edu.al       | Testtest1! | Email + password   |
| Student | test.student@students.uamd.edu.al | —       | Google OAuth only  |

> Students can only log in via Google OAuth — there is no password login for the student role.
> The `test.student@...` email is a placeholder; see below to add your own.

---

## Testing Student OAuth Locally

Since the student login is Google OAuth only, you need a real Google account seeded in the `STUDENT` table.

**Add your own Google email as a test student:**

1. Open `database/seeders/TestSeeder.php`
2. Add an entry to the `$students` array in `seedStudent()`:
   ```php
   ['email' => 'your.email@gmail.com', 'em' => 'Your', 'mb' => 'Name', 'matrikull' => 'TEST-004'],
   ```
3. Re-run the seeder:
   ```bash
   php artisan db:seed --class=TestSeeder
   ```
4. Use "Hyr me Google (Studentët)" on the login page and sign in with that Google account.

> Do not commit personal emails to the seeder. Keep them in a local `.env`-style override or a git-ignored file.

---

## Google OAuth Setup (local)

Add these to `university-api/.env` (get the values from Railway → university-api → Variables):

```env
GOOGLE_OAUTH_ID=...
GOOGLE_OAUTH_SECRET=...
GOOGLE_REDIRECT_URI=http://localhost:8000/api/v1/auth/google/callback
```

Then restart the dev server:

```bash
php artisan serve
```

The Google Cloud OAuth app already has `http://localhost:8000` and the callback URI registered — no changes needed there.

---

## Running the API Locally

```bash
cp .env.example .env        # first time only
php artisan key:generate    # first time only
php artisan migrate --seed  # runs all migrations + seeders
php artisan serve           # starts at http://localhost:8000
```
