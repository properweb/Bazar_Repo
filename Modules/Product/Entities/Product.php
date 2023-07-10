<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'product_key',
        'user_id',
        'main_category',
        'category',
        'sub_category',
        'status',
        'description',
        'country',
        'case_quantity',
        'min_order_qty',
        'sku',
        'option_items',
        'colorOptionItems',
        'usd_wholesale_price',
        'usd_retail_price',
        'cad_wholesale_price',
        'cad_retail_price',
        'gbp_wholesale_price',
        'gbp_retail_price',
        'eur_wholesale_price',
        'eur_retail_price',
        'aud_wholesale_price',
        'aud_retail_price',
        'usd_tester_price',
        'dimension_unit',
        'is_bestseller',
        'shipping_height',
        'stock',
        'shipping_length',
        'shipping_weight',
        'shipping_width',
        'weight_unit',
        'reatailers_inst',
        'reatailer_input_limit',
        'retailer_min_qty',
        'retailer_add_charge',
        'product_shipdate',
        'product_endshipdate',
        'product_deadline',
        'out_of_stock',
        'keep_product',
        'sell_type',
        'prepack_type',
        'outside_us',
        'tariff_code',
        'slug'

    ];

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function productVideos()
    {
        return $this->hasMany(Video::class);
    }

    public function productVariation()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function productPrepack()
    {
        return $this->hasMany(ProductPrepack::class);
    }

    public function productFeatureImage()
    {
        return $this->hasOne(ProductImage::class);
    }
}