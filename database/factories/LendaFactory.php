<?php

namespace Database\Factories;

use App\Models\Lenda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lenda>
 */
class LendaFactory extends Factory
{
    protected $model = Lenda::class;

    public function definition(): array
    {
        return [
            'LEND_EMER' => fake()->unique()->words(3, true),
            'LEND_KOD' => fake()->unique()->bothify('L###'),
            'DEP_ID' => 1,
        ];
    }
}
