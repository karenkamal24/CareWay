<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        // الأذونات الأساسية
        $permissions = [
            'view_any_department',
            'view_department',
            'create_department',
            'update_department',
            'delete_department',
            'view_any_permission',
            'view_permission',
            'create_permission',
            'update_permission',
            'delete_permission',
            'delete_any_permission',
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            'delete_any_user',
            'restore_user',
            'force_delete_user',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // أذونات الفئات (Category)
        $categoryPermissions = [
            'view_any_pharmacy::category',
            'view_pharmacy::category',
            'create_pharmacy::category',
            'update_pharmacy::category',
            'delete_pharmacy::category',
            'delete_any_pharmacy::category',
            'force_delete_pharmacy::category',
            'force_delete_any_pharmacy::category',
            'restore_pharmacy::category',
            'restore_any_pharmacy::category',
            'replicate_pharmacy::category',
            'reorder_pharmacy::category',
        ];

        foreach ($categoryPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // أذونات إعدادات التوصيل (Delivery Setting)
        $deliverySettingPermissions = [
            'view_any_pharmacy::delivery::setting',
            'view_pharmacy::delivery::setting',
            'create_pharmacy::delivery::setting',
            'update_pharmacy::delivery::setting',
            'delete_pharmacy::delivery::setting',
            'delete_any_pharmacy::delivery::setting',
            'force_delete_pharmacy::delivery::setting',
            'force_delete_any_pharmacy::delivery::setting',
            'restore_pharmacy::delivery::setting',
            'restore_any_pharmacy::delivery::setting',
            'replicate_pharmacy::delivery::setting',
            'reorder_pharmacy::delivery::setting',
        ];

        foreach ($deliverySettingPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // أذونات المناطق (Delivery Zone)
        $deliveryZonePermissions = [
            'view_any_pharmacy::delivery::zone',
            'view_pharmacy::delivery::zone',
            'create_pharmacy::delivery::zone',
            'update_pharmacy::delivery::zone',
            'delete_pharmacy::delivery::zone',
            'delete_any_pharmacy::delivery::zone',
            'force_delete_pharmacy::delivery::zone',
            'force_delete_any_pharmacy::delivery::zone',
            'restore_pharmacy::delivery::zone',
            'restore_any_pharmacy::delivery::zone',
            'replicate_pharmacy::delivery::zone',
            'reorder_pharmacy::delivery::zone',
        ];

        foreach ($deliveryZonePermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // أذونات الأطباء (Doctor)
        $doctorPermissions = [
            'view_any_doctor',
            'view_doctor',
            'create_doctor',
            'update_doctor',
            'delete_doctor',
            'delete_any_doctor',
            'force_delete_doctor',
            'force_delete_any_doctor',
            'restore_doctor',
            'restore_any_doctor',
            'replicate_doctor',
            'reorder_doctor',
        ];

        foreach ($doctorPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // أذونات الطلبات (Order)
        $orderPermissions = [
            'view_any_pharmacy::order',
            'view_pharmacy::order',
            'create_pharmacy::order',
            'update_pharmacy::order',
            'delete_pharmacy::order',
            'delete_any_pharmacy::order',
            'force_delete_pharmacy::order',
            'force_delete_any_pharmacy::order',
            'restore_pharmacy::order',
            'restore_any_pharmacy::order',
            'replicate_pharmacy::order',
            'reorder_pharmacy::order',
        ];

        foreach ($orderPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // أذونات المنتجات (Product)
        $productPermissions = [
            'view_any_pharmacy::product',
            'view_pharmacy::product',
            'create_pharmacy::product',
            'update_pharmacy::product',
            'delete_pharmacy::product',
            'delete_any_pharmacy::product',
            'force_delete_pharmacy::product',
            'force_delete_any_pharmacy::product',
            'restore_pharmacy::product',
            'restore_any_pharmacy::product',
            'replicate_pharmacy::product',
            'reorder_pharmacy::product',
        ];

        foreach ($productPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // أذونات الأدوات (Widget)
        $widgetPermissions = [
            'widget_OrdersStatsOverview',
            'widget_chart',
            'widget_orderstuts',
        ];

        foreach ($widgetPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ربط الأذونات بالدور (Role)
        $role = Role::findByName('super_admin');
        $permissions = Permission::all();
        $role->syncPermissions($permissions);
    }
}
