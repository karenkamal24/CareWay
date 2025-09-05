<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use BezhanSalleh\FilamentShield\Support\Utils;

class AdminSeeder extends Seeder
{
    public function run(): void
    {

        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'user_type' => 'admin',
            ]
        );


        $superAdminRoleName = Utils::getSuperAdminName(); // usually 'super_admin'


        $role = Role::firstOrCreate([
            'name' => $superAdminRoleName,
            'guard_name' => 'web',
        ]);

        // 4. ربط الدور بالمستخدم
        $admin->assignRole($role);

        // 5. إنشاء صلاحيات الـ Widgets يدويًا
        $widgetPermissions = [
            'widget_OrdersStatsOverview',
            'view_chart',
            'widget_orderstuts',
        ];

        foreach ($widgetPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // 6. إعطاء صلاحيات الـ widgets للمستخدم admin
        $admin->givePermissionTo($widgetPermissions);
    }
}
