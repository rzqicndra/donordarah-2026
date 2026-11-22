<?php

namespace Database\Seeders;

use App\Support\JobdeskAccess;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleJobdeskPermissionSeeder extends Seeder
{
    private const GUARD = 'web';

    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        foreach (JobdeskAccess::managedPermissions() as $name) {
            Permission::findOrCreate($name, self::GUARD);
        }

        $superAdmin = Role::findOrCreate('super_admin', self::GUARD);
        $petugas = Role::findOrCreate('petugas', self::GUARD);

        // Super Admin tetap memiliki izin lain dari plugin/fitur yang sudah terpasang.
        $superAdmin->givePermissionTo(
            Permission::query()
                ->where('guard_name', self::GUARD)
                ->get()
        );

        // Sinkronisasi sengaja mengganti permission ROLE petugas yang lama.
        // Tidak mengubah akun, password, role pengguna, atau data donor.
        $petugas->syncPermissions(JobdeskAccess::staffPermissions());

        $registrar->forgetCachedPermissions();
    }
}
