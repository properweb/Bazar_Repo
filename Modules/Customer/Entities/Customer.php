<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model {

    //Declare statuses
    const STATUS = 'manual';
    const SOURCE = 'manual upload';
    const REFERENCE = 'lead';

    protected $fillable = [
        'user_id',
        'customer_key',
        'name',
        'store_name',
        'email',
        'status',
        'source',
        'type',
        'reference'
    ];

}
