<?php

namespace Modules\Country\Entities;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{

    protected $fillable = [
        'name',
        'state_id'
    ];

}
