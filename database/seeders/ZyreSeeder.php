<?php

namespace Database\Seeders;

use App\Models\Pedagog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZyreSeeder extends Seeder
{
    public function run(): void
    {
        // One office per pedagog — SALLE_LLOJ = 'Z'
        // Offices numbered Z-101 upward; assigned one per pedagog in order
        $pedagogues = Pedagog::orderBy('PED_ID')->get();

        $officeNumber = 101;

        foreach ($pedagogues as $pedagog) {
            // Skip if this pedagog already has an office
            if (DB::table('ZYRE')->where('PED_ID', $pedagog->PED_ID)->exists()) {
                $officeNumber++;

                continue;
            }

            $nr = 'Z-' . $officeNumber;

            // Skip if room number already exists
            if (DB::table('SALLE')->where('SALLE_NR', $nr)->exists()) {
                $officeNumber++;

                continue;
            }

            $salleId = DB::table('SALLE')->insertGetId([
                'SALLE_NR' => $nr,
                'SALLE_KAPACITET' => 4,
                'SALLE_LLOJ' => 'Z',
            ]);

            DB::table('ZYRE')->insert([
                'SALL_ID' => $salleId,
                'ZYR_NR' => $nr,
                'PED_ID' => $pedagog->PED_ID,
            ]);

            $officeNumber++;
        }
    }
}
