<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'gender' => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'birth_date' => fake()->date(),
            'birth_place' => fake()->city(),
            'death_date' => null,
            'death_place' => null,
            'phone_number' => fake()->phoneNumber(),
            'biography' => fake()->paragraph(),
            'is_key_figure' => false,
            // 'father_id' => null, // DIHAPUS
            // 'mother_id' => null, // DIHAPUS
        ];
    }
}