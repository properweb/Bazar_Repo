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
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('product_key')->nullable();
            $table->string('import_type')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('name')->charset('utf8');
            $table->string('slug');
            $table->integer('main_category')->default('0');
            $table->integer('category')->default('0');
            $table->integer('sub_category')->default('0');
            $table->string('status')->default('publish');
            $table->mediumText('description')->nullable();
            $table->integer('country')->default('0');
            $table->integer('case_quantity')->default('0');
            $table->integer('min_order_qty')->default('0');
            $table->string('min_order_qty_type')->nullable();
            $table->string('sku')->nullable();$table->float('amount', 8, 2);
            $table->float('usd_wholesale_price')->default('0');
            $table->float('usd_retail_price')->default('0');
            $table->float('cad_wholesale_price')->default('0');
            $table->float('cad_retail_price')->default('0');
            $table->float('gbr_wholesale_price')->default('0');
            $table->float('gbr_retail_price')->default('0');
            $table->float('eur_wholesale_price')->default('0');
            $table->float('eur_retail_price')->default('0');
            $table->float('usd_tester_price')->default('0');
            $table->mediumText('fabric_content')->nullable();
            $table->mediumText('care_instruction')->nullable();
            $table->mediumText('season')->nullable();
            $table->string('Occasion')->nullable();
            $table->string('Aesthetic')->nullable();
            $table->string('Fit')->nullable();
            $table->string('Secondary_Occasion')->nullable();
            $table->string('Secondary_Aesthetic')->nullable();
            $table->string('Secondary_Fit')->nullable();
            $table->string('Preorder', 10)->nullable();
            $table->string('product_id',50)->nullable();
            $table->string('website')->nullable();
            $table->integer('stock')->nullable();
            $table->string('featured_image')->nullable();
            $table->integer('order_by')->default('0');
            $table->string('dimension_unit',50)->nullable();
            $table->string('is_bestseller',10)->nullable();
            $table->string('shipping_height',20)->nullable();
            $table->string('shipping_length',20)->nullable();
            $table->string('traffic_code',50)->nullable();
            $table->string('shipping_weight',20)->nullable();
            $table->string('shipping_width',20)->nullable();
            $table->string('weight_unit',20)->nullable();
            $table->float('gbp_wholesale_price')->default('0');
            $table->string('gbp_retail_price')->default('0');
            $table->string('reatailers_inst')->nullable();
            $table->string('reatailer_input_limit',50)->nullable();
            $table->string('retailer_min_qty',20)->nullable();
            $table->string('retailer_add_charge',20)->nullable();
            $table->date('product_shipdate')->nullable();
            $table->date('product_endshipdate')->nullable();
            $table->date('product_deadline')->nullable();
            $table->string('out_of_stock',20)->nullable();
            $table->string('keep_product',20)->nullable();
            $table->string('default_currency',20)->default('USD');
            $table->string('outside_us')->nullable();
            $table->tinyInteger('sell_type')->default('1');
            $table->tinyInteger('prepack_type')->default('1');
            $table->string('tariff_code')->nullable();
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
        Schema::dropIfExists('products');
    }
};
