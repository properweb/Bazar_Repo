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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('brand_id')->default('0');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('CASCADE');
            $table->string('product_sku')->nullable();
            $table->string('product_name')->nullable();
            $table->tinyInteger('variant_id')->nullable();
            $table->string('type')->nullable();
            $table->string('style_name')->nullable();
            $table->string('style_group_name')->nullable();
            $table->string('reference')->nullable();
            $table->tinyInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->float('price')->nullable();
            $table->string('status')->default('new');
            $table->integer('quantity')->nullable();
            $table->float('amount')->nullable();
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
        Schema::dropIfExists('carts');
    }
};
