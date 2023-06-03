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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('promotion_key')->unique();
            $table->text('title');
            $table->date('from_date');
            $table->date('to_date');
            $table->tinyInteger('type')->default(1);
            $table->string('country');
            $table->integer('tier');
            $table->tinyInteger('discount_type')->default(1);
            $table->float('ordered_amount');
            $table->float('discount_amount');
            $table->integer('free_shipping');
            $table->enum('status',['active','deactivated','completed'])->default('active');
            $table->enum('promotion_type',['order', 'product'])->default('order');
            $table->string('products');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotions');
    }
};
