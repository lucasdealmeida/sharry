<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title'      => $this->faker->word,
            'content'    => $this->faker->paragraph,
            'gps_lat'    => $this->faker->latitude,
            'gps_lng'    => $this->faker->longitude,
            'valid_from' => $this->faker->dateTime,
            'valid_to'   => $this->faker->dateTime,
            'user_id'    => User::factory(),
        ];
    }
}
