<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answer', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('user_id');
            $table->tinyInteger('post_id');
            $table->tinyInteger('class_id');
            $table->string('answer_link', 255)->nullable();
            $table->string('file', 1000)->nullable();
            $table->float('score')->nullable();
            $table->string('evaluate', 255)->nullable();
            $table->integer('add_time')->nullable();
            $table->string('reason_late', 255)->nullable();
            $table->dateTime('date_line')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('answer');
    }
};
