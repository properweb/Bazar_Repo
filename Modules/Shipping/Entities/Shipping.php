<?php

namespace Modules\Shipping\Entities;

use Illuminate\Database\Eloquent\Model;


class Shipping extends Model
{

    protected $fillable = [
        'name',
        'country',
        'street',
        'suite',
        'state',
        'town',
        'zip',
        'phoneCode',
        'phone',
        'user_id'
    ];
}
