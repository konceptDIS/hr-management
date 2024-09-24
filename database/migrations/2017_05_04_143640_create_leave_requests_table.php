<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->date('date_created');
            $table->string('leave_type');
            $table->string('attachement_file_path', 300);
            $table->string('created_by', 50)->index();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('submitted')->nullable()->default(null);
            $table->date('date_submitted')->nullable();
            $table->boolean('recalled')->nullable()->default(null);
            $table->date('date_recalled')->nullable();
            $table->boolean('md_approval_required');

            $table->boolean('supervisor_response')->nullable()->default(null);
            $table->date('supervisor_response_date')->nullable();
            $table->string('supervisor_username', 50)->nullable();
            $table->string('supervisor_response_reason',500)->nullable();

            $table->boolean('hr_response')->nullable()->default(null);
            $table->date('hr_response_date')->nullable();
            $table->string('hr_username',50)->nullable();
            $table->string('hr_response_reason',500)->nullable();

            $table->boolean('md_response')->nullable()->default(null);
            $table->date('md_response_date')->nullable();
            $table->string('md_username',50)->nullable();
            $table->string('md_response_reason',500)->nullable();

            $table->boolean('stand_in_response')->nullable()->default(null);
            $table->date('stand_in_response_date')->nullable();
            $table->string('stand_in_username',50)->nullable();
            $table->string('stand_in_response_reason',500)->nullable();

            $table->integer('days_requested');

            $table->string('designation', 50);
            $table->integer('days_left');

            $table->string('area_office', 50);
            $table->string('region', 50);
            $table->string('department', 50);
            $table->string('section', 50);
            $table->string('reason', 500);
            $table->string('phone_number', 15);
            $table->string('address', 70);

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
        Schema::dropIfExists('leave_requests');
    }
}
