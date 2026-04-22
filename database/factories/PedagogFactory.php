<?php

namespace Database\Factories;

use App\Models\Pedagog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pedagog>
 */
class PedagogFactory extends Factory
{
    protected $model = Pedagog::class;

    public function definition(): array
    {
        return [
            'PED_EM' => fake()->firstName(),
            'PED_MB' => fake()->lastName(),
            'PED_GJINI' => fake()->randomElement(['M', 'F']),
            'PED_TITULLI' => 'Msc.',
            'PED_EMAIL' => fake()->unique()->userName() . '@uamd.edu.al',
            'PED_TEL' => fake()->numerify('06########'),
            'DEP_ID' => 1,
        ];
    }
}
