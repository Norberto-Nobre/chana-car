<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 days', '+7 days');
        $end = (clone $start)->modify('+' . rand(1, 5) . ' days');

        $statuses = [
            Booking::STATUS_PENDING,
            Booking::STATUS_APPROVED,
            Booking::STATUS_REJECTED,
            Booking::STATUS_RETURNED,
            Booking::STATUS_EXPIRED,
        ];

        $paymentMethods = [
            Booking::PAYMENT_MULTICAIXA,
            Booking::PAYMENT_TRANSFER,
            Booking::PAYMENT_CASH,
        ];

        $paymentStatuses = [
            Booking::PAYMENT_STATUS_PAID,
            Booking::PAYMENT_STATUS_PENDING,
        ];

        return [
            'user_id' => User::factory(),
            'vehicle_id' => Vehicle::factory(),
            'start_date' => $start,
            'end_date' => $end,
            'pickup_date' => null, // ou: $this->faker->boolean ? Carbon::parse($start)->addHours(2) : null,
            'return_date' => null,
            'total' => $this->faker->randomFloat(2, 5000, 50000),
            'status' => $this->faker->randomElement($statuses),
            'payment_method' => $this->faker->randomElement($paymentMethods),
            'payment_status' => $this->faker->randomElement($paymentStatuses),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}