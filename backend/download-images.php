<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

$ctx = stream_context_create([
    'http' => ['timeout' => 15, 'follow_location' => true, 'max_redirects' => 5,
               'header' => "User-Agent: Mozilla/5.0\r\n"],
    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
]);

// Hapus gambar lama
echo "🗑️  Hapus gambar lama...\n";
$oldFiles = Storage::disk('public')->files('products');
foreach ($oldFiles as $f) Storage::disk('public')->delete($f);
DB::table('products')->whereNotNull('image')->update(['image' => null]);
echo "  ✅ " . count($oldFiles) . " file dihapus.\n\n";

// loremflickr dengan keyword sangat spesifik per produk
// format: https://loremflickr.com/400/300/keyword1,keyword2?lock=N
$products = [
    'Nasi Goreng Spesial'        => 'nasi,goreng,rice,fried',
    'Mie Goreng Seafood'         => 'mie,noodle,goreng,fried',
    'Ayam Bakar Madu'            => 'ayam,chicken,grilled,roasted',
    'Soto Ayam Lamongan'         => 'soto,soup,broth,bowl',
    'Rawon Daging'               => 'rawon,beef,soup,dark',
    'Ikan Bakar Bumbu Bali'      => 'ikan,fish,grilled,bakar',
    'Nasi Uduk Komplit'          => 'nasi,rice,coconut,plate',
    'Gado-Gado Spesial'          => 'gado,salad,peanut,vegetables',
    'Pisang Goreng Keju'         => 'pisang,banana,fried,dessert',
    'Tahu Crispy'                => 'tahu,tofu,crispy,fried',
    'Tempe Mendoan'              => 'tempe,tempeh,fried,snack',
    'Lumpia Goreng'              => 'lumpia,springroll,fried,roll',
    'Kentang Goreng'             => 'kentang,potato,fries,crispy',
    'Es Teh Manis'               => 'tea,iced,glass,cold',
    'Es Jeruk Peras'             => 'orange,juice,fresh,iced',
    'Jus Alpukat'                => 'avocado,juice,green,creamy',
    'Es Cincau Hijau'            => 'jelly,green,iced,drink',
    'Air Mineral'                => 'water,mineral,bottle,clear',
    'Es Campur Spesial'          => 'dessert,ice,colorful,mixed',
    'Klepon'                     => 'klepon,cake,green,sweet',
    'Puding Coklat'              => 'pudding,chocolate,dessert,dark',
    'Es Krim Lokal'              => 'icecream,scoop,dessert,cone',
    'Pisang Bakar Coklat Keju'   => 'banana,grilled,chocolate,cheese',
    'Paket Nasi + Ayam + Es Teh' => 'meal,set,rice,chicken',
    'Paket Mie + Tempe + Minuman'=> 'noodle,set,meal,plate',
    'Paket Keluarga (4 pax)'     => 'feast,family,food,spread',
    'Kopi Hitam'                 => 'coffee,black,cup,espresso',
    'Teh Tarik'                  => 'tea,milk,tarik,frothy',
    'Bajigur'                    => 'ginger,warm,drink,traditional',
    'Wedang Jahe'                => 'ginger,warm,cup,spice',
    'Susu Coklat Panas'          => 'chocolate,milk,hot,cocoa',
];

Storage::disk('public')->makeDirectory('products');

echo "🖼️  Download gambar...\n\n";
$success = $fail = 0;
$seed = 200;

foreach ($products as $name => $keyword) {
    $filename = 'products/' . trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-') . '.jpg';
    $url = "https://loremflickr.com/400/300/{$keyword}?lock={$seed}";

    $imageData = @file_get_contents($url, false, $ctx);

    if ($imageData && strlen($imageData) > 5000) {
        Storage::disk('public')->put($filename, $imageData);
        DB::table('products')->where('name', $name)
            ->update(['image' => $filename, 'updated_at' => now()]);
        echo "  ✅ {$name}\n";
        $success++;
    } else {
        echo "  ❌ Gagal: {$name}\n";
        $fail++;
    }

    $seed++;
    usleep(300000);
}

echo "\n════════════════════════════════\n";
echo "  ✅ Berhasil : {$success}\n";
echo "  ❌ Gagal    : {$fail}\n";
echo "════════════════════════════════\n";
echo "\n🎉 Selesai! Jalankan: php artisan cache:clear\n";