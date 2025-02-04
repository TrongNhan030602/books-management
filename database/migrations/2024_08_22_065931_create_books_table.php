<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->unsignedBigInteger('publisher_id');
            $table->decimal('price', 10, 2);
            $table->integer('initial_quantity');
            $table->integer('quantity');
            $table->year('published_year');
            $table->enum('status', ['available', 'borrowed', 'reserved', 'not_available']);
            $table->timestamps();

            // Define foreign key constraint
            $table->foreign('publisher_id')->references('id')->on('publishers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('books');
    }
}
