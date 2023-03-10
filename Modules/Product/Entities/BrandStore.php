<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;


class BrandStore extends Model
{
    protected $table = 'brand_store_import_tbl';
    public $timestamps = true;
    protected $fillable = [];
}
