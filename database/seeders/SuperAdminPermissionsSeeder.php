<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // جلب الدور super_admin
        $role = Role::findByName('super_admin');

        // جلب جميع الأذونات
        $permissions = Permission::all();

        // ربط الأذونات بـ super_admin
        $role->syncPermissions($permissions);
    }
}
