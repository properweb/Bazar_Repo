<?php

namespace Modules\Wordpress\Entities;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'website',
        'api_key',
        'api_password',
        'types',
        'actions'
    ];
}
