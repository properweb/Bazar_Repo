<?php

namespace Modules\Analytic\Entities;

use Illuminate\Database\Eloquent\Model;


class Visit extends Model
{
    protected $fillable = [
        'ip_address',
        'brand_id',
        'orders'
    ];
}
