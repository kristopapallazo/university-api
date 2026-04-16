<?php

namespace Database\Factories;

use App\Models\Provim;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provim>
 */
class ProvimFactory extends Factory
{
    protected $model = Provim::class;

    public function definition(): array
    {
        return [
            'TIP_EMER' => 'Final',
            'DAT_PROVIM' => fake()->date(),
            'SEK_ID' => 1,
        ];
    }
}
