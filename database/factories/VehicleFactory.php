<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        $types = ['SUV', 'Sedan', 'Pick-Up', 'Hatchback', 'Convertible'];
        $statuses = ['available', 'in_use', 'maintenance'];
        $fuels = ['gasoline', 'diesel', 'electric', 'hybrid'];

        return [
            'category_id' => Category::factory(),
            'brand' => $this->faker->company(),
            'model' => $this->faker->word(),
            'year' => $this->faker->year(),
            'plate' => strtoupper($this->faker->bothify('??-####-??')),
            'km' => $this->faker->numberBetween(0, 200000),
            'type' => $this->faker->randomElement($types),
            'status' => $this->faker->randomElement($statuses),
            'price_per_day' => $this->faker->randomFloat(2, 5000, 25000),
            'color' => $this->faker->safeColorName(),
            'doors' => $this->faker->randomElement([2, 3, 4, 5]),
            'fuel' => $this->faker->randomElement($fuels),
            'images' => json_encode([
                $this->faker->imageUrl(640, 480, 'car'),
                $this->faker->imageUrl(640, 480, 'car'),
            ]),
        ];
    }
}