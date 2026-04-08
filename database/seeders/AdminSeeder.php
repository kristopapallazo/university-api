<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * AdminSeeder
 * -----------
 * Bootstraps the very first admin user on a fresh install.
 *
 * WHY THIS EXISTS:
 *  - There is no public /register endpoint (by design — see docs/backend/phase-2-plan.md §1).
 *  - Students sign in via Google OAuth.
 *  - Pedagogs are created by an admin from the dashboard.
 *  - Admins are seeded manually here. Without this seeder, a fresh DB has zero users
 *    and nobody can call POST /api/v1/auth/login.
 *
 * HOW TO RUN (later — do NOT run yet):
 *   php artisan db:seed --class=AdminSeeder
 *   # or include it in DatabaseSeeder::run() and use `make fresh`
 *
 * SECURITY NOTES:
 *  - The default password below is a placeholder. CHANGE IT before running in any
 *    shared environment, and rotate it immediately after first login.
 *  - For production (Railway), set ADMIN_EMAIL and ADMIN_PASSWORD via env vars
 *    and read them with env('ADMIN_EMAIL') instead of hardcoding.
 *  - This seeder is idempotent: running it twice will not create duplicates
 *    thanks to updateOrCreate on the unique email.
 *
 * TODO before first run:
 *  - [ ] Decide the real admin email (probably your UAMD address)
 *  - [ ] Move the password to .env (ADMIN_PASSWORD) and reference it via env()
 *  - [ ] Register this seeder in DatabaseSeeder::run() if you want `make fresh` to include it
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@uamd.edu.al')],
            [
                'name' => 'Admin KP',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'change-me-immediately')),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
        );
    }
}
