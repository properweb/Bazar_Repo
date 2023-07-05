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
        Schema::create('retailers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('retailer_key')->unique();
            $table->string('country_code')->nullable();
            $table->string('country')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('state')->nullable();
            $table->string('town')->nullable();
            $table->string('post_code')->nullable();
            $table->string('address1')->nullable();
            $table->string('language')->nullable();
            $table->string('store_name')->nullable();
            $table->string('store_type')->nullable();
            $table->text('store_desc')->nullable();
            $table->string('store_cats')->nullable();
            $table->string('store_tags')->nullable();
            $table->text('store_about')->nullable();
            $table->string('website_url')->nullable();
            $table->string('annual_sales')->nullable();
            $table->string('years_in_business')->nullable();
            $table->tinyInteger('sign_up_for_email')->default(1);
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
        Schema::dropIfExists('retailers');
    }
};
