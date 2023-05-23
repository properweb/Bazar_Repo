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
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('retailer_id')->nullable();
            $table->dropColumn('name');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('shipping_name')->nullable();
            $table->integer('shipping_country')->nullable();
            $table->string('shipping_street')->nullable();
            $table->string('shipping_suite')->nullable();
            $table->integer('shipping_state')->nullable();
            $table->integer('shipping_town')->nullable();
            $table->string('shipping_zip')->nullable();
            $table->integer('shipping_phone_code')->nullable();
            $table->string('shipping_phone')->nullable();

            $table->foreign('retailer_id')->references('id')->on('users')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
};
