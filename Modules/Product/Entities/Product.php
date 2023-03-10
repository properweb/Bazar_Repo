<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;


class Product extends Model
{
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
        'usd_wholesale_price',
        'usd_retail_price',
        'cad_wholesale_price',
        'cad_retail_price',
        'gbp_wholesale_price',
        'gbp_retail_price',
        'eur_wholesale_price',
        'eur_retail_price',
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

}


