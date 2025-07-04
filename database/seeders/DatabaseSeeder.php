<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeder untuk user admin
        User::factory()->create([
            'name' => 'AdminKoi',
            'email' => 'adminkoi@mail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('kelompok4'),
            'remember_token' => Str::random(10),
            'role' => 'admin', // ganti is_admin
        ]);

        // Tambahkan ProductSeeder
        $this->call(ProductSeeder::class);
    }
}
