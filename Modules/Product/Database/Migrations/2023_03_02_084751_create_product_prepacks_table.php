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
        Schema::create('product_prepacks', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('CASCADE');
			$table->string('style')->charset('utf8');;
            $table->string('pack_name')->charset('utf8');
            $table->string('size_ratio');
            $table->string('size_range');
            $table->float('packs_price')->default('0.00');
            $table->tinyInteger('active')->default('1');
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
        Schema::dropIfExists('product_prepacks');
    }
};
