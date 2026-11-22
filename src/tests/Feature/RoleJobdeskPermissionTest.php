<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LokasiDonor;
use Database\Seeders\RoleJobdeskPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoleJobdeskPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RoleJobdeskPermissionSeeder::class);
    }

    #[Test]
    public function staff_dapat_mengelola_operasional_tetapi_tidak_dapat_menghapus(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('petugas');

        $this->assertTrue($staff->can('view_any_lokasi::donor'));
        $this->assertTrue($staff->can('create_lokasi::donor'));
        $this->assertTrue($staff->can('update_pendaftaran::donor'));
        $this->assertFalse($staff->can('delete_lokasi::donor'));
        $this->assertFalse($staff->can('delete_any_pendaftaran::donor'));
        $staff->givePermissionTo('delete_lokasi::donor');
        $this->assertFalse($staff->can('delete_lokasi::donor'));
        $this->assertFalse(Gate::forUser($staff)->allows('delete', new LokasiDonor()));
    }

    #[Test]
    public function staff_hanya_dapat_melihat_akun_dan_tidak_dapat_mengelola_role(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('petugas');

        $this->assertTrue($staff->can('view_any_user'));
        $this->assertFalse($staff->can('create_user'));
        $this->assertFalse($staff->can('update_user'));
        $this->assertFalse($staff->can('view_any_role'));
        $staff->givePermissionTo('create_role');
        $this->assertFalse($staff->can('create_role'));
        $this->assertFalse(Gate::forUser($staff)->allows('create', Role::class));
        $this->assertFalse(Gate::forUser($staff)->allows('update', User::factory()->create()));
    }

    #[Test]
    public function super_admin_dapat_mengelola_pengguna_dan_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->assertTrue($admin->can('create_user'));
        $this->assertTrue($admin->can('update_role'));
        $this->assertTrue($admin->can('delete_lokasi::donor'));
        $this->assertTrue(Gate::forUser($admin)->allows('create', User::class));
    }

    #[Test]
    public function seeder_dapat_diulang_tanpa_menambah_permission_duplikat(): void
    {
        $count = \Spatie\Permission\Models\Permission::query()->count();
        $this->seed(RoleJobdeskPermissionSeeder::class);

        $this->assertSame($count, \Spatie\Permission\Models\Permission::query()->count());
    }
}
