<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_dapat_melihat_daftar_user_customer()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);
        $nonCustomer = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('user.index'));

        $response->assertStatus(200);
        $response->assertSee($customer1->email);
        $response->assertSee($customer2->email);
        $response->assertDontSee($nonCustomer->email);
    }

    #[Test]
    public function admin_dapat_menghapus_user_customer()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($admin)->delete(route('user.destroy', $customer->id));

        $response->assertRedirect(route('user.index'));
        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
    }

    #[Test]
    public function admin_tidak_dapat_menghapus_user_non_customer()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $anotherAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->delete(route('user.destroy', $anotherAdmin->id));

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('danger', 'Anda tidak dapat menghapus user ini.');
        $this->assertDatabaseHas('users', ['id' => $anotherAdmin->id]);
    }
}
