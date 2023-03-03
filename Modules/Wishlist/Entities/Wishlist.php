<?php

namespace Modules\Wishlist\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wishlist extends Model {

    protected $fillable = ['user_id','brand_id', 'product_id', 'order_id', 'quantity', 'amount', 'price', 'status'];


}