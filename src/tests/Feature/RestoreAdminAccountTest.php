<?php

namespace Tests\Feature;

use App\Enums\StatusPengguna;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RestoreAdminAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    #[Test]
    public function dapat_membuat_akun_super_admin_terverifikasi(): void
    {
        $password = 'RahasiaAdmin#2026';

        $this->artisan('admin:restore')
            ->expectsQuestion('Password baru (minimal 12 karakter)', $password)
            ->expectsQuestion('Konfirmasi password baru', $password)
            ->assertSuccessful();

        $admin = User::query()
            ->where('email', 'admin@admin.com')
            ->firstOrFail();

        $this->assertSame(StatusPengguna::Aktif, $admin->status);
        $this->assertTrue($admin->hasVerifiedEmail());
        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertTrue(Hash::check($password, $admin->password));
        Notification::assertNothingSent();
    }

    #[Test]
    public function dapat_memulihkan_akun_tanpa_membuat_duplikat(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@admin.com',
            'email_verified_at' => null,
            'status' => StatusPengguna::Ditangguhkan,
        ]);

        $password = 'PasswordBaru#2026';

        $this->artisan('admin:restore')
            ->expectsConfirmation(
                'Akun ini sudah ada. Reset password dan tetapkan role Super Admin?',
                'yes'
            )
            ->expectsQuestion('Password baru (minimal 12 karakter)', $password)
            ->expectsQuestion('Konfirmasi password baru', $password)
            ->assertSuccessful();

        $admin->refresh();

        $this->assertSame(1, User::query()->where('email', 'admin@admin.com')->count());
        $this->assertSame(StatusPengguna::Aktif, $admin->status);
        $this->assertTrue($admin->hasVerifiedEmail());
        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertTrue(Hash::check($password, $admin->password));
    }

    #[Test]
    public function password_yang_tidak_cocok_tidak_membuat_akun(): void
    {
        $this->artisan('admin:restore')
            ->expectsQuestion('Password baru (minimal 12 karakter)', 'PasswordAman#2026')
            ->expectsQuestion('Konfirmasi password baru', 'Berbeda#2026')
            ->assertFailed();

        $this->assertDatabaseMissing('users', [
            'email' => 'admin@admin.com',
        ]);
    }
}
