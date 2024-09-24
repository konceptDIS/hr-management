<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('abbreviation', 5);
            $table->string('created_by', 50);
            $table->string('type', 15);
            // $table->string('supervisor_username',50)->nullable();
            $table->integer('parent_section_id');
            $table->integer('section_id')->nullable();
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
        Schema::dropIfExists('offices');
    }
}
