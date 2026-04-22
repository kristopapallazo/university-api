<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Lenda;
use Illuminate\Database\Seeder;

class LendaSeeder extends Seeder
{
    public function run(): void
    {
        // 5–6 courses per department
        // LEND_KOD: dept prefix + 3-digit number (unique across entire table)
        $map = [
            'Departamenti i Menaxhimit' => [
                ['LEND_EMER' => 'Bazat e Menaxhimit',                    'LEND_KOD' => 'MEN101'],
                ['LEND_EMER' => 'Menaxhim Strategjik',                   'LEND_KOD' => 'MEN201'],
                ['LEND_EMER' => 'Sjellja Organizative',                  'LEND_KOD' => 'MEN202'],
                ['LEND_EMER' => 'Menaxhim Burimesh Njerëzore',           'LEND_KOD' => 'MEN301'],
                ['LEND_EMER' => 'Lidershipi dhe Vendimmarrja',           'LEND_KOD' => 'MEN302'],
            ],
            'Departamenti i Financës dhe Kontabilitetit' => [
                ['LEND_EMER' => 'Kontabilitet i Përgjithshëm',           'LEND_KOD' => 'FIN101'],
                ['LEND_EMER' => 'Financë e Korporatave',                 'LEND_KOD' => 'FIN201'],
                ['LEND_EMER' => 'Analizë Financiare',                    'LEND_KOD' => 'FIN202'],
                ['LEND_EMER' => 'Kontabilitet Menaxherial',              'LEND_KOD' => 'FIN301'],
                ['LEND_EMER' => 'Auditim',                               'LEND_KOD' => 'FIN302'],
            ],
            'Departamenti i Marketingut' => [
                ['LEND_EMER' => 'Bazat e Marketingut',                   'LEND_KOD' => 'MKT101'],
                ['LEND_EMER' => 'Marketing Dixhital',                    'LEND_KOD' => 'MKT201'],
                ['LEND_EMER' => 'Sjellja e Konsumatorit',                'LEND_KOD' => 'MKT202'],
                ['LEND_EMER' => 'Menaxhim Marke',                        'LEND_KOD' => 'MKT301'],
                ['LEND_EMER' => 'Strategji Marketingu',                  'LEND_KOD' => 'MKT302'],
            ],
            'Departamenti i Informatikës' => [
                ['LEND_EMER' => 'Programim i Strukturuar',               'LEND_KOD' => 'INF101'],
                ['LEND_EMER' => 'Strukturat e të Dhënave',               'LEND_KOD' => 'INF102'],
                ['LEND_EMER' => 'Bazat e të Dhënave',                    'LEND_KOD' => 'INF201'],
                ['LEND_EMER' => 'Zhvillim Web',                          'LEND_KOD' => 'INF202'],
                ['LEND_EMER' => 'Rrjeta Kompjuterike',                   'LEND_KOD' => 'INF203'],
                ['LEND_EMER' => 'Inxhinieri Softuerësh',                 'LEND_KOD' => 'INF301'],
            ],
            'Departamenti i Inxhinierisë Civile' => [
                ['LEND_EMER' => 'Statika e Ndërtesave',                  'LEND_KOD' => 'CIV101'],
                ['LEND_EMER' => 'Materialet e Ndërtimit',                'LEND_KOD' => 'CIV102'],
                ['LEND_EMER' => 'Hidraulikë',                            'LEND_KOD' => 'CIV201'],
                ['LEND_EMER' => 'Konstruksion Betoni të Armuar',         'LEND_KOD' => 'CIV301'],
                ['LEND_EMER' => 'Menaxhim Projekti në Ndërtim',         'LEND_KOD' => 'CIV302'],
            ],
            'Departamenti i Inxhinierisë Mekanike' => [
                ['LEND_EMER' => 'Mekanikë Teknike',                      'LEND_KOD' => 'MEK101'],
                ['LEND_EMER' => 'Termodinamikë',                         'LEND_KOD' => 'MEK102'],
                ['LEND_EMER' => 'Rezistenca e Materialeve',              'LEND_KOD' => 'MEK201'],
                ['LEND_EMER' => 'Makineri dhe Mekanizma',                'LEND_KOD' => 'MEK301'],
                ['LEND_EMER' => 'Projektim Mekanik CAD',                 'LEND_KOD' => 'MEK302'],
            ],
            'Departamenti i Edukimit Fillor' => [
                ['LEND_EMER' => 'Pedagogji e Përgjithshme',              'LEND_KOD' => 'EDU101'],
                ['LEND_EMER' => 'Didaktikë',                             'LEND_KOD' => 'EDU102'],
                ['LEND_EMER' => 'Psikologji Edukimi',                    'LEND_KOD' => 'EDU201'],
                ['LEND_EMER' => 'Metodika e Mësimdhënies',               'LEND_KOD' => 'EDU301'],
                ['LEND_EMER' => 'Edukimi Gjithëpërfshirës',              'LEND_KOD' => 'EDU302'],
            ],
            'Departamenti i Psikologjisë' => [
                ['LEND_EMER' => 'Hyrje në Psikologji',                   'LEND_KOD' => 'PSI101'],
                ['LEND_EMER' => 'Psikologji Sociale',                    'LEND_KOD' => 'PSI201'],
                ['LEND_EMER' => 'Psikologji Klinike',                    'LEND_KOD' => 'PSI202'],
                ['LEND_EMER' => 'Psikologji Zhvillimi',                  'LEND_KOD' => 'PSI301'],
                ['LEND_EMER' => 'Këshillim Psikologjik',                 'LEND_KOD' => 'PSI302'],
            ],
            'Departamenti i Drejtësisë' => [
                ['LEND_EMER' => 'E Drejtë Kushtetuese',                  'LEND_KOD' => 'DRE101'],
                ['LEND_EMER' => 'E Drejtë Civile',                       'LEND_KOD' => 'DRE102'],
                ['LEND_EMER' => 'E Drejtë Penale',                       'LEND_KOD' => 'DRE201'],
                ['LEND_EMER' => 'E Drejtë Administrative',               'LEND_KOD' => 'DRE202'],
                ['LEND_EMER' => 'E Drejtë Ndërkombëtare Private',        'LEND_KOD' => 'DRE301'],
            ],
            'Departamenti i Shkencave Politike' => [
                ['LEND_EMER' => 'Hyrje në Shkenca Politike',             'LEND_KOD' => 'POL101'],
                ['LEND_EMER' => 'Sisteme Politike Krahasuese',           'LEND_KOD' => 'POL201'],
                ['LEND_EMER' => 'Marrëdhënie Ndërkombëtare',             'LEND_KOD' => 'POL202'],
                ['LEND_EMER' => 'Teoria Politike',                       'LEND_KOD' => 'POL301'],
            ],
            'Departamenti i Turizmit' => [
                ['LEND_EMER' => 'Hyrje në Turizëm',                      'LEND_KOD' => 'TUR101'],
                ['LEND_EMER' => 'Menaxhim Hotelier',                     'LEND_KOD' => 'TUR201'],
                ['LEND_EMER' => 'Marketing Turizmi',                     'LEND_KOD' => 'TUR202'],
                ['LEND_EMER' => 'Turizëm i Qëndrueshëm',                 'LEND_KOD' => 'TUR301'],
                ['LEND_EMER' => 'Gjeografi Turizmi',                     'LEND_KOD' => 'TUR302'],
            ],
            'Departamenti i Punës Sociale' => [
                ['LEND_EMER' => 'Hyrje në Punën Sociale',                'LEND_KOD' => 'PSH101'],
                ['LEND_EMER' => 'Politikë Sociale',                      'LEND_KOD' => 'PSH201'],
                ['LEND_EMER' => 'Punë Sociale me Grupet',                'LEND_KOD' => 'PSH202'],
                ['LEND_EMER' => 'Mirëqenia Sociale',                     'LEND_KOD' => 'PSH301'],
            ],
            'Departamenti i Matematikës' => [
                ['LEND_EMER' => 'Analizë Matematike I',                  'LEND_KOD' => 'MAT101'],
                ['LEND_EMER' => 'Analizë Matematike II',                 'LEND_KOD' => 'MAT102'],
                ['LEND_EMER' => 'Algjebër Lineare',                      'LEND_KOD' => 'MAT201'],
                ['LEND_EMER' => 'Probabilitet dhe Statistikë',           'LEND_KOD' => 'MAT202'],
                ['LEND_EMER' => 'Ekuacione Diferenciale',                'LEND_KOD' => 'MAT301'],
            ],
            'Departamenti i Fizikës' => [
                ['LEND_EMER' => 'Fizikë e Përgjithshme I',               'LEND_KOD' => 'FIZ101'],
                ['LEND_EMER' => 'Fizikë e Përgjithshme II',              'LEND_KOD' => 'FIZ102'],
                ['LEND_EMER' => 'Elektriciteti dhe Magnetizmi',          'LEND_KOD' => 'FIZ201'],
                ['LEND_EMER' => 'Mekanikë Kuantike',                     'LEND_KOD' => 'FIZ301'],
            ],
            'Departamenti i Kimisë dhe Biologjisë' => [
                ['LEND_EMER' => 'Kimi e Përgjithshme',                   'LEND_KOD' => 'KIM101'],
                ['LEND_EMER' => 'Biologji e Përgjithshme',               'LEND_KOD' => 'KIM102'],
                ['LEND_EMER' => 'Kimi Organike',                         'LEND_KOD' => 'KIM201'],
                ['LEND_EMER' => 'Mikrobiologji',                         'LEND_KOD' => 'KIM202'],
                ['LEND_EMER' => 'Biokimi',                               'LEND_KOD' => 'KIM301'],
            ],
        ];

        foreach ($map as $depName => $courses) {
            $department = Department::where('DEP_EM', $depName)->first();

            if (! $department) {
                continue;
            }

            foreach ($courses as $data) {
                Lenda::firstOrCreate(
                    ['LEND_KOD' => $data['LEND_KOD']],
                    array_merge($data, ['DEP_ID' => $department->DEP_ID])
                );
            }
        }
    }
}
