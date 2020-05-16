<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('go');
            $table->unsignedTinyInteger('status');
            $table->unsignedTinyInteger('position');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('room_id')->references('id')->on('rooms');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unique(['room_id', 'user_id'], 'uq_room_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('room_user');
    }
}
