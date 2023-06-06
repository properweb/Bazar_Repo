<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;

class OrderReview extends Model {

    protected $fillable = [
        'user_id',
        'order_id',
        'rate',
        'review',
    ];
}
