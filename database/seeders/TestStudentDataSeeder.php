<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Fature;
use App\Models\Lenda;
use App\Models\Nota;
use App\Models\Pedagog;
use App\Models\ProgramStudim;
use App\Models\Provim;
use App\Models\Regjistrim;
use App\Models\Salle;
use App\Models\Seksion;
use App\Models\Semestr;
use App\Models\Student;
use Illuminate\Database\Seeder;

/**
 * Seeds realistic academic data for test.student@uamd.edu.al so that
 * Dija's student tools (schedule, grades, exams, invoices) return real data.
 *
 * Reuses existing DB records wherever possible; only creates what is missing.
 * Safe to run multiple times (idempotent via updateOrCreate / firstOrCreate).
 *
 * Run on Railway:  php artisan db:seed --class=TestStudentDataSeeder --force
 */
class TestStudentDataSeeder extends Seeder
{
    private const EMAIL = 'test.student@uamd.edu.al';

    public function run(): void
    {
        $student = Student::where('STU_EMAIL', self::EMAIL)->firstOrFail();

        $faculty = $this->resolveFaculty();
        $department = $this->resolveDepartment($faculty);
        $program = $this->resolveProgram($department);
        $pedagog = $this->resolvePedagog($department);
        $semester = $this->resolveSemester();
        $salle = $this->resolveSalle();

        // Enroll student in the program
        $program->students()->syncWithoutDetaching([$student->STU_ID]);

        $lenda1 = $this->resolveLenda('Algoritmet dhe Strukturat e të Dhënave', 'CS-101', $department);
        $lenda2 = $this->resolveLenda('Bazat e të Dhënave', 'CS-102', $department);
        $lenda3 = $this->resolveLenda('Inxhinieria e Softuerit', 'CS-103', $department);

        $sek1 = $this->resolveSeksion($lenda1, $pedagog, $program, $semester, $salle, 'E Hënë', '08:00', '10:00');
        $sek2 = $this->resolveSeksion($lenda2, $pedagog, $program, $semester, $salle, 'E Martë', '10:00', '12:00');
        $sek3 = $this->resolveSeksion($lenda3, $pedagog, $program, $semester, $salle, 'E Mërkurë', '12:00', '14:00');

        $reg1 = $this->resolveRegjistrim($student, $sek1);
        $reg2 = $this->resolveRegjistrim($student, $sek2);
        $reg3 = $this->resolveRegjistrim($student, $sek3);

        // Exams
        $prov1 = $this->resolveProvim($sek1, '2026-06-20');
        $prov2 = $this->resolveProvim($sek2, '2026-06-22');
        $prov3 = $this->resolveProvim($sek3, '2026-07-01');

        // Grades for first two exams (third is upcoming — no grade yet)
        $this->resolveNota($student, $prov1, 9.5);
        $this->resolveNota($student, $prov2, 8.0);

        // Invoices — one paid, one unpaid
        $this->resolveFature($student, 'paguar', 25000.00, '2026-02-01', 'Tarifë semestrale — Semestri i parë');
        $this->resolveFature($student, 'papaguar', 25000.00, '2026-06-01', 'Tarifë semestrale — Semestri i dytë');

        $this->command->info('Test student data seeded for: ' . self::EMAIL);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function resolveFaculty(): Faculty
    {
        return Faculty::firstOrCreate(
            ['FAK_EM' => 'Fakulteti i Shkencave të Natyrës'],
            ['FAK_EM' => 'Fakulteti i Shkencave të Natyrës']
        );
    }

    private function resolveDepartment(Faculty $faculty): Department
    {
        return Department::firstOrCreate(
            ['DEP_EM' => 'Departamenti i Informatikës', 'FAK_ID' => $faculty->FAK_ID],
            ['DEP_EM' => 'Departamenti i Informatikës', 'FAK_ID' => $faculty->FAK_ID]
        );
    }

    private function resolveProgram(Department $department): ProgramStudim
    {
        return ProgramStudim::firstOrCreate(
            ['PROG_EM' => 'Informatikë (Bachelor)', 'DEP_ID' => $department->DEP_ID],
            [
                'PROG_EM' => 'Informatikë (Bachelor)',
                'PROG_NIV' => 'Bachelor',
                'PROG_KRD' => 180,
                'DEP_ID' => $department->DEP_ID,
            ]
        );
    }

    private function resolvePedagog(Department $department): Pedagog
    {
        return Pedagog::firstOrCreate(
            ['PED_EMAIL' => 'test.pedagog@uamd.edu.al'],
            [
                'PED_EM' => 'Test',
                'PED_MB' => 'Pedagog',
                'PED_GJINI' => 'M',
                'PED_TITULLI' => 'Dr.',
                'PED_DT_PUNESIM' => '2020-01-01',
                'PED_EMAIL' => 'test.pedagog@uamd.edu.al',
                'DEP_ID' => $department->DEP_ID,
            ]
        );
    }

    private function resolveSemester(): Semestr
    {
        return Semestr::firstOrCreate(
            ['SEM_NR' => 2],
            [
                'SEM_NR' => 2,
                'SEM_DAT_FILLIMI' => '2026-02-01',
                'SEM_DAT_MBARIMI' => '2026-07-31',
            ]
        );
    }

    private function resolveSalle(): Salle
    {
        return Salle::firstOrCreate(
            ['SALLE_NR' => 'A101'],
            [
                'SALLE_NR' => 'A101',
                'SALLE_KAPACITET' => 30,
                'SALLE_LLOJ' => 'Leksion',
            ]
        );
    }

    private function resolveLenda(string $name, string $code, Department $department): Lenda
    {
        return Lenda::firstOrCreate(
            ['LEND_KOD' => $code],
            [
                'LEND_EMER' => $name,
                'LEND_KOD' => $code,
                'DEP_ID' => $department->DEP_ID,
            ]
        );
    }

    private function resolveSeksion(
        Lenda $lenda,
        Pedagog $pedagog,
        ProgramStudim $program,
        Semestr $semester,
        Salle $salle,
        string $day,
        string $start,
        string $end,
    ): Seksion {
        return Seksion::firstOrCreate(
            ['LEND_ID' => $lenda->LEND_ID, 'SEM_ID' => $semester->SEM_ID, 'DITA' => $day],
            [
                'DITA' => $day,
                'ORE_FILLIMI' => $start,
                'ORE_MBARIMI' => $end,
                'LEND_ID' => $lenda->LEND_ID,
                'PED_ID' => $pedagog->PED_ID,
                'PROG_ID' => $program->PROG_ID,
                'SEM_ID' => $semester->SEM_ID,
                'SALL_ID' => $salle->SALLE_ID,
            ]
        );
    }

    private function resolveRegjistrim(Student $student, Seksion $seksion): Regjistrim
    {
        return Regjistrim::firstOrCreate(
            ['STU_ID' => $student->STU_ID, 'SEK_ID' => $seksion->SEK_ID],
            [
                'STU_ID' => $student->STU_ID,
                'SEK_ID' => $seksion->SEK_ID,
                'DAT_REGJ' => now()->toDateString(),
                'REGJ_STATUS' => 'aktiv',
            ]
        );
    }

    private function resolveProvim(Seksion $seksion, string $date): Provim
    {
        return Provim::firstOrCreate(
            ['SEK_ID' => $seksion->SEK_ID],
            [
                'SEK_ID' => $seksion->SEK_ID,
                'DAT_PROVIM' => $date,
            ]
        );
    }

    private function resolveNota(Student $student, Provim $provim, float $grade): Nota
    {
        return Nota::firstOrCreate(
            ['STU_ID' => $student->STU_ID, 'PROV_ID' => $provim->PROV_ID],
            [
                'STU_ID' => $student->STU_ID,
                'PROV_ID' => $provim->PROV_ID,
                'NOTA_VLERA' => $grade,
                'NOTA_DAT' => now()->toDateString(),
            ]
        );
    }

    private function resolveFature(Student $student, string $status, float $amount, string $date, string $description): Fature
    {
        return Fature::firstOrCreate(
            ['STU_ID' => $student->STU_ID, 'FAT_PERSHKRIM' => $description],
            [
                'STU_ID' => $student->STU_ID,
                'FAT_DAT_LESHIM' => $date,
                'FAT_SHUMA' => $amount,
                'FAT_STATUSI' => $status,
                'FAT_PERSHKRIM' => $description,
            ]
        );
    }
}
