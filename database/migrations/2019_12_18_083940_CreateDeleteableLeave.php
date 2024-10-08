<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeleteableLeave extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('deleteable_leave')) {
            Schema::create('deleteable_leave', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('leave_request_id')->unique();
                $table->string('created_by', 50);
                $table->string('reason', 500);
                $table->string('applicant_username', 50);
                $table->text('application_data')->nullable()->default(null);
                $table->text('approval_data')->nullable()->default(null);
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
        Schema::dropIfExists('deleteable_leave');
    }
}
