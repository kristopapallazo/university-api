<?php

namespace Database\Factories;

use App\Models\Fature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fature>
 */
class FatureFactory extends Factory
{
    protected $model = Fature::class;

    public function definition(): array
    {
        return [
            'FAT_DAT_LESHIM' => fake()->date(),
            'FAT_SHUMA' => fake()->randomFloat(2, 5000, 50000),
            'FAT_STATUSI' => 'E papaguar',
            'FAT_PERSHKRIM' => 'Tarifë vjetore',
            'STU_ID' => 1,
        ];
    }
}
