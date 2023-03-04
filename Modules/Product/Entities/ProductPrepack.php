<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductPrepack extends Model
{
    protected $table  = 'product_prepacks';
    protected $fillable = [
        'product_id',
        'style',
        'pack_name',
        'size_ratio',
        'size_range',
        'packs_price',
        'active'
    ];
}
