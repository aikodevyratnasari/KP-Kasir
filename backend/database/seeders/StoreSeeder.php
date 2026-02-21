<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('stores')->insertOrIgnore([
            [
                'name'           => 'DePOS Restaurant - Pusat',
                'address'        => 'Jl. Raya Surabaya No. 1, Surabaya, Jawa Timur 60111',
                'phone'          => '031-12345678',
                'email'          => 'pusat@depos.id',
                'tax_number'     => '01.234.567.8-900.000',
                'tax_rate'       => 10.00,
                'receipt_footer' => 'Terima kasih telah berkunjung! Kami berharap dapat melayani Anda kembali.',
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'name'           => 'DePOS Restaurant - Cabang Timur',
                'address'        => 'Jl. Rungkut Industri No. 45, Surabaya, Jawa Timur 60293',
                'phone'          => '031-87654321',
                'email'          => 'timur@depos.id',
                'tax_number'     => '01.234.567.8-901.000',
                'tax_rate'       => 10.00,
                'receipt_footer' => 'Terima kasih telah berkunjung! Kami berharap dapat melayani Anda kembali.',
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
    }
}
