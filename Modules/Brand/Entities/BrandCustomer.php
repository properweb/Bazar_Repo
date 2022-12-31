<?php

namespace Modules\Brand\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BrandCustomer extends Model
{
   protected $fillable = ['user_id','brand_id'];

}
