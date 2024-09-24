<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable()->default(null);
            $table->string('last_name', 50);
            $table->string('salary_grade');
            $table->string('username', 255)->unique();
            $table->string('password', 60);
            $table->string('department', 50);
            $table->string('section', 50);
            $table->string('phone_number',15);
            $table->string('designation',50);
            $table->string('area_office', 50)->nullable();
            $table->string('region', 50)->nullable();
            $table->date('resumption_date');
            $table->boolean('is_contract_staff')->default(false);
            $table->rememberToken();
            $table->string('staff_id',10);
            $table->string('gender',10);
            $table->string('verified_by');
            $table->boolean('verified')->nullable();
            $table->date('date_verified');
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
        Schema::drop('users');
    }
}
