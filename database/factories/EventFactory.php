<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->jobTitle(),
            'event_start' => now()->format('Y-m-d'),
            'event_end' => now()->addMonth()->format('Y-m-d'),
            'details' => $this->faker->realText()
        ];
    }
}
