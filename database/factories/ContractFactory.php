<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'pdf_path' => 'contracts/' . $this->faker->uuid . '.pdf', // caminho fictício
            'generated_at' => Carbon::now(),
        ];
    }
}