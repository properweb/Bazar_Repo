<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;

class OrderReturn extends Model {

    protected $table = 'returns';

    protected $fillable = [
        'policies',
        'feedback',
        'order_id',
        'shipping_date',
    ];
}
