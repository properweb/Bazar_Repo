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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('brand_key')->unique();
            $table->string('brand_name');
            $table->string('brand_slug')->unique();
            $table->string('num_store')->nullable();
            $table->string('prime_cat')->nullable();
            $table->string('website_url')->nullable();
            $table->string('agree_terms')->nullable();
            $table->string('country_code')->nullable();
            $table->string('country')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('update_check')->nullable();
            $table->string('language')->nullable();
            $table->string('headquatered')->nullable();
            $table->string('established_year')->nullable();
            $table->string('insta_handle')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('product_made')->nullable();
            $table->string('product_shipped')->nullable();
            $table->string('brand_big_box_store')->nullable();
            $table->string('num_products_addcatalog')->nullable();
            $table->string('num_products_sell')->nullable();
            $table->string('release_inventory')->nullable();
            $table->string('stored_carried')->nullable();
            $table->string('tools_used')->nullable();
            $table->string('tools_used_other')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('logo_image')->nullable();
            $table->string('publications')->nullable();
            $table->text('shared_brd_story');
            $table->string('tag_shop_page')->nullable();
            $table->text('tag_shop_page_about');
            $table->integer('avg_lead_time')->default(1);
            $table->float('first_order_min')->nullable();
            $table->float('re_order_min')->nullable();
            $table->string('upload_wholesale_notes')->nullable();
            $table->string('upload_wholesale_xlsx')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('video_url')->nullable();
            $table->string('photo_lib_link')->nullable();
            $table->string('upload_zip')->nullable();
            $table->string('bazaar_direct_link')->unique();
            $table->string('upload_contact_list')->nullable();
            $table->tinyInteger('step_count')->default(0);
            $table->tinyInteger('first_visit')->default(0);
            $table->tinyInteger('previewed_shop_page')->default(0);
            $table->tinyInteger('added_product')->default(0);
            $table->tinyInteger('go_live')->default(0);
            $table->float('cad_order_min')->nullable();
            $table->float('cad_reorder_min')->nullable();
            $table->float('gbp_order_min')->nullable();
            $table->float('gbp_reorder_min')->nullable();
            $table->float('aud_order_min')->nullable();
            $table->float('aud_reorder_min')->nullable();
            $table->float('eur_order_min')->nullable();
            $table->float('eur_reorder_min')->nullable();
            $table->tinyInteger('outside_us')->default(1);
            $table->tinyInteger('sell_to_middle_east')->default(1);
            $table->tinyInteger('sell_to_uk')->default(1);
            $table->tinyInteger('sell_to_aus')->default(1);
            $table->tinyInteger('sell_to_online')->default(1);
            $table->tinyInteger('allow_social_sellers')->default(1);
            $table->tinyInteger('sell_to_qual_reat')->default(1);
            $table->integer('shop_lead_time')->default(1);
            $table->date('pause_from_date')->nullable();
            $table->date('pause_to_date')->nullable();
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
        Schema::dropIfExists('brands');
    }
};
