<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {

            Schema::create('userlocation', function (Blueprint $table) {
                $table->unsignedBigInteger('locationid');
                $table->unsignedBigInteger('userid');

                $table->foreign('userid')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

                $table->foreign('locationid')
                ->references('id')
                ->on('locations')
                ->onDelete('cascade');

            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::drop('userlocation');
        Schema::dropIfExists('userlocation');
    }
};
