<?php

namespace Modules\Promotion\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Promotion extends Model {

    //Declare constants
    const CUSTOMER_TYPE = 1;
    const STATUS_ACTIVE = 'active';

    protected $fillable = [
        'title',
        'user_id',
        'promotion_key',
        'from_date',
        'to_date',
        'type',
        'country',
        'tier',
        'discount_type',
        'ordered_amount',
        'discount_amount',
        'free_shipping',
        'status',
        'products'
    ];

}
