<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
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
}
