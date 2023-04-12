<?php

namespace Modules\Wordpress\Entities;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'brand_id',
        'website',
        'api_key',
        'api_password',
        'types'
    ];

}
