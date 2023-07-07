<?php

namespace Modules\Cart\Entities;

use Illuminate\Database\Eloquent\Model;


class Cart extends Model {

    protected $table = 'carts';
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
}
