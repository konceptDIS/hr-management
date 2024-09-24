<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function(Blueprint $table){
            $table->increments('id');
            $table->integer('leave_request_id');
            $table->string('description', 250);
            $table->string('ext', 5);
            $table->string('type', 30);
            $table->string('size', 50);
            $table->string('filename', 250);
            $table->string('uploaded_by', 50);
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
        Schema::dropIfExists('documents');
    }
}
