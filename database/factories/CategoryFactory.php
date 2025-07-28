<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $statuses = [Category::STATUS_ACTIVE, Category::STATUS_INACTIVE];

        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'image' => $this->faker->imageUrl(640, 480, 'cars', true, 'Category'), // ou null
            'status' => $this->faker->randomElement($statuses),
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }
}