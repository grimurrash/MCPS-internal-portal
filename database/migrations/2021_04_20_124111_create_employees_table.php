<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('fullName');

            $table->string('department_id',10)->nullable();

            //  Время начала и завершения рабочего дня хранится в секундах от начала дня (00:00 + 540 минут(9 часов))
            $table->integer('startOfTheDay')->default(540);
            $table->integer('endOfTheDay')->default(1080);
            $table->boolean('visitControl')->default(true);

            //  Дополнительная информация
            $table->string('workingPosition')->nullable();
            $table->string('internalCode',4)->nullable();
            $table->string('mobilePhone',18)->nullable();
            $table->string('roomNumber', 5)->nullable();
            $table->string('email')->nullable();
            $table->string('birthday',100)->nullable();


            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
}
