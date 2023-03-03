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
            $table->unsignedBigInteger('user_id');
            $table->string('promotion_key')->unique();
            $table->text('title');
            $table->date('from_date');
            $table->date('to_date');
            $table->enum('type',['all', 'new', 'return'])->default('all');
            $table->string('country');
            $table->integer('tier');
            $table->enum('discount_type',['percent', 'amount', 'free'])->default('percent');
            $table->float('ordered_amount');
            $table->float('discount_amount');
            $table->integer('free_shipping');
            $table->enum('status',['active', 'inactive'])->default('active');
            $table->string('country');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
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
        Schema::dropIfExists('promotions');
    }
};
