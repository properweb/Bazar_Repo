<?php

namespace Modules\order\Entities;

use Illuminate\Database\Eloquent\Model;


class Order extends Model {

    protected $table = 'orders';
    protected $fillable = ['brand_id', 'user_id', 'order_number', 'sub_total', 'quantity', 'delivery_charge', 'status', 'total_amount', 'name', 'country', 'post_code', 'address1', 'phone', 'email', 'payment_method', 'payment_status', 'shipping_id', 'coupon', 'state', 'town', 'shipping_date','user_email','shipping_name','shipping_country','shipping_street','shipping_suite','shipping_state','shipping_town','shipping_zip','shipping_phoneCode','shipping_phone','brand_name','brand_phone','brand_country','brand_state','brand_town'];

}
