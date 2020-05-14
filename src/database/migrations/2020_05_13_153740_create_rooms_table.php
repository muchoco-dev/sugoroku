<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uname',13);
            $table->string('name');
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('board_id');
            $table->unsignedTinyInteger('max_member_count');
            $table->unsignedTinyInteger('member_count');
            $table->unsignedTinyInteger('status');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('uname');
            $table->foreign('owner_id')->references('id')->on('users');
            $table->foreign('board_id')->references('id')->on('boards');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rooms');
    }
}
