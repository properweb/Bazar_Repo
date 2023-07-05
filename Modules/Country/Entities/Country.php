<?php

namespace Modules\Country\Entities;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{

    protected $fillable = [
        'shortname',
        'name',
        'phonecode'
    ];

}


