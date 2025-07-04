<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'kode_produk' => $this->faker->unique()->numerify('PRD###'),
            'nama_produk' => $this->faker->words(2, true),
            'berat' => $this->faker->randomElement(['100 gram', '200 gram', '300 gram']),
            'stok' => $this->faker->numberBetween(10, 100),
            'harga_satuan' => $this->faker->randomFloat(2, 1000, 50000),
            'gambar' => null,  // atau isi dengan dummy binary jika mau
        ];
    }
}
