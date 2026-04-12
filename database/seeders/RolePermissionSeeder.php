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

        $viewWorkshops = Permission::firstOrCreate(
            ['name' => 'workshops.view', 'guard_name' => 'web'],
        );
        $manageWorkshops = Permission::firstOrCreate(
            ['name' => 'workshops.manage', 'guard_name' => 'web'],
        );

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $employee = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        $admin->syncPermissions([$viewWorkshops, $manageWorkshops]);
        $employee->syncPermissions([$viewWorkshops]);
    }
}
