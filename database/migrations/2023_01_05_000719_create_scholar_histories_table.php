<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScholarHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scholar_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scholar_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->string('academic_year', 255);
            $table->string('semester', 255);
            $table->boolean('qualified');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scholar_histories');
    }
}
