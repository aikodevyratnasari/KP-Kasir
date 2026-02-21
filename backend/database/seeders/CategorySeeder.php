<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $stores = DB::table('stores')->pluck('id');

        $categories = [
            ['name' => 'Makanan Utama',  'sort_order' => 1],
            ['name' => 'Makanan Ringan', 'sort_order' => 2],
            ['name' => 'Minuman',        'sort_order' => 3],
            ['name' => 'Dessert',        'sort_order' => 4],
            ['name' => 'Paket Hemat',    'sort_order' => 5],
            ['name' => 'Minuman Panas',  'sort_order' => 6],
        ];

        foreach ($stores as $storeId) {
            foreach ($categories as $cat) {
                DB::table('categories')->insertOrIgnore([
                    'store_id'   => $storeId,
                    'name'       => $cat['name'],
                    'slug'       => Str::slug($cat['name']) . '-' . $storeId,
                    'sort_order' => $cat['sort_order'],
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
