<?php

namespace App\Console\Commands;

use App\Enums\StatusPengguna;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class RestorePetugasAccount extends Command
{
    protected $signature = 'petugas:restore';

    protected $description = 'Membuat atau memulihkan akun Petugas Donor Darah tanpa menghapus data lain';

    public function handle(): int
    {
        $email = 'petugas@admin.com';

        if (! $this->input->isInteractive()) {
            $this->error('Jalankan perintah secara interaktif untuk memasukkan password.');

            return self::FAILURE;
        }

        try {
            if (
                ! Schema::hasTable('users')
                || ! Schema::hasTable('roles')
                || ! Schema::hasTable('model_has_roles')
            ) {
                $this->error('Tabel akun atau role belum tersedia. Periksa status migrasi.');

                return self::FAILURE;
            }

            $existingUser = User::query()
                ->where('email', $email)
                ->first();

            if (
                $existingUser !== null
                && ! $this->confirm(
                    'Akun petugas sudah ada. Reset password, aktifkan akun, dan tetapkan role Petugas?',
                    false
                )
            ) {
                $this->warn('Dibatalkan. Tidak ada data yang diubah.');

                return self::FAILURE;
            }

            $password = $this->secret('Password baru (minimal 12 karakter)');
            $confirmation = $this->secret('Konfirmasi password baru');

            if (! is_string($password) || strlen($password) < 12) {
                $this->error('Password harus memiliki minimal 12 karakter.');

                return self::FAILURE;
            }

            if (! is_string($confirmation) || ! hash_equals($password, $confirmation)) {
                $this->error('Konfirmasi password tidak cocok.');

                return self::FAILURE;
            }

            DB::transaction(function () use ($email, $password): void {
                $petugas = User::query()->firstOrNew([
                    'email' => $email,
                ]);

                if (! $petugas->exists) {
                    $petugas->name = 'Petugas Donor Darah';
                    $petugas->nomor_telepon = '081200000002';
                }

                $petugas->password = Hash::make($password);
                $petugas->status = StatusPengguna::Aktif;
                $petugas->email_verified_at = now();

                // Hindari mengirim email verifikasi saat pemulihan lewat CLI.
                $petugas->saveQuietly();

                $role = Role::query()->firstOrCreate([
                    'name' => 'petugas',
                    'guard_name' => 'web',
                ]);

                $petugas->syncRoles([$role]);
            });

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $this->info("Akun Petugas siap digunakan: {$email}");
            $this->line('Login melalui /admin/login dengan password baru yang Anda buat.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->error('Pemulihan gagal. Periksa log Laravel dan koneksi database.');

            return self::FAILURE;
        }
    }
}
