<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;


class Video extends Model
{

    protected $table = 'product_video';
    public $timestamps = true;
    protected $fillable = [];
}
