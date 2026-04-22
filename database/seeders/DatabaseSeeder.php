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

            // ── Academic structure ─────────────────
            FacultySeeder::class,
            DepartmentSeeder::class,
            TestSeeder::class,           // static test users (needs departments for pedagog DEP_ID)
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
