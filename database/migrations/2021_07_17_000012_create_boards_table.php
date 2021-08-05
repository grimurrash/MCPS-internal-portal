<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->text('accessToken')->nullable();
            $table->text('refreshToken')->nullable();
            $table->string('tokenExpires')->nullable();
            $table->string('userName')->nullable();
            $table->string('userEmail')->nullable();
            $table->string('position')->nullable();
            $table->string('officeNumber')->nullable();
            $table->unsignedBigInteger('building_id')->nullable();
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('boards');
    }
}
