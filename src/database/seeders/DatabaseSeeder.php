<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeder ini hanya menyiapkan role dan permission sistem.
     * Tidak membuat ulang akun ataupun menghapus data transaksi.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            RoleJobdeskPermissionSeeder::class,
        ]);
    }
}
