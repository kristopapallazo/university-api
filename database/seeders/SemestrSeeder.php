<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SemestrSeeder extends Seeder
{
    public function run(): void
    {
        $semesters = [
            // 2023-2024
            ['VIT_EMER' => '2023-2024', 'SEM_NR' => 1, 'SEM_DAT_FILLIMI' => '2023-10-01', 'SEM_DAT_MBARIMI' => '2024-01-31'],
            ['VIT_EMER' => '2023-2024', 'SEM_NR' => 2, 'SEM_DAT_FILLIMI' => '2024-02-01', 'SEM_DAT_MBARIMI' => '2024-06-30'],
            // 2024-2025
            ['VIT_EMER' => '2024-2025', 'SEM_NR' => 1, 'SEM_DAT_FILLIMI' => '2024-10-01', 'SEM_DAT_MBARIMI' => '2025-01-31'],
            ['VIT_EMER' => '2024-2025', 'SEM_NR' => 2, 'SEM_DAT_FILLIMI' => '2025-02-01', 'SEM_DAT_MBARIMI' => '2025-06-30'],
        ];

        foreach ($semesters as $sem) {
            $vit = DB::table('VIT_AKADEMIK')->where('VIT_EMER', $sem['VIT_EMER'])->first();

            if (! $vit) {
                continue;
            }

            $exists = DB::table('SEMESTR')
                ->where('VIT_ID', $vit->VIT_ID)
                ->where('SEM_NR', $sem['SEM_NR'])
                ->exists();

            if (! $exists) {
                DB::table('SEMESTR')->insert([
                    'SEM_NR'          => $sem['SEM_NR'],
                    'SEM_DAT_FILLIMI' => $sem['SEM_DAT_FILLIMI'],
                    'SEM_DAT_MBARIMI' => $sem['SEM_DAT_MBARIMI'],
                    'VIT_ID'          => $vit->VIT_ID,
                ]);
            }
        }
    }
}
