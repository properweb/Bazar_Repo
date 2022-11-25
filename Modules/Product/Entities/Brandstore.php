<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brandstore extends Model
{
    protected $table = 'brand_store_import_tbl';
    public $timestamps = true;
    protected $fillable = [];
}
