<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDaysLeftSalaryGradeToLeaveRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            // Schema::table('leave_requests', function(Blueprint $table){
            //     $table->string('salary_grade', 100);
            //     $table->integer('days_left');
            // });
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
