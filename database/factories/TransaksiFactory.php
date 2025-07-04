<?php

namespace Database\Factories;

use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransaksiFactory extends Factory
{
    protected $model = Transaksi::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),   // Pastikan selalu ada user_id
            'kode_produk' => 'PRD001',      // Sesuaikan dengan produk yg ada di table products (silakan tambahkan manual 1 dummy product di tabel products saat testing)
            'qty' => 1,
            'total_harga' => 10000,
            'status' => 'selesai',
            'bukti_transaksi' => 'fake_image_data',
        ];
    }
}
