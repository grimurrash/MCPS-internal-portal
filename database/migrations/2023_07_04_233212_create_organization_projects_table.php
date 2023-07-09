<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organization_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('number');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description');
            $table->text('metrics');
            $table->integer('planned_coverage')->default(0);
            $table->integer('actual_coverage')->default(0);
            $table->unsignedBigInteger('responsible_employee_id');
            $table->unsignedBigInteger('curator_id');
            $table->unsignedBigInteger('organizer_id');

            $table->integer('status');

            $table->timestamps();

            $table->foreign('responsible_employee_id')->references('id')->on('users');
            $table->foreign('curator_id')->references('id')->on('users');
            $table->foreign('organizer_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_projects');
    }
};
