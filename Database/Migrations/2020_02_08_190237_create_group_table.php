<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group', function (Blueprint $table) {
            $table->increments('id_group');
            $table->string('group_name');
            $table->string('group_email')->nullable();
            $table->string('group_telp')->nullable();
            $table->text('group_address')->nullable();
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('modified_by');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('group', function($table){
            $table->foreign('parent_id')->references('id_group')->on('group')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group');
    }
}
