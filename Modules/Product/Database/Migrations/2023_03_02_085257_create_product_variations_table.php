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
        Schema::create('product_variations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('variant_key')->nullable();
            $table->string('image')->nullable();
            $table->string('swatch_image')->nullable();
            $table->integer('product_id')->nullable();
            $table->float('price')->nullable();
            $table->string('options1')->nullable();
            $table->string('options2')->nullable();
            $table->string('options3')->nullable();
            $table->string('value1')->nullable();
            $table->string('value2')->nullable();
            $table->string('value3')->nullable();
            $table->string('sku')->nullable();
            $table->string('image_id',50)->nullable();
            $table->float('retail_price')->nullable();
            $table->float('cad_wholesale_price')->nullable();
            $table->float('cad_retail_price')->nullable();
            $table->float('gbp_wholesale_price')->nullable();
            $table->float('gbp_retail_price')->nullable();
            $table->float('eur_wholesale_price')->nullable();
            $table->float('eur_retail_price')->nullable();
            $table->float('aud_wholesale_price')->nullable();
            $table->float('aud_retail_price')->nullable();
            $table->string('stock',11)->nullable();
            $table->string('weight',20)->nullable();
            $table->string('length',20)->nullable();
            $table->string('length_unit',20)->nullable();
            $table->string('width_unit',20)->nullable();
            $table->string('height_unit',20)->nullable();
            $table->string('width',20)->nullable();
            $table->string('height',20)->nullable();
            $table->string('dimension_unit',20)->nullable();
            $table->string('weight_unit',20)->nullable();
            $table->string('tariff_code',20)->nullable();
            $table->string('website')->nullable();
            $table->string('website_product_id',30)->nullable();
            $table->string('variation_id',30)->nullable();
            $table->string('inventory_item_id',30)->nullable();
            $table->tinyInteger('status')->default('1');
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
        Schema::dropIfExists('product_variations');
    }
};
