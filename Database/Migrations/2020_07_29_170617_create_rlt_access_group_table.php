<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRltAccessGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rlt_access_group', function (Blueprint $table) {
            $table->increments('id_rlt_access_group');
            $table->text('access_scope');
            $table->unsignedInteger('module_id');
            $table->unsignedInteger('group_id');
            $table->timestamps();
        });

        Schema::table('rlt_access_group', function($table){
            $table->foreign('module_id')->references(\Gdevilbat\SpardaCMS\Modules\Core\Entities\Module::getPrimaryKey())->on('module')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('group_id')->references(\Gdevilbat\SpardaCMS\Modules\User\Entities\Group::getPrimaryKey())->on('group')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rlt_access_group');
    }
}
