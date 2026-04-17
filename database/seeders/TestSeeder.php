<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Pedagog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * TestSeeder
 * ----------
 * Creates one static test user per role so every developer and environment
 * has known, predictable credentials from day one.
 *
 * Credentials (also in docs/testing.md):
 *
 *  Role     | Email                          | Password     | Login method
 *  ---------|--------------------------------|--------------|----------------
 *  admin    | test.admin@uamd.edu.al         | Testtest1!   | email + password
 *  pedagog  | test.pedagog@uamd.edu.al       | Testtest1!   | email + password
 *  student  | test.student@students.uamd.edu.al      | (OAuth) | Google OAuth ONLY
 *  student  | kristopapallazo@students.uamd.edu.al   | (OAuth) | Google OAuth ONLY
 *  student  | papallazo.dev@gmail.com                | (OAuth) | Google OAuth ONLY (dev override)
 *
 * NOTE: The student rows exist in the STUDENT table so Google OAuth can resolve
 * the role. Students cannot log in with a password — Sanctum only, via OAuth.
 *
 * Idempotent: safe to run multiple times (uses updateOrCreate on unique keys).
 */
class TestSeeder extends Seeder
{
    private const PASSWORD = 'Testtest1!';

    public function run(): void
    {
        $this->seedAdmin();
        $this->seedPedagog();
        $this->seedStudent();
    }

    private function seedAdmin(): void
    {
        User::updateOrCreate(
            ['email' => 'test.admin@uamd.edu.al'],
            [
                'name' => 'Test Admin',
                'password' => Hash::make(self::PASSWORD),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }

    private function seedPedagog(): void
    {
        $email = 'test.pedagog@uamd.edu.al';

        // Ensure a matching PEDAGOG row exists so Auth::user()->pedagog works
        $depId = Department::where('DEP_EM', 'Departamenti i Informatikës')->value('DEP_ID');

        Pedagog::updateOrCreate(
            ['PED_EMAIL' => $email],
            [
                'PED_EM' => 'Test',
                'PED_MB' => 'Pedagog',
                'PED_GJINI' => 'M',
                'PED_TITULLI' => 'Dr.',
                'PED_DT_PUNESIM' => '2024-01-01',
                'DEP_ID' => $depId,
            ]
        );

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Test Pedagog',
                'password' => Hash::make(self::PASSWORD),
                'role' => 'pedagog',
                'email_verified_at' => now(),
            ]
        );
    }

    private function seedStudent(): void
    {
        // Each entry lets Google OAuth resolveRole() identify the email as a student.
        // No users row is created here — auto-created on first OAuth login.
        $students = [
            ['email' => 'test.student@students.uamd.edu.al', 'em' => 'Test',  'mb' => 'Student',    'matrikull' => 'TEST-001'],
            ['email' => 'kristopapallazo@students.uamd.edu.al', 'em' => 'Kristo', 'mb' => 'Papallazo', 'matrikull' => 'TEST-002'],
            ['email' => 'papallazo.dev@gmail.com',           'em' => 'Kristo', 'mb' => 'Dev',        'matrikull' => 'TEST-003'],
        ];

        foreach ($students as $s) {
            Student::updateOrCreate(
                ['STU_EMAIL' => $s['email']],
                [
                    'STU_EM' => $s['em'],
                    'STU_MB' => $s['mb'],
                    'STU_GJINI' => 'M',
                    'STU_DTL' => '2000-01-01',
                    'STU_NR_MATRIKULL' => $s['matrikull'],
                    'STU_STATUS' => 'Aktiv',
                ]
            );
        }
    }
}
