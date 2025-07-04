<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\DB;

class AdminPesananControllerTest extends TestCase
{
    use RefreshDatabase;

    private function seedProduct()
    {
        DB::table('products')->insert([
            'kode_produk' => 'PRD001',
            'nama_produk' => 'Dummy Product',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    #[Test]
    public function hanya_admin_yang_bisa_mengakses_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/adminPesanan');

        $response->assertStatus(200);
    }


    #[Test]
    public function user_dilarang_mengakses_index(): void
    {
        $response = $this->get('/adminPesanan');
        $response->assertRedirect('/login');
    }


}
