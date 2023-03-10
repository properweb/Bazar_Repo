<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{

    protected $table = 'product_variations';
    
    protected $fillable = [
        'variant_key',
        'swatch_image',
        'image',
        'product_id',
        'price',
        'options1',
        'options2',
        'options3',
        'sku',
        'value1',
        'value2',
        'value3',
        'retail_price',
        'cad_wholesale_price',
        'cad_retail_price',
        'gbp_wholesale_price',
        'gbp_retail_price',
        'eur_wholesale_price',
        'eur_retail_price',
        'aud_wholesale_price',
        'aud_retail_price',
        'stock',
        'weight',
        'length',
        'length_unit',
        'width_unit',
        'height_unit',
        'width',
        'height',
        'dimension_unit',
        'weight_unit',
        'tariff_code'
    ];
}
