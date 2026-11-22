<?php

namespace Database\Seeders;

use App\Enums\PeranPengguna;
use App\Enums\StatusPengguna;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Akun demo hanya untuk development. Tidak dijalankan DatabaseSeeder.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn(
                'UserSeeder dilewati pada environment production.'
            );

            return;
        }

        $users = [
            [
                'email' => 'admin@admin.com',
                'name' => 'Super Admin',
                'nomor_telepon' => '081200000001',
                'role' => PeranPengguna::SuperAdmin->value,
            ],
            [
                'email' => 'petugas@admin.com',
                'name' => 'Petugas Donor Darah',
                'nomor_telepon' => '081200000002',
                'role' => PeranPengguna::Petugas->value,
            ],
            [
                'email' => 'pendonor@donordarah.test',
                'name' => 'Pendonor Pengujian',
                'nomor_telepon' => '081200000003',
                'role' => PeranPengguna::Pendonor->value,
            ],
            [
                'email' => 'pemohon@donordarah.test',
                'name' => 'Yayasan Harapan Sehat',
                'nomor_telepon' => '081200000004',
                'role' => PeranPengguna::PemohonDonor->value,
            ],
        ];

        foreach ($users as $data) {
            $user = User::withoutEvents(
                fn (): User => User::updateOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'nomor_telepon' => $data['nomor_telepon'],
                        'password' => Hash::make('password'),
                        'status' => StatusPengguna::Aktif,
                    ]
                )
            );

            // email_verified_at sengaja tidak dibuat mass-assignable.
            $user->forceFill([
                'email_verified_at' => now(),
            ])->saveQuietly();

            $user->syncRoles([$data['role']]);
        }
    }
}
