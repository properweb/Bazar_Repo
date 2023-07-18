<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Brand\Entities\Brand;
use Modules\Product\Entities\Product;
use Modules\Cart\Entities\Cart;
use Modules\Country\Entities\City;
use Modules\User\Entities\User;
use Modules\Order\Entities\OrderReview;

class Order extends Model {

    protected $fillable = [
        'brand_id',
        'user_id',
        'order_number',
        'sub_total',
        'quantity',
        'delivery_charge',
        'status',
        'total_amount',
        'name',
        'country',
        'post_code',
        'address1',
        'phone',
        'email',
        'payment_method',
        'payment_status',
        'shipping_id',
        'coupon',
        'state',
        'town',
        'promotion_type',
        'promotion_amount',
        'shipping_amount',
        'shipping_date',
        'actualShipDate',
        'shipping_free',
        'user_email',
        'shipping_name',
        'shipping_country',
        'shipping_street',
        'shipping_suite',
        'shipping_state',
        'shipping_town',
        'shipping_zip',
        'shipping_phoneCode',
        'shipping_phone',
        'brand_name',
        'brand_phone',
        'brand_country',
        'brand_state',
        'brand_town'
    ];
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class,'brand_id','user_id');
    }

    public function orderReview()
    {
        return $this->hasOne(OrderReview::class);
    }

}
