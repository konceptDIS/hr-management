<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('leave_approvals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('leave_request_id')->index();
            $table->date('date_approved');
            $table->string('approved_by',50);
            $table->string('leave_type',50);
            $table->integer('days');
            $table->string('applicant_username');
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
        Schema::drop('leave_approvals');
    }
}
