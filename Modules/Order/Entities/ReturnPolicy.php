<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;

class ReturnPolicy extends Model {

    protected $fillable = [
        'title',
        'status',
    ];
}
