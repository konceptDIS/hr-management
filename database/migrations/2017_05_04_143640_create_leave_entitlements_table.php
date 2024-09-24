<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveEntitlementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_entitlements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('days_since_resumption');
            $table->integer('days_since_resumption_max');
            $table->integer('days_allowed');
            $table->string('leave_type');
            $table->string('salary_grade');
            $table->boolean('show')->default(false);
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
        Schema::dropIfExists('leave_entitlements');
    }
}
