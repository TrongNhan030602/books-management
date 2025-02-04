<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAvatarToUsersAndCoverImageToBooks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email'); // Thêm cột avatar sau cột email
        });

        Schema::table('books', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->after('title'); // Thêm cột cover_image sau cột title
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar'); // Xóa cột avatar nếu rollback
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('cover_image'); // Xóa cột cover_image nếu rollback
        });
    }
}