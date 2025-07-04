<?php

namespace Tests\Feature;

use App\Models\Transaksi;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function hanya_user_login_yang_bisa_melihat_cart(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/cart');

        $response->assertStatus(200);
        $response->assertViewIs('cart.index');
    }

    #[Test]
    public function guest_dilarang_melihat_cart(): void
    {
        $response = $this->get('/cart');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function cart_hanya_menampilkan_transaksi_user_dengan_status_belum_dibayar(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Transaksi milik user, status belum dibayar ✅
        $transaksiUser = Transaksi::factory()->create([
            'user_id' => $user->id,
            'kode_produk' => $product->kode_produk,
            'status' => 'belum dibayar'
        ]);

        // Transaksi milik user, status selain belum dibayar ❌
        Transaksi::factory()->create([
            'user_id' => $user->id,
            'kode_produk' => $product->kode_produk,
            'status' => 'dikirim'
        ]);

        // Transaksi milik user lain ❌
        Transaksi::factory()->create([
            'user_id' => User::factory()->create()->id,
            'kode_produk' => $product->kode_produk,
            'status' => 'belum dibayar'
        ]);

        $response = $this->actingAs($user)->get('/cart');

        $response->assertStatus(200);
        $response->assertViewIs('cart.index');
        $response->assertViewHas('carts', function ($carts) use ($transaksiUser) {
            // Cuma transaksi yang benar yang tampil
            return $carts->contains('id', $transaksiUser->id) && $carts->count() === 1;
        });
    }
}
