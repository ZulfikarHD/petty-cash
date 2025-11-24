<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management permissions
            'manage-users',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',

            // Transaction management permissions
            'manage-transactions',
            'view-transactions',
            'create-transactions',
            'edit-transactions',
            'delete-transactions',
            'approve-transactions',

            // Reports permissions
            'view-reports',
            'export-reports',

            // Settings permissions
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $accountant = Role::firstOrCreate(['name' => 'Accountant', 'guard_name' => 'web']);
        $cashier = Role::firstOrCreate(['name' => 'Cashier', 'guard_name' => 'web']);
        $requester = Role::firstOrCreate(['name' => 'Requester', 'guard_name' => 'web']);

        // Assign permissions to Admin (full access)
        $admin->givePermissionTo(Permission::all());

        // Assign permissions to Accountant
        $accountant->givePermissionTo([
            'view-users',
            'manage-transactions',
            'view-transactions',
            'create-transactions',
            'edit-transactions',
            'delete-transactions',
            'approve-transactions',
            'view-reports',
            'export-reports',
        ]);

        // Assign permissions to Cashier
        $cashier->givePermissionTo([
            'view-users',
            'manage-transactions',
            'view-transactions',
            'create-transactions',
            'edit-transactions',
            'delete-transactions',
        ]);

        // Assign permissions to Requester
        $requester->givePermissionTo([
            'view-transactions',
            'create-transactions',
        ]);
    }
}
