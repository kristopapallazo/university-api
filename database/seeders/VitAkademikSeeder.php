<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VitAkademikSeeder extends Seeder
{
    public function run(): void
    {
        $years = [
            [
                'VIT_EMER'      => '2023-2024',
                'DATE_FILLIMI'  => '2023-10-01',
                'DATE_MBARIMI'  => '2024-06-30',
                'AKTIV'         => false,
            ],
            [
                'VIT_EMER'      => '2024-2025',
                'DATE_FILLIMI'  => '2024-10-01',
                'DATE_MBARIMI'  => '2025-06-30',
                'AKTIV'         => true,   // current academic year
            ],
        ];

        foreach ($years as $year) {
            $exists = DB::table('VIT_AKADEMIK')
                ->where('VIT_EMER', $year['VIT_EMER'])
                ->exists();

            if (! $exists) {
                DB::table('VIT_AKADEMIK')->insert($year);
            }
        }
    }
}
