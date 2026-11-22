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

class RestoreAdminAccount extends Command
{
    protected $signature = 'admin:restore
                            {--email=admin@admin.com : Email akun Super Admin}';

    protected $description = 'Buat atau pulihkan akun Super Admin tanpa menghapus data yang ada';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->option('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Format email tidak valid.');

            return self::FAILURE;
        }

        if (! $this->input->isInteractive()) {
            $this->error('Jalankan perintah secara interaktif untuk memasukkan password dengan aman.');

            return self::FAILURE;
        }

        try {
            if (
                ! Schema::hasTable('users')
                || ! Schema::hasTable('roles')
                || ! Schema::hasTable('model_has_roles')
            ) {
                $this->error('Tabel autentikasi belum lengkap. Jalankan migrasi terlebih dahulu.');

                return self::FAILURE;
            }

            $existingUser = User::query()
                ->where('email', $email)
                ->first();

            if (
                $existingUser instanceof User
                && ! $this->confirm(
                    'Akun ini sudah ada. Reset password dan tetapkan role Super Admin?',
                    false
                )
            ) {
                $this->warn('Pemulihan dibatalkan. Tidak ada data yang berubah.');

                return self::SUCCESS;
            }

            $password = $this->secret('Password baru (minimal 12 karakter)');
            $confirmation = $this->secret('Konfirmasi password baru');

            if (! is_string($password) || strlen($password) < 12) {
                $this->error('Password minimal 12 karakter.');

                return self::FAILURE;
            }

            if (! is_string($confirmation) || ! hash_equals($password, $confirmation)) {
                $this->error('Konfirmasi password tidak cocok.');

                return self::FAILURE;
            }

            DB::transaction(function () use ($email, $password): void {
                $admin = User::query()->firstOrNew([
                    'email' => $email,
                ]);

                if (! $admin->exists) {
                    $admin->name = 'Super Admin';
                }

                $admin->password = Hash::make($password);
                $admin->status = StatusPengguna::Aktif;
                $admin->email_verified_at = now();

                // Tidak kirim email verifikasi saat pemulihan akun via CLI.
                $admin->saveQuietly();

                $role = Role::query()->firstOrCreate([
                    'name' => 'super_admin',
                    'guard_name' => 'web',
                ]);

                // Akun yang dipulihkan hanya memiliki peran Super Admin.
                $admin->syncRoles([$role]);
            });

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $this->info("Akun Super Admin siap digunakan: {$email}");
            $this->line('Buka /admin/login lalu masuk dengan password yang baru Anda tentukan.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->error('Pemulihan gagal. Periksa koneksi database, migrasi, dan log Laravel.');

            return self::FAILURE;
        }
    }
}
