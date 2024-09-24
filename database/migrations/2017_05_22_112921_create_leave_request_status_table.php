<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveRequestStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_request_status', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('leave_request_id')->index();
            $table->string('responder_username', 50);
            $table->date('response_date');
            $table->string('role',50);
            $table->string('remarks',500);
            $table->string('stage');
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
        Schema::drop('leave_request_status');
    }
}
