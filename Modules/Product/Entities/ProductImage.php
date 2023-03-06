<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;


class ProductImage extends Model
{

    protected $table = 'product_images';
    public $timestamps = true;
    protected $fillable = [];
}
