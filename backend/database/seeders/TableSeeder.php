<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $stores = DB::table('stores')->pluck('id');

        $tableConfig = [
            // [ number, capacity, section ]
            // Indoor - small tables
            ['A01', 2, 'Indoor'],
            ['A02', 2, 'Indoor'],
            ['A03', 4, 'Indoor'],
            ['A04', 4, 'Indoor'],
            ['A05', 4, 'Indoor'],
            ['A06', 4, 'Indoor'],
            // Indoor - large tables
            ['B01', 6, 'Indoor'],
            ['B02', 6, 'Indoor'],
            ['B03', 8, 'Indoor'],
            // Outdoor
            ['C01', 4, 'Outdoor'],
            ['C02', 4, 'Outdoor'],
            ['C03', 4, 'Outdoor'],
            ['C04', 6, 'Outdoor'],
            // VIP Room
            ['V01', 8,  'VIP'],
            ['V02', 10, 'VIP'],
            ['V03', 12, 'VIP'],
        ];

        foreach ($stores as $storeId) {
            foreach ($tableConfig as [$number, $capacity, $section]) {
                DB::table('tables')->insertOrIgnore([
                    'store_id'   => $storeId,
                    'number'     => $number,
                    'capacity'   => $capacity,
                    'section'    => $section,
                    'status'     => 'available',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
