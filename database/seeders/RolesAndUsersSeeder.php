<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $waiterRole = Role::create(['name' => 'waiter']);
        $cashierRole = Role::create(['name' => 'cashier']);

        // Create test users
        $waiter = User::create([
            'name' => 'Waiter Test',
            'email' => 'waiter@example.com',
            'password' => bcrypt('password')
        ]);
        $waiter->roles()->attach($waiterRole);

        $cashier = User::create([
            'name' => 'Cashier Test',
            'email' => 'cashier@example.com',
            'password' => bcrypt('password')
        ]);
        $cashier->roles()->attach($cashierRole);
    }
}