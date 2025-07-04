<?php

namespace Tests\Feature;

use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Insert dummy product untuk FK kode_produk
        DB::table('products')->insert([
            'kode_produk' => 'PRD001',
            'nama_produk' => 'Dummy Product',
            'berat' => 100,
            'stok' => 50,
            'harga_satuan' => 10000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    #[Test]
    public function admin_diarahkan_ke_dashboard_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    #[Test]
    public function customer_melihat_transaksi_yang_menunggu_pengiriman_atau_dikirim()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        // Transaksi yang akan tampil
        Transaksi::factory()->create([
            'user_id' => $customer->id,
            'status' => 'menunggu pengiriman',
            'kode_produk' => 'PRD001',
        ]);

        Transaksi::factory()->create([
            'user_id' => $customer->id,
            'status' => 'dikirim',
            'kode_produk' => 'PRD001',
        ]);

        // Transaksi lain yang tidak boleh tampil
        Transaksi::factory()->create([
            'user_id' => $customer->id,
            'status' => 'selesai',
            'kode_produk' => 'PRD001',
        ]);

        $response = $this->actingAs($customer)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('customer.dashboard');
        $response->assertViewHas('transaksis', function ($transaksis) {
            return $transaksis->count() === 2 &&
                   $transaksis->every(fn($t) => in_array($t->status, ['menunggu pengiriman', 'dikirim']));
        });
    }

    #[Test]
    public function guest_diarahkan_ke_login()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }
}
