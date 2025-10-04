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


        $admin->assignRole($role);


        $widgetPermissions = [
            'widget_OrdersStatsOverview',
            'view_chart',
            'widget_orderstuts',
             'widget_LabStats',

        ];

        foreach ($widgetPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }


        $admin->givePermissionTo($widgetPermissions);
    }
}
