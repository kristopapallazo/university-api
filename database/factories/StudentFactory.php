<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'STU_EM' => fake()->firstName(),
            'STU_MB' => fake()->lastName(),
            'STU_ATESI' => fake()->firstNameMale(),
            'STU_GJINI' => fake()->randomElement(['M', 'F']),
            'STU_DTL' => fake()->date('Y-m-d', '2005-01-01'),
            'STU_NR_MATRIKULL' => fake()->unique()->numerify('2021######'),
            'STU_EMAIL' => fake()->unique()->userName() . '@std.uamd.edu.al',
            'STU_TEL' => fake()->numerify('06########'),
            'STU_DAT_REGJISTRIM' => fake()->date(),
            'STU_STATUS' => 'Aktiv',
            'DHOM_ID' => null,
        ];
    }
}
