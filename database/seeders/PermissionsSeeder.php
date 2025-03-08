<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        
        $permissionsByGroup = [
            'Departments' => [
                'view_any_department',
                'view_department',
                'create_department',
                'update_department',
                'delete_department',
                'delete_any_department',
                'force_delete_department',
                'force_delete_any_department',
                'restore_department',
                'restore_any_department',
                'replicate_department',
                'reorder_department',


            ],
            'DeliveryZone'=>[
                'delivery_zone_view_any',
                'delivery_zone_view',
                'delivery_zone_create',
                'delivery_zone_update',
                'delivery_zone_delete',
                'delivery_zone_delete_any',
                'delivery_zone_restore',
                'delivery_zone_force_delete',
                'delivery_zone_restore',
                'delivery_zone_restore_any',
                'delivery_zone_replicate',
                'delivery_zone_reorder'
            ],

            'Users' => [
               'view_any_user',
               'view_user',
               'create_user',
               'update_user',
                'delete_user',
                'delete_any_user',
                'force_delete_user',
                'ForceDeleteAny',
                'Restore',
                'RestoreAny',
                'Replicate',
                'Reorder',

            ],
        
            'Deliveries' => [
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
                'reorder_pharmacy::delivery::setting'

            ],
            'Doctors' => [
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
            ],
            'Permissions' => [
                'view_any_permission', 
                'view_permission',
                'create_permission',
                'update_permission',
                'delete_permission',
                'delete_any_permission',
                'force_delete_permission',
                'force_delete_any_permission',
                'restore_permission',
                'restore_any_permission',
                'replicate_permission',
                'reorder_permission',],

            'Orders' => [
                'view_any_order',
                'view_order',
                'create_order',
                'update_order',
                'delete_order',
                'delete_any_order',
                'force_delete_order',
                'force_delete_any_order',
                'restore_order',
                'restore_any_order',
                'replicate_order',
                'reorder_order',
            ],
            'Products' => [
               'view_any_product',
                'view_product',
                'create_product',
                'update_product',
                'delete_product',
                'delete_any_product',
                'force_delete_product',
                'force_delete_any_product',
                'restore_product',
                'restore_any_product',
                'replicate_product',
                'reorder_product',
            ],
            'Categories' => [
                'category_view_any',
                'category_view',
                'category_create',
                'category_update',
                'category_delete',
                'category_delete_any',
                'category_force_delete',
                'category_force_delete_any',
                'category_restore',
                'category_restore_any',
                'category_replicate',
                'category_reorder'
            ],
            'Roles' => [
                'view_any_role',
                'view_role',
                'create_role',
                'update_role',
                'delete_role',
                'delete_any_role',
                'force_delete_role',
                'force_delete_any_role',
                'restore_role',
                'restore_any_role',
                'replicate_role',
                'reorder_role',
            ],
            'Widgets' => [
                'widget_OrdersStatsOverview',
                'view_chart',
                'widget_orderstuts',
            ],
        ];

      
        foreach ($permissionsByGroup as $group => $permissions) {
            foreach ($permissions as $permission) {
                Permission::updateOrCreate(
                    ['name' => $permission, 'guard_name' => 'web'],
                    ['group' => $group]
                );
            }
        }

       
        $role = Role::where('name', 'super_admin')->first();
        if ($role) {
            $role->syncPermissions(Permission::all());
        }
    }
}
