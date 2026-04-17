<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // ── Core auth ──────────────────────────
            AdminSeeder::class,
            TestSeeder::class,           // static test users (admin/pedagog/student)

            // ── Academic structure ─────────────────
            FacultySeeder::class,
            DepartmentSeeder::class,
            PedagogSeeder::class,        // needs departments
            ProgramStudimSeeder::class,  // needs departments
            LendaSeeder::class,          // needs departments
            KurrikulaSeeder::class,      // needs programs + courses

            // ── Academic calendar ──────────────────
            VitAkademikSeeder::class,
            SemestrSeeder::class,

            // ── Rooms ──────────────────────────────
            SalleSeeder::class,          // lecture halls + labs
            ZyreSeeder::class,           // offices (needs pedagogues)

            // ── Dormitories ────────────────────────
            KonviktSeeder::class,

            // ── Library ───────────────────────────
            LibnSeeder::class,
        ]);
    }
}
