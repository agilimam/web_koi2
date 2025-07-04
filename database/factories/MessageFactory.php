<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        $chat = Chat::factory()->create();

        return [
            'chat_id' => $chat->id,
            'sender_id' => $chat->user_id,  // misalnya default dikirim oleh user
            'isi_pesan' => $this->faker->sentence(),
            'created_at' => now(),
        ];
    }
}
