<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KonviktSeeder extends Seeder
{
    public function run(): void
    {
        $konviktet = [
            [
                'KONV_EMER' => 'Konvikti i Studentëve Nr. 1',
                'KONV_ADRESE' => 'Rruga Aleksander Moisiu, Durrës',
                'KONV_KAPACITET' => 160,
                'dhomat' => 40, // 4 students per room
            ],
            [
                'KONV_EMER' => 'Konvikti i Studentëve Nr. 2',
                'KONV_ADRESE' => 'Lagjia Shkozë, Durrës',
                'KONV_KAPACITET' => 120,
                'dhomat' => 30,
            ],
        ];

        foreach ($konviktet as $k) {
            $konvId = DB::table('KONVIKT')
                ->where('KONV_EMER', $k['KONV_EMER'])
                ->value('KONV_ID');

            if (! $konvId) {
                $konvId = DB::table('KONVIKT')->insertGetId([
                    'KONV_EMER' => $k['KONV_EMER'],
                    'KONV_ADRESE' => $k['KONV_ADRESE'],
                    'KONV_KAPACITET' => $k['KONV_KAPACITET'],
                ]);
            }

            for ($i = 1; $i <= $k['dhomat']; $i++) {
                $nr = str_pad($i, 3, '0', STR_PAD_LEFT); // 001, 002, ...

                $exists = DB::table('DHOME')
                    ->where('KONV_ID', $konvId)
                    ->where('DHOM_NR', $nr)
                    ->exists();

                if (! $exists) {
                    DB::table('DHOME')->insert([
                        'DHOM_NR' => $nr,
                        'DHOM_KAPACITET' => 4,
                        'KONV_ID' => $konvId,
                    ]);
                }
            }
        }
    }
}
