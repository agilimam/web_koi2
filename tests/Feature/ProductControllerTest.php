<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_bisa_menambahkan_produk_baru()
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        $file = UploadedFile::fake()->image('produk.jpg');

        $response = $this->actingAs($admin)->post('/product', [
            'nama_produk' => 'Produk Test',
            'berat' => '500g',
            'stok' => 10,
            'harga_satuan' => 20000,
            'gambar' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Produk berhasil ditambahkan']);
        $this->assertDatabaseHas('products', ['nama_produk' => 'Produk Test']);
    }

    #[Test]
    public function non_admin_tidak_bisa_menambahkan_produk()
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->post('/product', [
            'nama_produk' => 'Produk Test',
            'berat' => '500g',
            'stok' => 10,
            'harga_satuan' => 20000,
        ]);

        // Karena non-admin akan diarahkan (redirect) ke dashboard atau login
        // Maka cek apakah response 302 dan diarahkan ke halaman lain (tidak 403 karena middleware redirect)
        $response->assertRedirect(); // ✅ Sesuai dengan kondisi routes + middleware admin
    }

   #[Test]
public function admin_bisa_mengupdate_produk()
{
    $this->withoutExceptionHandling();

    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::factory()->create(['nama_produk' => 'Lama'])->refresh();

    $this->assertNotNull($product->kode_produk); // validasi kode_produk

    $response = $this->actingAs($admin)->patch("/product/{$product->kode_produk}/adited", [
        'nama_produk' => 'Baru',
        'berat' => '250g',
        'stok' => 5,
        'harga_satuan' => 10000,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['message' => 'Produk berhasil diperbarui!']);
    $this->assertDatabaseHas('products', ['nama_produk' => 'Baru']);
}

#[Test]
public function admin_bisa_menghapus_produk()
{
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::factory()->create()->refresh();

    $response = $this->actingAs($admin)->delete(route('product.destroy', $product));

    $response->assertRedirect(route('product.index'));
    $this->assertDatabaseMissing('products', ['kode_produk' => $product->kode_produk]);
}



    #[Test]
public function bisa_melihat_gambar_produk_jika_ada()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->create([
        'kode_produk' => 'PRD123',
        'gambar' => 'fake_image_data',
    ]);

    $response = $this->get("/produk/{$product->kode_produk}/gambar");
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'image/jpeg');
}

#[Test]
public function return_404_jika_gambar_produk_tidak_ada()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->create([
        'kode_produk' => 'PRD456',
        'gambar' => null,
    ]);

    $response = $this->get("/produk/{$product->kode_produk}/gambar");
    $response->assertStatus(404);
}

}
