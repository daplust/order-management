<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'list-foods',
            'view-foods',
            'create-foods',
            'edit-foods',
            'delete-foods'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $waiterRole = Role::create(['name' => 'waiter']);
        $waiterRole->givePermissionTo($permissions);

        $cashierRole = Role::create(['name' => 'cashier']);
        $cashierRole->givePermissionTo(['list-foods', 'view-foods']);

        // Create test users
        $waiter = User::create([
            'name' => 'Waiter Test',
            'email' => 'waiter@example.com',
            'password' => bcrypt('password'),
        ]);
        $waiter->assignRole('waiter');

        $cashier = User::create([
            'name' => 'Cashier Test',
            'email' => 'cashier@example.com',
            'password' => bcrypt('password'),
        ]);
        $cashier->assignRole('cashier');
    }
}