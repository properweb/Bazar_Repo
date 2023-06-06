<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;

class ReturnReason extends Model {

    protected $fillable = [
        'title',
        'status',
    ];
}
