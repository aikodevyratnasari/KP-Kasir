<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId   = DB::table('roles')->where('slug', 'admin')->value('id');
        $managerRoleId = DB::table('roles')->where('slug', 'manager')->value('id');
        $cashierRoleId = DB::table('roles')->where('slug', 'cashier')->value('id');
        $kitchenRoleId = DB::table('roles')->where('slug', 'kitchen_staff')->value('id');

        $store1 = DB::table('stores')->where('email', 'pusat@depos.id')->value('id');
        $store2 = DB::table('stores')->where('email', 'timur@depos.id')->value('id');

        $users = [
            // ── Super Admin ──────────────────────────────────────────────
            [
                'role_id'    => $adminRoleId,
                'store_id'   => $store1,
                'name'       => 'Super Admin',
                'email'      => 'admin@depos.id',
                'password'   => Hash::make('Admin@12345'),
                'phone'      => '081200000001',
                'status'     => 'active',
            ],
            // ── Managers ─────────────────────────────────────────────────
            [
                'role_id'    => $managerRoleId,
                'store_id'   => $store1,
                'name'       => 'Budi Santoso',
                'email'      => 'manager.pusat@depos.id',
                'password'   => Hash::make('Manager@12345'),
                'phone'      => '081200000002',
                'status'     => 'active',
            ],
            [
                'role_id'    => $managerRoleId,
                'store_id'   => $store2,
                'name'       => 'Siti Rahayu',
                'email'      => 'manager.timur@depos.id',
                'password'   => Hash::make('Manager@12345'),
                'phone'      => '081200000003',
                'status'     => 'active',
            ],
            // ── Cashiers ─────────────────────────────────────────────────
            [
                'role_id'    => $cashierRoleId,
                'store_id'   => $store1,
                'name'       => 'Andi Prasetyo',
                'email'      => 'kasir1.pusat@depos.id',
                'password'   => Hash::make('Cashier@12345'),
                'phone'      => '081200000004',
                'status'     => 'active',
            ],
            [
                'role_id'    => $cashierRoleId,
                'store_id'   => $store1,
                'name'       => 'Dewi Kusuma',
                'email'      => 'kasir2.pusat@depos.id',
                'password'   => Hash::make('Cashier@12345'),
                'phone'      => '081200000005',
                'status'     => 'active',
            ],
            [
                'role_id'    => $cashierRoleId,
                'store_id'   => $store2,
                'name'       => 'Rizky Firmansyah',
                'email'      => 'kasir1.timur@depos.id',
                'password'   => Hash::make('Cashier@12345'),
                'phone'      => '081200000006',
                'status'     => 'active',
            ],
            // ── Kitchen Staff ─────────────────────────────────────────────
            [
                'role_id'    => $kitchenRoleId,
                'store_id'   => $store1,
                'name'       => 'Wahyu Hidayat',
                'email'      => 'dapur1.pusat@depos.id',
                'password'   => Hash::make('Kitchen@12345'),
                'phone'      => '081200000007',
                'status'     => 'active',
            ],
            [
                'role_id'    => $kitchenRoleId,
                'store_id'   => $store1,
                'name'       => 'Nurul Aini',
                'email'      => 'dapur2.pusat@depos.id',
                'password'   => Hash::make('Kitchen@12345'),
                'phone'      => '081200000008',
                'status'     => 'active',
            ],
            [
                'role_id'    => $kitchenRoleId,
                'store_id'   => $store2,
                'name'       => 'Hendra Wijaya',
                'email'      => 'dapur1.timur@depos.id',
                'password'   => Hash::make('Kitchen@12345'),
                'phone'      => '081200000009',
                'status'     => 'active',
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->insertOrIgnore(array_merge($user, [
                'failed_login_attempts' => 0,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]));
        }
    }
}
