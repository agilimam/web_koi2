<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatFactory extends Factory
{
    protected $model = Chat::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'admin_id' => User::factory()->create(['role' => 'admin'])->id,
            'created_at' => now(),
        ];
    }
}
