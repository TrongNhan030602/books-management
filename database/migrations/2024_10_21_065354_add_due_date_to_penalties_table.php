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
        Schema::table('penalties', function (Blueprint $table) {
            $table->date('due_date')->nullable(); // Thêm trường due_date, có thể để null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('penalties', function (Blueprint $table) {
            $table->dropColumn('due_date'); // Xóa trường khi rollback
        });
    }
};