<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Products extends Model
{

    protected $table = 'products';
    public $timestamps = true;
    protected $fillable = [];
}


