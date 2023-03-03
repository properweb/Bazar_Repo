<?php

namespace Modules\Category\Entities;

use Illuminate\Database\Eloquent\Model;


class Category extends Model
{
    protected $table = 'category';
    public $timestamps = true;
    protected $fillable = [];
}
