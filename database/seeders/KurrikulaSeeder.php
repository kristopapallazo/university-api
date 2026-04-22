<?php

namespace Database\Seeders;

use App\Models\Kurrikula;
use App\Models\Lenda;
use App\Models\ProgramStudim;
use Illuminate\Database\Seeder;

class KurrikulaSeeder extends Seeder
{
    public function run(): void
    {
        // Maps program name -> list of course codes with year, semester, credits, mandatory flag
        // KURR_VIT: 1–5 (year of study within the program)
        // KURR_NR_SEMESTER: 1 or 2
        // KURR_KREDIT: ECTS credits for this course
        // KURR_I_DETYRUESHEM: true = mandatory, false = elective
        $map = [
            'Informatikë' => [
                ['kod' => 'INF101', 'vit' => 1, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'INF102', 'vit' => 1, 'sem' => 2, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'MAT101', 'vit' => 1, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'MAT201', 'vit' => 1, 'sem' => 2, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'INF201', 'vit' => 2, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'INF202', 'vit' => 2, 'sem' => 2, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'INF203', 'vit' => 2, 'sem' => 2, 'kredit' => 4, 'detyrueshem' => true],
                ['kod' => 'INF301', 'vit' => 3, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
            ],
            'Menaxhim Biznesi' => [
                ['kod' => 'MEN101', 'vit' => 1, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'FIN101', 'vit' => 1, 'sem' => 2, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'MKT101', 'vit' => 1, 'sem' => 2, 'kredit' => 4, 'detyrueshem' => true],
                ['kod' => 'MEN201', 'vit' => 2, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'MEN202', 'vit' => 2, 'sem' => 2, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'MEN301', 'vit' => 3, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'MEN302', 'vit' => 3, 'sem' => 2, 'kredit' => 4, 'detyrueshem' => false],
            ],
            'Financë-Kontabilitet' => [
                ['kod' => 'FIN101', 'vit' => 1, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'MEN101', 'vit' => 1, 'sem' => 2, 'kredit' => 4, 'detyrueshem' => true],
                ['kod' => 'FIN201', 'vit' => 2, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'FIN202', 'vit' => 2, 'sem' => 2, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'FIN301', 'vit' => 3, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'FIN302', 'vit' => 3, 'sem' => 2, 'kredit' => 6, 'detyrueshem' => true],
            ],
            'Drejtësi' => [
                ['kod' => 'DRE101', 'vit' => 1, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'DRE102', 'vit' => 1, 'sem' => 2, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'DRE201', 'vit' => 2, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'DRE202', 'vit' => 2, 'sem' => 2, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'DRE301', 'vit' => 3, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'POL101', 'vit' => 3, 'sem' => 2, 'kredit' => 4, 'detyrueshem' => false],
            ],
            'Inxhinieri Civile' => [
                ['kod' => 'CIV101', 'vit' => 1, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'CIV102', 'vit' => 1, 'sem' => 2, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'MAT101', 'vit' => 1, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'FIZ101', 'vit' => 1, 'sem' => 2, 'kredit' => 4, 'detyrueshem' => true],
                ['kod' => 'CIV201', 'vit' => 2, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'CIV301', 'vit' => 3, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'CIV302', 'vit' => 4, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
            ],
            'Psikologji' => [
                ['kod' => 'PSI101', 'vit' => 1, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'PSI201', 'vit' => 2, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'PSI202', 'vit' => 2, 'sem' => 2, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'PSI301', 'vit' => 3, 'sem' => 1, 'kredit' => 6, 'detyrueshem' => true],
                ['kod' => 'PSI302', 'vit' => 3, 'sem' => 2, 'kredit' => 6, 'detyrueshem' => true],
            ],
        ];

        foreach ($map as $programName => $entries) {
            $program = ProgramStudim::where('PROG_EM', $programName)->first();

            if (! $program) {
                continue;
            }

            foreach ($entries as $entry) {
                $lenda = Lenda::where('LEND_KOD', $entry['kod'])->first();

                if (! $lenda) {
                    continue;
                }

                Kurrikula::firstOrCreate(
                    ['PROG_ID' => $program->PROG_ID, 'LEND_ID' => $lenda->LEND_ID],
                    [
                        'KURR_VIT' => $entry['vit'],
                        'KURR_NR_SEMESTER' => $entry['sem'],
                        'KURR_KREDIT' => $entry['kredit'],
                        'KURR_I_DETYRUESHEM' => $entry['detyrueshem'],
                        'PROG_ID' => $program->PROG_ID,
                        'LEND_ID' => $lenda->LEND_ID,
                    ]
                );
            }
        }
    }
}
