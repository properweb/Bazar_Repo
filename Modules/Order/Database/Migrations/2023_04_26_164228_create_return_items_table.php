<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_id')->nullable();
            $table->integer('item_id')->unsigned();
            $table->integer('quantity')->unsigned();
            $table->unsignedBigInteger('reason_id')->nullable();
            $table->timestamps();

            $table->foreign('return_id')->references('id')->on('returns')->onDelete('cascade');
            $table->foreign('reason_id')->references('id')->on('return_reasons')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('return_items');
    }
};
