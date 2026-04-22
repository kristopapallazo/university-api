<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Pedagog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PedagogSeeder extends Seeder
{
    public function run(): void
    {
        // 2 pedagogues per department — keyed by department name
        $map = [
            'Departamenti i Menaxhimit' => [
                ['PED_EM' => 'Arjon',   'PED_MB' => 'Hoxha',   'PED_GJINI' => 'M', 'PED_TITULLI' => 'Prof. Dr.', 'PED_EMAIL' => 'arjon.hoxha@uamd.edu.al',   'PED_DT_PUNESIM' => '2010-09-01'],
                ['PED_EM' => 'Blerta',  'PED_MB' => 'Murati',  'PED_GJINI' => 'F', 'PED_TITULLI' => 'Dr.',       'PED_EMAIL' => 'blerta.murati@uamd.edu.al',  'PED_DT_PUNESIM' => '2015-09-01'],
            ],
            'Departamenti i Financës dhe Kontabilitetit' => [
                ['PED_EM' => 'Gëzim',   'PED_MB' => 'Shehu',   'PED_GJINI' => 'M', 'PED_TITULLI' => 'Prof. Dr.', 'PED_EMAIL' => 'gezim.shehu@uamd.edu.al',    'PED_DT_PUNESIM' => '2008-09-01'],
                ['PED_EM' => 'Elona',   'PED_MB' => 'Popa',    'PED_GJINI' => 'F', 'PED_TITULLI' => 'MSc.',      'PED_EMAIL' => 'elona.popa@uamd.edu.al',     'PED_DT_PUNESIM' => '2018-09-01'],
            ],
            'Departamenti i Marketingut' => [
                ['PED_EM' => 'Ilir',    'PED_MB' => 'Rexha',   'PED_GJINI' => 'M', 'PED_TITULLI' => 'Dr.',       'PED_EMAIL' => 'ilir.rexha@uamd.edu.al',     'PED_DT_PUNESIM' => '2012-09-01'],
                ['PED_EM' => 'Ilda',    'PED_MB' => 'Ndoka',   'PED_GJINI' => 'F', 'PED_TITULLI' => 'Lec.',      'PED_EMAIL' => 'ilda.ndoka@uamd.edu.al',     'PED_DT_PUNESIM' => '2020-09-01'],
            ],
            'Departamenti i Informatikës' => [
                ['PED_EM' => 'Bledar',  'PED_MB' => 'Osmani',  'PED_GJINI' => 'M', 'PED_TITULLI' => 'Prof. Dr.', 'PED_EMAIL' => 'bledar.osmani@uamd.edu.al',  'PED_DT_PUNESIM' => '2009-09-01'],
                ['PED_EM' => 'Klaudia', 'PED_MB' => 'Tafa',    'PED_GJINI' => 'F', 'PED_TITULLI' => 'Dr.',       'PED_EMAIL' => 'klaudia.tafa@uamd.edu.al',   'PED_DT_PUNESIM' => '2016-09-01'],
            ],
            'Departamenti i Inxhinierisë Civile' => [
                ['PED_EM' => 'Erion',   'PED_MB' => 'Krasniqi', 'PED_GJINI' => 'M', 'PED_TITULLI' => 'Prof. Dr.', 'PED_EMAIL' => 'erion.krasniqi@uamd.edu.al', 'PED_DT_PUNESIM' => '2007-09-01'],
                ['PED_EM' => 'Gentiana', 'PED_MB' => 'Ukaj',    'PED_GJINI' => 'F', 'PED_TITULLI' => 'Dr.',       'PED_EMAIL' => 'gentiana.ukaj@uamd.edu.al',  'PED_DT_PUNESIM' => '2014-09-01'],
            ],
            'Departamenti i Inxhinierisë Mekanike' => [
                ['PED_EM' => 'Lorenc',  'PED_MB' => 'Cela',    'PED_GJINI' => 'M', 'PED_TITULLI' => 'Prof. Dr.', 'PED_EMAIL' => 'lorenc.cela@uamd.edu.al',    'PED_DT_PUNESIM' => '2011-09-01'],
                ['PED_EM' => 'Mimoza',  'PED_MB' => 'Deda',    'PED_GJINI' => 'F', 'PED_TITULLI' => 'MSc.',      'PED_EMAIL' => 'mimoza.deda@uamd.edu.al',    'PED_DT_PUNESIM' => '2019-09-01'],
            ],
            'Departamenti i Edukimit Fillor' => [
                ['PED_EM' => 'Kujtim',  'PED_MB' => 'Gjoka',   'PED_GJINI' => 'M', 'PED_TITULLI' => 'Dr.',       'PED_EMAIL' => 'kujtim.gjoka@uamd.edu.al',   'PED_DT_PUNESIM' => '2013-09-01'],
                ['PED_EM' => 'Fatmira', 'PED_MB' => 'Leka',    'PED_GJINI' => 'F', 'PED_TITULLI' => 'MSc.',      'PED_EMAIL' => 'fatmira.leka@uamd.edu.al',   'PED_DT_PUNESIM' => '2017-09-01'],
            ],
            'Departamenti i Psikologjisë' => [
                ['PED_EM' => 'Orion',   'PED_MB' => 'Basha',   'PED_GJINI' => 'M', 'PED_TITULLI' => 'Dr.',       'PED_EMAIL' => 'orion.basha@uamd.edu.al',    'PED_DT_PUNESIM' => '2014-09-01'],
                ['PED_EM' => 'Nora',    'PED_MB' => 'Gjoni',   'PED_GJINI' => 'F', 'PED_TITULLI' => 'MSc.',      'PED_EMAIL' => 'nora.gjoni@uamd.edu.al',     'PED_DT_PUNESIM' => '2021-09-01'],
            ],
            'Departamenti i Drejtësisë' => [
                ['PED_EM' => 'Artan',   'PED_MB' => 'Zoto',    'PED_GJINI' => 'M', 'PED_TITULLI' => 'Prof. Dr.', 'PED_EMAIL' => 'artan.zoto@uamd.edu.al',     'PED_DT_PUNESIM' => '2006-09-01'],
                ['PED_EM' => 'Merita',  'PED_MB' => 'Xhafa',   'PED_GJINI' => 'F', 'PED_TITULLI' => 'Dr.',       'PED_EMAIL' => 'merita.xhafa@uamd.edu.al',   'PED_DT_PUNESIM' => '2015-09-01'],
            ],
            'Departamenti i Shkencave Politike' => [
                ['PED_EM' => 'Besnik',  'PED_MB' => 'Marku',   'PED_GJINI' => 'M', 'PED_TITULLI' => 'Dr.',       'PED_EMAIL' => 'besnik.marku@uamd.edu.al',   'PED_DT_PUNESIM' => '2012-09-01'],
                ['PED_EM' => 'Alma',    'PED_MB' => 'Veli',    'PED_GJINI' => 'F', 'PED_TITULLI' => 'MSc.',      'PED_EMAIL' => 'alma.veli@uamd.edu.al',      'PED_DT_PUNESIM' => '2020-09-01'],
            ],
            'Departamenti i Turizmit' => [
                ['PED_EM' => 'Dritan',  'PED_MB' => 'Curri',   'PED_GJINI' => 'M', 'PED_TITULLI' => 'Dr.',       'PED_EMAIL' => 'dritan.curri@uamd.edu.al',   'PED_DT_PUNESIM' => '2013-09-01'],
                ['PED_EM' => 'Valbona', 'PED_MB' => 'Ago',     'PED_GJINI' => 'F', 'PED_TITULLI' => 'MSc.',      'PED_EMAIL' => 'valbona.ago@uamd.edu.al',    'PED_DT_PUNESIM' => '2018-09-01'],
            ],
            'Departamenti i Punës Sociale' => [
                ['PED_EM' => 'Florjan', 'PED_MB' => 'Mema',    'PED_GJINI' => 'M', 'PED_TITULLI' => 'Dr.',       'PED_EMAIL' => 'florjan.mema@uamd.edu.al',   'PED_DT_PUNESIM' => '2016-09-01'],
                ['PED_EM' => 'Silvana', 'PED_MB' => 'Hasa',    'PED_GJINI' => 'F', 'PED_TITULLI' => 'MSc.',      'PED_EMAIL' => 'silvana.hasa@uamd.edu.al',   'PED_DT_PUNESIM' => '2019-09-01'],
            ],
            'Departamenti i Matematikës' => [
                ['PED_EM' => 'Agron',   'PED_MB' => 'Myftiu',  'PED_GJINI' => 'M', 'PED_TITULLI' => 'Prof. Dr.', 'PED_EMAIL' => 'agron.myftiu@uamd.edu.al',   'PED_DT_PUNESIM' => '2005-09-01'],
                ['PED_EM' => 'Teuta',   'PED_MB' => 'Bello',   'PED_GJINI' => 'F', 'PED_TITULLI' => 'Dr.',       'PED_EMAIL' => 'teuta.bello@uamd.edu.al',    'PED_DT_PUNESIM' => '2014-09-01'],
            ],
            'Departamenti i Fizikës' => [
                ['PED_EM' => 'Bardhyl', 'PED_MB' => 'Shyti',   'PED_GJINI' => 'M', 'PED_TITULLI' => 'Prof. Dr.', 'PED_EMAIL' => 'bardhyl.shyti@uamd.edu.al',  'PED_DT_PUNESIM' => '2008-09-01'],
                ['PED_EM' => 'Ornela',  'PED_MB' => 'Lami',    'PED_GJINI' => 'F', 'PED_TITULLI' => 'Dr.',       'PED_EMAIL' => 'ornela.lami@uamd.edu.al',    'PED_DT_PUNESIM' => '2017-09-01'],
            ],
            'Departamenti i Kimisë dhe Biologjisë' => [
                ['PED_EM' => 'Nikolin', 'PED_MB' => 'Ago',     'PED_GJINI' => 'M', 'PED_TITULLI' => 'Dr.',       'PED_EMAIL' => 'nikolin.ago@uamd.edu.al',    'PED_DT_PUNESIM' => '2011-09-01'],
                ['PED_EM' => 'Edlira',  'PED_MB' => 'Nika',    'PED_GJINI' => 'F', 'PED_TITULLI' => 'MSc.',      'PED_EMAIL' => 'edlira.nika@uamd.edu.al',    'PED_DT_PUNESIM' => '2022-09-01'],
            ],
        ];

        foreach ($map as $depName => $pedagogues) {
            $department = Department::where('DEP_EM', $depName)->first();

            if (! $department) {
                continue;
            }

            foreach ($pedagogues as $data) {
                Pedagog::firstOrCreate(
                    ['PED_EMAIL' => $data['PED_EMAIL']],
                    array_merge($data, ['DEP_ID' => $department->DEP_ID])
                );
            }
        }

        // Create a users table row for each pedagog so they can log in.
        // In production, the user row is created on first Google OAuth login.
        // This seeder is for testing/dev only.
        foreach (Pedagog::all() as $ped) {
            User::firstOrCreate(
                ['email' => $ped->PED_EMAIL],
                [
                    'name' => "{$ped->PED_EM} {$ped->PED_MB}",
                    'role' => 'pedagog',
                    'password' => Hash::make('Testtest1!'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
