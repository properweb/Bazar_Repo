<?php

namespace Modules\Cart\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model {

    protected $table = 'carts';
    protected $fillable = ['user_id', 'product_id', 'order_id', 'quantity', 'amount', 'price', 'status'];
    
//    public function product()
//    {
//        return $this->belongsTo(Product::class, 'product_id');
//    }
//    public function order(){
//        return $this->belongsTo(Order::class,'order_id');
//    }

}
