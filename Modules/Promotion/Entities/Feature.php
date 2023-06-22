<?php

namespace Modules\Promotion\Entities;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model {

    protected $fillable = [
        'title',
        'amount',
        'days',
    ];

}
