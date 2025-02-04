<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBooksTableAddLocationAndCategory extends Migration
{
    public function up()
    {
        Schema::table('books', function (Blueprint $table) {
            // Kiểm tra xem cột location có tồn tại chưa, nếu chưa thì thêm
            if (!Schema::hasColumn('books', 'location')) {
                $table->string('location')->nullable()->after('title');
            }

            // Kiểm tra xem cột category_id có tồn tại chưa, nếu chưa thì thêm
            if (!Schema::hasColumn('books', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('location');
            }

            // Thêm khóa ngoại cho category_id nếu chưa có
            if (!Schema::hasTable('categories')) {
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            // Xóa khóa ngoại và các cột nếu có
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'location']);
        });
    }
}