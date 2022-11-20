<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ScholarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'phone_number' => 9178781045,
            'id_number' => rand(1000000, 9999999) . '-' . rand(1,2),
            'department' => 'CCSIT',
            'course' => 'BSIT',
            'major' => 'Programming',
            'year_level' => '4th Year',
            'email' => $this->faker->email()
        ];
    }
}
