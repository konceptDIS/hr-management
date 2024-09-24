<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recalls', function(Blueprint $table){
            $table->increments('id');
            $table->integer('leave_approval_id')->index();
            $table->string('applicant_username', 50)->index();
            $table->date('date');
            $table->string('reason',300);
            $table->string('supervisor_username',50);
            $table->boolean('supervisor_response',50);
            $table->string('supervisor_response_reason',300);
            $table->integer('days_credited');
            $table->string('leave_type',50)->index();
            $table->date('leave_approval_date');
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
        Schema::drop('recalls');
    }
}
