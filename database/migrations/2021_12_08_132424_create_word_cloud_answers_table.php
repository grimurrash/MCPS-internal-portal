<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWordCloudAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('word_cloud_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wordCloud_id');
            $table->text('answer');
            $table->string('ip');
            $table->foreign('wordCloud_id')->references('id')->on('word_clouds')->onDelete('cascade');
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
        Schema::dropIfExists('word_cloud_answers');
    }
}
