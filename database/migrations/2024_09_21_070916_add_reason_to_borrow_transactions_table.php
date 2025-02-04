<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('borrow_transactions', function (Blueprint $table) {
            $table->string('reason')->nullable(); // Thêm cột reason
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('borrow_transactions', function (Blueprint $table) {
            $table->dropColumn('reason'); // Xóa cột reason khi rollback
        });
    }
};
