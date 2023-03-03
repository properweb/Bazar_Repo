<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductPrepack extends Model
{
    protected $table = 'product_prepacks';
    public $timestamps = true;
    protected $fillable = [];
}
