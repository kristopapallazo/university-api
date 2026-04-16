<?php

namespace Database\Factories;

use App\Models\Nota;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Nota>
 */
class NotaFactory extends Factory
{
    protected $model = Nota::class;

    public function definition(): array
    {
        return [
            'NOTA_VLERA' => fake()->randomFloat(2, 5, 10),
            'NOTA_DAT' => fake()->date(),
            'STU_ID' => 1,
            'PROV_ID' => 1,
        ];
    }
}
