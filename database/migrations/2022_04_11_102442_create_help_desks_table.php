<?php

use App\Models\HelpDesk;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHelpDesksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('help_desks', function (Blueprint $table) {
            $table->id();
            $table->dateTime('creation_time');
            $table->string('employee_info')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable()->default(null);
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->enum('category', HelpDesk::getHelpDeskCategoriesKeys())->default(0);
            $table->enum('execution_address', HelpDesk::getExecutionAddressesKeys())->default(0);
            $table->text('description');
            $table->unsignedBigInteger('executor_id')->nullable()->default(null);
            $table->foreign('executor_id')->references('id')->on('users')->onDelete('set null');
            $table->enum('status', HelpDesk::getHelpDeskTaskStatusesKeys())->default(0);
            $table->dateTime('date_of_execution')->nullable()->default(null);

            $table->boolean('is_send_feedback_email')->default(0);
            $table->integer('estimation')->nullable()->default(null);
            $table->text('employee_note')->nullable()->default(null);
            $table->text('executor_note')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('help_desks');
    }
}
