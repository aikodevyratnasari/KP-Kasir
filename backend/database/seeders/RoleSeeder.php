<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name'        => 'Admin',
                'slug'        => 'admin',
                'description' => 'Full system access: user management, store configuration, all reports.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Manager',
                'slug'        => 'manager',
                'description' => 'Operational access: menu, orders, payments, reports, table management.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Cashier',
                'slug'        => 'cashier',
                'description' => 'Front-of-house: create orders, process payments, manage tables.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Kitchen Staff',
                'slug'        => 'kitchen_staff',
                'description' => 'Kitchen display: view order queue, update cooking status.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];

        DB::table('roles')->insertOrIgnore($roles);
    }
}
