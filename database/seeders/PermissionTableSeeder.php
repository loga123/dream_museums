<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'group-store',
            'group-update',
            'group-delete',
            'marker-store',
            'marker-update',
            'marker-delete',
            'city-store',
            'city-update',
            'city-delete',
            'user-delete',
            'user-edit',
            'user-direct-permission',
            'user-reset-password',

        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
