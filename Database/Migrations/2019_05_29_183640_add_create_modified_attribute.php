<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreateModifiedAttribute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->after('remember_token')->nullable();
            $table->unsignedBigInteger('modified_by')->after('created_by')->nullable();
        });

        Schema::table('users', function($table){
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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'created_by'))
            {
                if (\DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign(['created_by']);
                }
                $table->dropColumn('created_by');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'modified_by'))
            {
                if (\DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign(['modified_by']);
                }
                $table->dropColumn('modified_by');
            }
        });
    }
}
