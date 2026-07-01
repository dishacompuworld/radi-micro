<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Create permissions
        $permissions = [
            'create-user', 'view-users', 'edit-user', 'destroy-user', 
            'add-server', 'view-server', 'update-server', 'delete-server',
            'destroy-permission', 'edit-permission', 
            'add-alerts', 'show-alerts', 'edit-alerts', 'delete-alert', 'delete-alerts',
            'run-command', 'upload-chart', 'edit-role', 'destroy-role', 'view-role', 'create-role', 
            'add-location', 'view-locations', 'delete-location', 
            'view-prtg',  
            'rename-ont', 'fetch-op-power', 'show-op-power', 'assign-optical-power', 'add-ont', 'register-ont', 'delete-ont', 
            'view-packages', 'reset-mac', 'enable-disable', 'overright-bandwidth', 'view-admin-radius-logs', 'view-radius-logs',
            'all-logs', 'delete-all-logs', 
            'view-sheduler', 'view-script', 'view-microtik-logs', 'view-system-health', 'view-neighbors', 'view-services',
            'view-dashboard', 'view-subscriber', 'view-dashboard-stats',
            'send-whatsapp'

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdminRole->givePermissionTo(Permission::all());

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo([
            'view-users', 'view-server', 'view-locations', 'view-dashboard', 'view-subscriber', 'view-dashboard-stats',
            'view-serverstats', 'view-packages', 'view-radius-logs', 'show-log', 'view-radius', 'view-sheduler', 'view-access-control',
            'view-prtg', 'view-active-users'
        ]);
    }
}
