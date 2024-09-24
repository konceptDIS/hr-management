<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActionReversalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('action_reversals')){
            Schema::create('action_reversals', function(Blueprint $table){
                $table->increments('id');
                $table->unsignedBigInteger('leave_request_id');
                $table->string('reason', 500);
                $table->string('created_by',50);
                $table->string('reversed_action', 150);
                $table->string('applicant_username', 50);
                $table->json('application_data');
                $table->json('approval_data');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
