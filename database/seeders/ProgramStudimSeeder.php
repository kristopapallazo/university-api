<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\ProgramStudim;
use Illuminate\Database\Seeder;

class ProgramStudimSeeder extends Seeder
{
    public function run(): void
    {
        // Programs keyed by department name
        // PROG_NIV: 'Bachelor' | 'Master' | 'Doktorature'
        // PROG_KRD: 180 = 3yr Bachelor, 240 = 4yr Bachelor (engineering), 120 = 2yr Master
        $map = [
            'Departamenti i Menaxhimit' => [
                ['PROG_EM' => 'Menaxhim Biznesi',            'PROG_NIV' => 'Bachelor', 'PROG_KRD' => 180],
                ['PROG_EM' => 'Administrim Biznesi',          'PROG_NIV' => 'Master',   'PROG_KRD' => 120],
            ],
            'Departamenti i Financës dhe Kontabilitetit' => [
                ['PROG_EM' => 'Financë-Kontabilitet',         'PROG_NIV' => 'Bachelor', 'PROG_KRD' => 180],
                ['PROG_EM' => 'Auditim dhe Kontabilitet',     'PROG_NIV' => 'Master',   'PROG_KRD' => 120],
            ],
            'Departamenti i Marketingut' => [
                ['PROG_EM' => 'Marketing',                    'PROG_NIV' => 'Bachelor', 'PROG_KRD' => 180],
            ],
            'Departamenti i Informatikës' => [
                ['PROG_EM' => 'Informatikë',                  'PROG_NIV' => 'Bachelor', 'PROG_KRD' => 180],
                ['PROG_EM' => 'Shkenca Kompjuterike',         'PROG_NIV' => 'Master',   'PROG_KRD' => 120],
            ],
            'Departamenti i Inxhinierisë Civile' => [
                ['PROG_EM' => 'Inxhinieri Civile',            'PROG_NIV' => 'Bachelor', 'PROG_KRD' => 240],
            ],
            'Departamenti i Inxhinierisë Mekanike' => [
                ['PROG_EM' => 'Inxhinieri Mekanike',          'PROG_NIV' => 'Bachelor', 'PROG_KRD' => 240],
            ],
            'Departamenti i Edukimit Fillor' => [
                ['PROG_EM' => 'Arsim Fillor',                 'PROG_NIV' => 'Bachelor', 'PROG_KRD' => 180],
            ],
            'Departamenti i Psikologjisë' => [
                ['PROG_EM' => 'Psikologji',                   'PROG_NIV' => 'Bachelor', 'PROG_KRD' => 180],
                ['PROG_EM' => 'Psikologji Klinike',           'PROG_NIV' => 'Master',   'PROG_KRD' => 120],
            ],
            'Departamenti i Drejtësisë' => [
                ['PROG_EM' => 'Drejtësi',                     'PROG_NIV' => 'Bachelor', 'PROG_KRD' => 240],
                ['PROG_EM' => 'E Drejtë Ndërkombëtare',       'PROG_NIV' => 'Master',   'PROG_KRD' => 120],
            ],
            'Departamenti i Shkencave Politike' => [
                ['PROG_EM' => 'Shkenca Politike',             'PROG_NIV' => 'Bachelor', 'PROG_KRD' => 180],
            ],
            'Departamenti i Turizmit' => [
                ['PROG_EM' => 'Turizëm',                      'PROG_NIV' => 'Bachelor', 'PROG_KRD' => 180],
                ['PROG_EM' => 'Menaxhim Turizmi',             'PROG_NIV' => 'Master',   'PROG_KRD' => 120],
            ],
            'Departamenti i Punës Sociale' => [
                ['PROG_EM' => 'Punë Sociale',                 'PROG_NIV' => 'Bachelor', 'PROG_KRD' => 180],
            ],
            'Departamenti i Matematikës' => [
                ['PROG_EM' => 'Matematikë',                   'PROG_NIV' => 'Bachelor', 'PROG_KRD' => 180],
            ],
            'Departamenti i Fizikës' => [
                ['PROG_EM' => 'Fizikë',                       'PROG_NIV' => 'Bachelor', 'PROG_KRD' => 180],
            ],
            'Departamenti i Kimisë dhe Biologjisë' => [
                ['PROG_EM' => 'Kimi-Biologji',                'PROG_NIV' => 'Bachelor', 'PROG_KRD' => 180],
            ],
        ];

        foreach ($map as $depName => $programs) {
            $department = Department::where('DEP_EM', $depName)->first();

            if (! $department) {
                continue;
            }

            foreach ($programs as $data) {
                ProgramStudim::firstOrCreate(
                    ['PROG_EM' => $data['PROG_EM'], 'DEP_ID' => $department->DEP_ID],
                    array_merge($data, ['DEP_ID' => $department->DEP_ID])
                );
            }
        }
    }
}
