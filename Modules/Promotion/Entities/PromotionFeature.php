<?php

namespace Modules\Promotion\Entities;

use Illuminate\Database\Eloquent\Model;

class PromotionFeature extends Model {

    protected $fillable = [
        'feature_id',
        'promotion_id',
    ];
}
