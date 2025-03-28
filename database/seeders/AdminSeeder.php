<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminSeeder extends Seeder
{
    public function run()
    {
       
        $admin = User::firstOrCreate(
            ['email' => 'Admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password123'),
                'user_type' => 'admin',
            ]
        );

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

       
        $permissions = Permission::pluck('name')->toArray();
        $role->syncPermissions($permissions);

        $admin->assignRole($role);
    }
}
