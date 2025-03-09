<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CustomPermissionSeeder extends Seeder
{
    public function run()
    {
        // ✅ إنشاء الأذونات الخاصة بالمخططات والطلبات
        $widgetPermissions = [
            'widget_OrdersStatsOverview',
            'widget_orderstuts',
        ];

        foreach ($widgetPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ✅ إعطاء كل الأذونات إلى دور "super_admin"
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $allPermissions = Permission::all();
            $superAdminRole->syncPermissions($allPermissions);
        }
    }
    
}
