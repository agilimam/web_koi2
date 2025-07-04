<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DeliveryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Tambahkan 1 produk yang diperlukan oleh transaksi
        DB::table('products')->insert([
            'kode_produk' => 'PRD001',
            'nama_produk' => 'Produk Dummy',
            'berat' => 100,
            'stok' => 10,
            'harga_satuan' => 50000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    #[Test]
    public function store_menyimpan_data_delivery_dan_update_status_transaksi()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create(['role' => 'admin']);
        $transaksi = Transaksi::factory()->create([
            'status' => 'menunggu pengiriman',
        ]);

        $file = UploadedFile::fake()->image('resi.jpg');

        $response = $this->actingAs($user)->post(route('delivery.store'), [
            'transaction_id' => $transaksi->id,
            'no_resi' => 'ABC123456789',
            'upload_resi' => $file,
        ]);

        $response->assertRedirect(route('adminPesanan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('deliveries', [
            'transaction_id' => $transaksi->id,
            'no_resi' => 'ABC123456789',
        ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaksi->id,
            'status' => 'dikirim',
        ]);
    }

    #[Test]
    public function store_validasi_gagal_jika_field_kosong()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post(route('delivery.store'), []);

        $response->assertInvalid(['transaction_id', 'no_resi']);
    }

    #[Test]
    public function show_image_mengembalikan_gambar_jika_ada()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $imageData = base64_decode('
        /9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAAQABADASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAX/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAG+Af/EABQQAQAAAAAAAAAAAAAAAAAAACD/2gAIAQEAAQUCmP/EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQMBAT8BP//EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQIBAT8BP//EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQEABj8Cf//Z');

        $transaksi = Transaksi::factory()->create([
            'status' => 'menunggu pengiriman',
        ]);

        $delivery = Delivery::create([
            'transaction_id' => $transaksi->id,
            'no_resi' => 'RESI-TEST',
            'upload_resi' => $imageData,
        ]);

        $response = $this->actingAs($user)->get("/delivery/{$delivery->id}/image");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    #[Test]
    public function show_image_mengembalikan_404_jika_tidak_ada_gambar()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $transaksi = Transaksi::factory()->create([
            'status' => 'menunggu pengiriman',
        ]);

        $delivery = Delivery::create([
            'transaction_id' => $transaksi->id,
            'no_resi' => 'NO-IMG',
            'upload_resi' => null,
        ]);

        $response = $this->actingAs($user)->get("/delivery/{$delivery->id}/image");

        $response->assertStatus(404);
    }
}
