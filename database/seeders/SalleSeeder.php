<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalleSeeder extends Seeder
{
    public function run(): void
    {
        // ── Lecture halls (SALLE_LLOJ = 'A') ──────────────────────────────
        // Standard 10 halls on floor 1, 5 on floor 2
        $auditoria = [
            ['SALLE_NR' => 'A-101', 'SALLE_KAPACITET' => 80,  'floor' => 1, 'tip' => 'X'],
            ['SALLE_NR' => 'A-102', 'SALLE_KAPACITET' => 80,  'floor' => 1, 'tip' => 'X'],
            ['SALLE_NR' => 'A-103', 'SALLE_KAPACITET' => 60,  'floor' => 1, 'tip' => 'X'],
            ['SALLE_NR' => 'A-104', 'SALLE_KAPACITET' => 60,  'floor' => 1, 'tip' => 'X'],
            ['SALLE_NR' => 'A-105', 'SALLE_KAPACITET' => 100, 'floor' => 1, 'tip' => 'X'],
            ['SALLE_NR' => 'A-106', 'SALLE_KAPACITET' => 120, 'floor' => 1, 'tip' => 'X'],
            ['SALLE_NR' => 'A-107', 'SALLE_KAPACITET' => 40,  'floor' => 1, 'tip' => 'X'],
            ['SALLE_NR' => 'A-108', 'SALLE_KAPACITET' => 40,  'floor' => 1, 'tip' => 'X'],
            ['SALLE_NR' => 'A-201', 'SALLE_KAPACITET' => 80,  'floor' => 2, 'tip' => 'X'],
            ['SALLE_NR' => 'A-202', 'SALLE_KAPACITET' => 80,  'floor' => 2, 'tip' => 'X'],
            ['SALLE_NR' => 'A-203', 'SALLE_KAPACITET' => 60,  'floor' => 2, 'tip' => 'X'],
            ['SALLE_NR' => 'A-204', 'SALLE_KAPACITET' => 60,  'floor' => 2, 'tip' => 'X'],
            ['SALLE_NR' => 'A-205', 'SALLE_KAPACITET' => 100, 'floor' => 2, 'tip' => 'X'],
        ];

        // ── Labs (SALLE_LLOJ = 'A', AUD_TIP = 'L') ────────────────────────
        $laboratore = [
            ['SALLE_NR' => 'L-101', 'SALLE_KAPACITET' => 30, 'floor' => 1, 'pc_nr' => 30, 'pajisje' => 'PC, Projektor, Whiteboard'],
            ['SALLE_NR' => 'L-102', 'SALLE_KAPACITET' => 25, 'floor' => 1, 'pc_nr' => 25, 'pajisje' => 'PC, Projektor, Whiteboard'],
            ['SALLE_NR' => 'L-103', 'SALLE_KAPACITET' => 20, 'floor' => 1, 'pc_nr' => 20, 'pajisje' => 'PC, Pajisje Rrjeti'],
        ];

        foreach ($auditoria as $room) {
            $exists = DB::table('SALLE')->where('SALLE_NR', $room['SALLE_NR'])->exists();
            if ($exists) {
                continue;
            }

            $salleId = DB::table('SALLE')->insertGetId([
                'SALLE_NR'         => $room['SALLE_NR'],
                'SALLE_KAPACITET'  => $room['SALLE_KAPACITET'],
                'SALLE_LLOJ'       => 'A',
            ]);

            DB::table('AUDITOR')->insert([
                'SALL_ID' => $salleId,
                'AUD_Y'   => $room['floor'],
                'AUD_TIP' => $room['tip'],
            ]);
        }

        foreach ($laboratore as $room) {
            $exists = DB::table('SALLE')->where('SALLE_NR', $room['SALLE_NR'])->exists();
            if ($exists) {
                continue;
            }

            $salleId = DB::table('SALLE')->insertGetId([
                'SALLE_NR'        => $room['SALLE_NR'],
                'SALLE_KAPACITET' => $room['SALLE_KAPACITET'],
                'SALLE_LLOJ'      => 'A',
            ]);

            DB::table('AUDITOR')->insert([
                'SALL_ID' => $salleId,
                'AUD_Y'   => $room['floor'],
                'AUD_TIP' => 'L',
            ]);

            DB::table('LABORATOR')->insert([
                'SALLE_ID'    => $salleId,
                'LAB_PC_NR'   => $room['pc_nr'],
                'LAB_PAJISJE' => $room['pajisje'],
            ]);
        }
    }
}
