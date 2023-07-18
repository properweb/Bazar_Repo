<?php

namespace Modules\Brand\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\Product;
use Modules\User\Entities\User;

class Brand extends Model
{

    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the user that owns the brand.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
