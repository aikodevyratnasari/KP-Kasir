<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $stores = DB::table('stores')->pluck('id');

        $menuItems = [
            // Makanan Utama
            ['category' => 'Makanan Utama', 'name' => 'Nasi Goreng Spesial',    'price' => 35000,  'stock' => 50, 'description' => 'Nasi goreng dengan telur, ayam, dan sayuran segar pilihan.'],
            ['category' => 'Makanan Utama', 'name' => 'Mie Goreng Seafood',     'price' => 40000,  'stock' => 40, 'description' => 'Mie goreng dengan udang, cumi, dan bumbu spesial.'],
            ['category' => 'Makanan Utama', 'name' => 'Ayam Bakar Madu',        'price' => 45000,  'stock' => 30, 'description' => 'Ayam bakar dengan saus madu dan rempah pilihan, disajikan dengan nasi.'],
            ['category' => 'Makanan Utama', 'name' => 'Soto Ayam Lamongan',     'price' => 28000,  'stock' => 60, 'description' => 'Soto ayam khas Lamongan dengan lontong atau nasi.'],
            ['category' => 'Makanan Utama', 'name' => 'Rawon Daging',           'price' => 38000,  'stock' => 35, 'description' => 'Rawon daging sapi dengan kuah hitam khas Jawa Timur.'],
            ['category' => 'Makanan Utama', 'name' => 'Ikan Bakar Bumbu Bali',  'price' => 55000,  'stock' => 25, 'description' => 'Ikan bakar segar dengan bumbu Bali yang kaya rempah.'],
            ['category' => 'Makanan Utama', 'name' => 'Nasi Uduk Komplit',      'price' => 32000,  'stock' => 45, 'description' => 'Nasi uduk dengan ayam goreng, tempe, tahu, dan sambal.'],
            ['category' => 'Makanan Utama', 'name' => 'Gado-Gado Spesial',      'price' => 25000,  'stock' => 50, 'description' => 'Sayuran segar dengan saus kacang kental dan lontong.'],

            // Makanan Ringan
            ['category' => 'Makanan Ringan', 'name' => 'Pisang Goreng Keju',   'price' => 18000,  'stock' => 60, 'description' => 'Pisang goreng renyah dengan taburan keju susu.'],
            ['category' => 'Makanan Ringan', 'name' => 'Tahu Crispy',           'price' => 15000,  'stock' => 80, 'description' => 'Tahu goreng crispy dengan saus pedas manis.'],
            ['category' => 'Makanan Ringan', 'name' => 'Tempe Mendoan',         'price' => 12000,  'stock' => 80, 'description' => 'Tempe mendoan khas Banyumas dengan tepung berbumbu.'],
            ['category' => 'Makanan Ringan', 'name' => 'Lumpia Goreng',         'price' => 20000,  'stock' => 50, 'description' => 'Lumpia isi rebung, telur, dan ayam, digoreng renyah.'],
            ['category' => 'Makanan Ringan', 'name' => 'Kentang Goreng',        'price' => 22000,  'stock' => 70, 'description' => 'Kentang goreng renyah dengan saus sambal dan mayonaise.'],

            // Minuman
            ['category' => 'Minuman', 'name' => 'Es Teh Manis',                'price' => 8000,   'stock' => 100, 'description' => 'Teh manis dingin segar.'],
            ['category' => 'Minuman', 'name' => 'Es Jeruk Peras',              'price' => 12000,  'stock' => 80,  'description' => 'Jeruk peras segar dengan es batu.'],
            ['category' => 'Minuman', 'name' => 'Jus Alpukat',                 'price' => 18000,  'stock' => 40,  'description' => 'Jus alpukat segar dengan susu dan coklat.'],
            ['category' => 'Minuman', 'name' => 'Es Cincau Hijau',             'price' => 10000,  'stock' => 60,  'description' => 'Cincau hijau dengan santan dan gula aren.'],
            ['category' => 'Minuman', 'name' => 'Air Mineral',                 'price' => 5000,   'stock' => 200, 'description' => 'Air mineral botol 600ml.'],
            ['category' => 'Minuman', 'name' => 'Es Campur Spesial',           'price' => 20000,  'stock' => 50,  'description' => 'Es campur dengan kelapa muda, nangka, dan sirup.'],

            // Dessert
            ['category' => 'Dessert', 'name' => 'Klepon',                      'price' => 15000,  'stock' => 40, 'description' => 'Klepon isi gula merah dengan taburan kelapa parut.'],
            ['category' => 'Dessert', 'name' => 'Puding Coklat',               'price' => 12000,  'stock' => 50, 'description' => 'Puding coklat lembut dengan saus vanilla.'],
            ['category' => 'Dessert', 'name' => 'Es Krim Lokal',               'price' => 15000,  'stock' => 60, 'description' => 'Es krim dengan berbagai pilihan rasa lokal.'],
            ['category' => 'Dessert', 'name' => 'Pisang Bakar Coklat Keju',    'price' => 22000,  'stock' => 40, 'description' => 'Pisang bakar dengan topping coklat dan keju.'],

            // Paket Hemat
            ['category' => 'Paket Hemat', 'name' => 'Paket Nasi + Ayam + Es Teh',  'price' => 38000,  'stock' => 30, 'description' => 'Nasi goreng + ayam goreng + es teh manis. Hemat 15%.'],
            ['category' => 'Paket Hemat', 'name' => 'Paket Mie + Tempe + Minuman', 'price' => 35000,  'stock' => 30, 'description' => 'Mie goreng + tempe mendoan + es teh. Hemat 10%.'],
            ['category' => 'Paket Hemat', 'name' => 'Paket Keluarga (4 pax)',       'price' => 140000, 'stock' => 15, 'description' => '4 nasi goreng + 4 ayam goreng + 4 minuman pilihan.'],

            // Minuman Panas
            ['category' => 'Minuman Panas', 'name' => 'Kopi Hitam',            'price' => 10000,  'stock' => 100, 'description' => 'Kopi hitam robusta pilihan.'],
            ['category' => 'Minuman Panas', 'name' => 'Teh Tarik',             'price' => 12000,  'stock' => 80,  'description' => 'Teh susu kental manis khas Malaysia.'],
            ['category' => 'Minuman Panas', 'name' => 'Bajigur',               'price' => 12000,  'stock' => 60,  'description' => 'Minuman tradisional jahe, santan, dan gula aren.'],
            ['category' => 'Minuman Panas', 'name' => 'Wedang Jahe',           'price' => 10000,  'stock' => 70,  'description' => 'Jahe merah segar dengan gula batu, menghangatkan badan.'],
            ['category' => 'Minuman Panas', 'name' => 'Susu Coklat Panas',     'price' => 13000,  'stock' => 60,  'description' => 'Susu full cream dengan coklat bubuk premium.'],
        ];

        foreach ($stores as $storeId) {
            foreach ($menuItems as $item) {
                // Get category ID for this store
                $categoryId = DB::table('categories')
                    ->where('store_id', $storeId)
                    ->where('name', $item['category'])
                    ->value('id');

                if (! $categoryId) {
                    continue;
                }

                $slug = Str::slug($item['name']) . '-' . $storeId;

                DB::table('products')->insertOrIgnore([
                    'category_id'      => $categoryId,
                    'store_id'         => $storeId,
                    'name'             => $item['name'],
                    'slug'             => $slug,
                    'description'      => $item['description'],
                    'image'            => null,
                    'price'            => $item['price'],
                    'stock'            => $item['stock'],
                    'low_stock_alert'  => 10,
                    'is_available'     => true,
                    'track_stock'      => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
    }
}
