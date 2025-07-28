<?php

namespace Database\Factories;

use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class MaintenanceFactory extends Factory
{
    protected $model = Maintenance::class;

    public function definition(): array
    {
        $statuses = [
            Maintenance::STATUS_SCHEDULED,
            Maintenance::STATUS_IN_PROGRESS,
            Maintenance::STATUS_COMPLETED,
        ];

        $start = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $end = (clone $start)->modify('+' . rand(1, 5) . ' days');

        return [
            'vehicle_id' => Vehicle::factory(),
            'description' => $this->faker->sentence(),
            'start_date' => $start,
            'end_date' => $end,
            'cost' => $this->faker->randomFloat(2, 10000, 100000),
            'status' => $this->faker->randomElement($statuses),
        ];
    }
}