<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookSeeder extends Seeder
{
    public function run()
    {
        DB::table('books')->insert([
            [
                'title' => 'Lập trình PHP cơ bản',
                'author' => 'Nguyễn Văn A',
                'publisher_id' => 1, // ID nhà xuất bản tương ứng
                'price' => 150000,
                'initial_quantity' => 10,
                'quantity' => 10,
                'published_year' => 2023,
                'status' => 'available',
            ],
            [
                'title' => 'Học Laravel từ cơ bản đến nâng cao',
                'author' => 'Trần Thị B',
                'publisher_id' => 2,
                'price' => 200000,
                'initial_quantity' => 5,
                'quantity' => 5,
                'published_year' => 2024,
                'status' => 'available',
            ],
            [
                'title' => 'Giới thiệu về Machine Learning',
                'author' => 'Lê Văn C',
                'publisher_id' => 3,
                'price' => 250000,
                'initial_quantity' => 7,
                'quantity' => 7,
                'published_year' => 2022,
                'status' => 'available',
            ],
        ]);
    }
}
