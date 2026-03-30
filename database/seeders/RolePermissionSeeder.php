<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect([
            'manage users',
            'manage weekly menus',
            'manage daily menus',
            'manage menu options',
            'view reports',
            'select menu option',
            'view own selections',
        ])->map(fn (string $name) => Permission::findOrCreate($name, 'web'));

        $superAdmin = Role::findOrCreate('SuperAdmin', 'web');
        $supplier = Role::findOrCreate('Supplier', 'web');
        $worker = Role::findOrCreate('Worker', 'web');

        $superAdmin->syncPermissions($permissions);
        $supplier->syncPermissions([
            'manage weekly menus',
            'manage daily menus',
            'manage menu options',
            'view reports',
        ]);
        $worker->syncPermissions([
            'select menu option',
            'view own selections',
        ]);
    }
}
