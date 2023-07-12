<?php

namespace Modules\Promotion\Entities;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model {

    //Declare constants
    const CUSTOMER_TYPE = 1;
    const STATUS_ACTIVE = 'active';
    const STATUS_DEACTIVATED = 'deactivated';
    const STATUS_COMPLETED = 'completed';

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
        'promotion_type',
        'status'
    ];

}
