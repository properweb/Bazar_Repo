<?php

namespace Modules\Country\Entities;

use Illuminate\Database\Eloquent\Model;


class Country extends Model
{
    protected $table = 'countries';
    public $timestamps = true;
    protected $fillable = [];

}


