<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model {

    protected $fillable = [
        'return_id',
        'item_id',
        'quantity',
        'reason_id',
    ];
}
