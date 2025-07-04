<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class TransaksiControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_bisa_menambahkan_produk_ke_keranjang()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'kode_produk' => 'PRD001',
            'stok' => 10,
            'harga_satuan' => 10000,
        ]);

        $response = $this->actingAs($user)->post('/transaction', [
            'kode_produk' => $product->kode_produk,
            'qty' => 2,
        ]);

        $response->assertRedirect('/cart');
        $this->assertDatabaseHas('transactions', [
            'kode_produk' => $product->kode_produk,
            'qty' => 2,
        ]);
    }

    #[Test]
    public function user_tidak_bisa_menambahkan_qty_melebihi_stok()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'kode_produk' => 'PRD002',
            'stok' => 5,
        ]);

        $response = $this->actingAs($user)->post('/transaction', [
            'kode_produk' => $product->kode_produk,
            'qty' => 10,
        ]);

        $response->assertSessionHasErrors(['qty']);
    }

    #[Test]
    public function user_bisa_menghapus_transaksi_yang_belum_dibayar()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'kode_produk' => 'PRD003',
            'stok' => 10,
        ]);

        $transaksi = Transaksi::create([
            'user_id' => $user->id,
            'kode_produk' => $product->kode_produk,
            'nama_produk' => $product->nama_produk,
            'berat' => $product->berat,
            'qty' => 1,
            'total_harga' => 10000,
            'status' => 'belum dibayar',
        ]);

        $response = $this->actingAs($user)->delete("/cart/{$transaksi->id}");
        $response->assertRedirect('/cart');
        $this->assertDatabaseMissing('transactions', ['id' => $transaksi->id]);
    }

    #[Test]
    public function user_bisa_mengirim_transaksi_menunggu_konfirmasi()
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'kode_produk' => 'PRD004',
        ]);

        // Buat transaksi belum dibayar
        $transaksi = Transaksi::create([
            'user_id' => $user->id,
            'kode_produk' => $product->kode_produk,
            'nama_produk' => $product->nama_produk,
            'berat' => $product->berat,
            'qty' => 1,
            'total_harga' => 10000,
            'status' => 'belum dibayar',
        ]);

        $bukti = UploadedFile::fake()->image('bukti.jpg');

        $response = $this->actingAs($user)->post('/listproduct/savetransaksi', [
            'alamat' => 'Jalan Testing',
            'no_hp' => '08123456789',
            'bukti_transaksi' => $bukti,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'id' => $transaksi->id,
            'status' => 'menunggu pengiriman',
        ]);
    }

    #[Test]
    public function user_bisa_melihat_riwayat_transaksi()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['kode_produk' => 'PRD005']);

        Transaksi::create([
            'user_id' => $user->id,
            'kode_produk' => $product->kode_produk,
            'nama_produk' => $product->nama_produk,
            'berat' => $product->berat,
            'qty' => 1,
            'total_harga' => 10000,
            'status' => 'selesai',
        ]);

        $response = $this->actingAs($user)->get('/history');
        $response->assertStatus(200);
        $response->assertSee('selesai');
    }

    #[Test]
    public function user_bisa_menandai_transaksi_selesai()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['kode_produk' => 'PRD006']);

        $transaksi = Transaksi::create([
            'user_id' => $user->id,
            'kode_produk' => $product->kode_produk,
            'nama_produk' => $product->nama_produk,
            'berat' => $product->berat,
            'qty' => 1,
            'total_harga' => 10000,
            'status' => 'dikirim',
        ]);

        $response = $this->actingAs($user)->patch("/transaksi/{$transaksi->id}/selesai");

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('transactions', [
            'id' => $transaksi->id,
            'status' => 'selesai',
        ]);
    }
}
