<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        $types = [Document::TYPE_CNH, Document::TYPE_ID, Document::TYPE_PASSPORT];

        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement($types),
            'file_path' => 'documents/' . $this->faker->uuid . '.pdf',
            'expiry_date' => $this->faker->dateTimeBetween('now', '+3 years'),
        ];
    }
}