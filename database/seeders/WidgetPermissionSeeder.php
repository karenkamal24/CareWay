<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class WidgetPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'widget_OrdersStatsOverview',
            'view_chart',
            'widget_orderstuts',
        ];

        // إنشاء الصلاحيات إن لم تكن موجودة
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        // جلب المستخدم admin
        $admin = User::where('email', 'admin@gmail.com')->first();

        // إعطاء الصلاحيات للمستخدم مباشرة (أو للـ role لو تفضلين)
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }
    }
}
