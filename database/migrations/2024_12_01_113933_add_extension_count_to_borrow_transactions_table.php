<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('borrow_transactions', function (Blueprint $table) {
            $table->unsignedInteger('extension_count')->default(0)->after('status');
        });
    }

    public function down()
    {
        Schema::table('borrow_transactions', function (Blueprint $table) {
            $table->dropColumn('extension_count');
        });
    }

};