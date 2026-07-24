<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocationUserSeeder extends Seeder
{
    public function run(): void
    {
        // ========== LOCATIONS ==========
        $gudangUtama = Location::create([
            'code' => 'GDG-01',
            'name' => 'Gudang Utama Subang',
            'type' => 'warehouse',
            'is_main_source' => true,
            'address' => 'Jl. Raya Pertanian No. 123, Subang, Jawa Barat',
            'phone' => '0260-123456',
            'pic_name' => 'Budi Santoso',
        ]);

        $toko1 = Location::create([
            'code' => 'TKO-01',
            'name' => 'Toko Cabang Pamanukan',
            'type' => 'store',
            'address' => 'Jl. Pasar Baru No. 45, Pamanukan, Subang',
            'phone' => '0260-234567',
            'pic_name' => 'Sari Dewi',
        ]);

        $toko2 = Location::create([
            'code' => 'TKO-02',
            'name' => 'Toko Cabang Pagaden',
            'type' => 'store',
            'address' => 'Jl. Raya Pagaden No. 78, Pagaden, Subang',
            'phone' => '0260-345678',
            'pic_name' => 'Andi Wijaya',
        ]);

        $toko3 = Location::create([
            'code' => 'TKO-03',
            'name' => 'Toko Cabang Ciasem',
            'type' => 'store',
            'address' => 'Jl. Pantai Utara No. 12, Ciasem, Subang',
            'phone' => '0260-456789',
            'pic_name' => 'Rina Kartika',
        ]);

        // ========== USERS ==========
        $password = Hash::make('password');

        // Super Admin
        $superAdmin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@agrosaranatani.co.id',
            'password' => $password,
            'phone' => '081234567890',
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('Super Admin');
        $superAdmin->locations()->attach([$gudangUtama->id, $toko1->id, $toko2->id, $toko3->id]);

        // Owner
        $owner = User::create([
            'name' => 'Haji Ahmad Subandi',
            'email' => 'owner@agrosaranatani.co.id',
            'password' => $password,
            'phone' => '081298765432',
            'email_verified_at' => now(),
        ]);
        $owner->assignRole('Owner');

        // Kepala Gudang
        $kepalaGudang = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@agrosaranatani.co.id',
            'password' => $password,
            'phone' => '081211112222',
            'email_verified_at' => now(),
        ]);
        $kepalaGudang->assignRole('Kepala Gudang');
        $kepalaGudang->locations()->attach([$gudangUtama->id]);

        // Staff Gudang
        $staffGudang = User::create([
            'name' => 'Dedi Kurniawan',
            'email' => 'dedi@agrosaranatani.co.id',
            'password' => $password,
            'phone' => '081233334444',
            'email_verified_at' => now(),
        ]);
        $staffGudang->assignRole('Staff Gudang');
        $staffGudang->locations()->attach([$gudangUtama->id]);

        // Kepala Toko 1
        $kepalaToko1 = User::create([
            'name' => 'Sari Dewi',
            'email' => 'sari@agrosaranatani.co.id',
            'password' => $password,
            'phone' => '081255556666',
            'email_verified_at' => now(),
        ]);
        $kepalaToko1->assignRole('Kepala Toko');
        $kepalaToko1->locations()->attach([$toko1->id]);

        // Staff Toko 1
        $staffToko1 = User::create([
            'name' => 'Maya Siti Rahma',
            'email' => 'maya@agrosaranatani.co.id',
            'password' => $password,
            'phone' => '081277778888',
            'email_verified_at' => now(),
        ]);
        $staffToko1->assignRole('Staff Toko');
        $staffToko1->locations()->attach([$toko1->id]);

        // Kepala Toko 2
        $kepalaToko2 = User::create([
            'name' => 'Andi Wijaya',
            'email' => 'andi@agrosaranatani.co.id',
            'password' => $password,
            'phone' => '081299990000',
            'email_verified_at' => now(),
        ]);
        $kepalaToko2->assignRole('Kepala Toko');
        $kepalaToko2->locations()->attach([$toko2->id]);

        // Staff Toko 2
        $staffToko2 = User::create([
            'name' => 'Rudi Hermawan',
            'email' => 'rudi@agrosaranatani.co.id',
            'password' => $password,
            'phone' => '081200001111',
            'email_verified_at' => now(),
        ]);
        $staffToko2->assignRole('Staff Toko');
        $staffToko2->locations()->attach([$toko2->id]);

        // Kepala Toko 3
        $kepalaToko3 = User::create([
            'name' => 'Rina Kartika',
            'email' => 'rina@agrosaranatani.co.id',
            'password' => $password,
            'phone' => '081244445555',
            'email_verified_at' => now(),
        ]);
        $kepalaToko3->assignRole('Kepala Toko');
        $kepalaToko3->locations()->attach([$toko3->id]);

        // Staff Toko 3
        $staffToko3 = User::create([
            'name' => 'Agus Prasetyo',
            'email' => 'agus@agrosaranatani.co.id',
            'password' => $password,
            'phone' => '081266667777',
            'email_verified_at' => now(),
        ]);
        $staffToko3->assignRole('Staff Toko');
        $staffToko3->locations()->attach([$toko3->id]);
    }
}
