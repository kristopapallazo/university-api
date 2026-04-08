<?php

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        $faculties = [
            'Fakulteti i Shkencave të Biznesit',
            'Fakulteti i Shkencave Teknike',
            'Fakulteti i Edukimit',
            'Fakulteti i Shkencave Juridike dhe Politike',
            'Fakulteti i Shkencave Profesionale',
            'Fakulteti i Shkencave të Natyrës',
        ];

        foreach ($faculties as $name) {
            Faculty::firstOrCreate(['FAK_EM' => $name]);
        }
    }
}
