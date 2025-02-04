<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PublisherSeeder extends Seeder
{
    public function run()
    {
        DB::table('publishers')->insert([
            ['name' => 'Nhà xuất bản Đại học Quốc gia', 'address' => '123 Đường Đại học, Thành phố Hà Nội'],
            ['name' => 'Nhà xuất bản Giáo dục Việt Nam', 'address' => '456 Đường Giáo dục, Thành phố Hồ Chí Minh'],
            ['name' => 'Nhà xuất bản Thế Giới', 'address' => '789 Đường Thế Giới, Thành phố Đà Nẵng'],
        ]);
    }
}
