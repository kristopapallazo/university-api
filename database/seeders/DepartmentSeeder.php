<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'Fakulteti i Shkencave të Biznesit' => [
                'Departamenti i Menaxhimit',
                'Departamenti i Financës dhe Kontabilitetit',
                'Departamenti i Marketingut',
            ],
            'Fakulteti i Shkencave Teknike' => [
                'Departamenti i Informatikës',
                'Departamenti i Inxhinierisë Civile',
                'Departamenti i Inxhinierisë Mekanike',
            ],
            'Fakulteti i Edukimit' => [
                'Departamenti i Edukimit Fillor',
                'Departamenti i Psikologjisë',
            ],
            'Fakulteti i Shkencave Juridike dhe Politike' => [
                'Departamenti i Drejtësisë',
                'Departamenti i Shkencave Politike',
            ],
            'Fakulteti i Shkencave Profesionale' => [
                'Departamenti i Turizmit',
                'Departamenti i Punës Sociale',
            ],
            'Fakulteti i Shkencave të Natyrës' => [
                'Departamenti i Matematikës',
                'Departamenti i Fizikës',
                'Departamenti i Kimisë dhe Biologjisë',
            ],
        ];

        foreach ($map as $facultyName => $departments) {
            $faculty = Faculty::where('FAK_EM', $facultyName)->first();

            if (! $faculty) {
                continue;
            }

            foreach ($departments as $depName) {
                Department::firstOrCreate([
                    'DEP_EM' => $depName,
                ], [
                    'FAK_ID' => $faculty->FAK_ID,
                ]);
            }
        }
    }
}
