<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariation extends Model
{

    protected $table = 'product_variations';
    public $timestamps = true;
    protected $fillable = [];
}
