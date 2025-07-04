<?php

namespace Tests\Feature;

use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminTransaksiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // semua kolom produk yang dibutuhkan
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
    public function hanya_admin_yang_bisa_mengakses_index()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/adminTransaksi');

        $response->assertStatus(200);
        $response->assertViewIs('adminTransaksi.index');
    }

    
    #[Test]
    public function guest_dilarang_mengakses_index()
    {
        $response = $this->get('/adminTransaksi');

        $response->assertRedirect('/login');
    }



    #[Test]
    public function return_404_jika_bukti_transaksi_null()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $transaksi = Transaksi::factory()->create([
            'bukti_transaksi' => null,
            'kode_produk' => 'PRD001',  // ✅ Foreign key valid
        ]);

        $response = $this->actingAs($admin)->get("/adminTransaksi/{$transaksi->id}/image");

        $response->assertStatus(404);
    }

}
