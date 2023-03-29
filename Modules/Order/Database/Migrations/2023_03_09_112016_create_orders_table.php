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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->nullable();
            $table->tinyInteger('parent_id')->nullable();
            $table->unsignedBigInteger('brand_id')->default('0');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('CASCADE');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->string('user_email')->nullable();
            $table->float('sub_total')->default('0.00');
            $table->unsignedBigInteger('shipping_id')->default('0');
            $table->foreign('shipping_id')->references('id')->on('shippings')->onDelete('CASCADE');
            $table->date('shipping_date')->nullable();
            $table->float('shipping_charge')->nullable();
            $table->integer('shipping_free')->default('0');
            $table->float('total_amount')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('discount_type')->nullable();
            $table->float('discount')->nullable();
            $table->float('has_discount')->default('0');
            $table->string('payment_method')->default('cod');
            $table->string('payment_status')->default('paid');
            $table->string('status')->default('new');
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('town')->nullable();
            $table->string('post_code')->nullable();
            $table->mediumText('address1')->nullable();
            $table->mediumText('address2')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('brand_email')->nullable();
            $table->string('brand_phone')->nullable();
            $table->string('brand_country')->nullable();
            $table->string('brand_state')->nullable();
            $table->string('brand_town')->nullable();
            $table->string('brand_post_code')->nullable();
            $table->string('brand_address1')->nullable();
            $table->string('brand_address2')->nullable();
            $table->string('cancel_reason_title')->nullable();
            $table->string('cancel_reason_desc')->nullable();
            $table->string('shipping_name')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('shipping_street')->nullable();
            $table->string('shipping_suite')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_town')->nullable();
            $table->string('shipping_zip')->nullable();
            $table->string('shipping_phoneCode')->nullable();
            $table->string('shipping_phone')->nullable();
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
        Schema::dropIfExists('orders');
    }
};
