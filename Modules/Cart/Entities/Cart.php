<?php

namespace Modules\Cart\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\Product;

class Cart extends Model {

    protected $fillable = [
        'brand_id',
        'product_id',
        'variant_id',
        'type',
        'style_name',
        'style_group_name',
        'reference',
        'user_id',
        'price',
        'status',
        'quantity',
        'amount'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
