<?php

namespace Modules\Brand\Entities;

use Illuminate\Database\Eloquent\Model;

class BrandCustomer extends Model {

    protected $fillable = ['user_id', 'brand_id'];

}
