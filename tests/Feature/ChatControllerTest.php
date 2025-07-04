<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;
    #[Test]
    public function user_hanya_bisa_melihat_chat_sendiri()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'customer']);
        $user2 = User::factory()->create(['role' => 'customer']);

        Chat::factory()->create(['user_id' => $user->id, 'admin_id' => $admin->id]);
        Chat::factory()->create(['user_id' => $user2->id, 'admin_id' => $admin->id]);

        $response = $this->actingAs($user)->get('/chat');  

        $response->assertStatus(302); 
    }


    #[Test]
    public function admin_tidak_boleh_memulai_chat()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/chat');  

        $response->assertStatus(403);
    }

    #[Test]
    public function user_dapat_mengirim_pesan_ke_chat()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'customer']);
        $chat = Chat::factory()->create(['user_id' => $user->id, 'admin_id' => $admin->id]);

        $response = $this->actingAs($user)->post("/chat/{$chat->id}/send", [
            'isi_pesan' => 'Pesan dari user',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('messages', [
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'isi_pesan' => 'Pesan dari user',
        ]);
    }
}
