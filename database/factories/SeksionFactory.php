<?php

namespace Database\Factories;

use App\Models\Seksion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seksion>
 */
class SeksionFactory extends Factory
{
    protected $model = Seksion::class;

    public function definition(): array
    {
        return [
            'DITA' => 'Hene',
            'ORE_FILLIMI' => '09:00:00',
            'ORE_MBARIMI' => '11:00:00',
            'LEND_ID' => 1,
            'PED_ID' => 1,
            'PROG_ID' => 1,
            'SEM_ID' => 1,
            'SALL_ID' => 1,
        ];
    }
}
