<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'date' => now()->addDays(10),
            'venue' => $this->faker->address,
            'rows' => 5,
            'columns' => 5,
            'status' => 'published',
            'price' => 100.00,
        ];
    }
} 