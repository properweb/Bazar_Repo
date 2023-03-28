<?php

namespace Modules\Retailer\Entities;

use Illuminate\Database\Eloquent\Model;

class Retailer extends Model
{

    protected $fillable = [
        'user_id',
        'retailer_key',
        'country_code',
        'country',
        'phone_number',
        'state',
        'town',
        'post_code',
        'address1',
        'language',
        'store_name',
        'store_type',
        'store_desc',
        'store_cats',
        'store_tags',
        'store_about',
        'website_url',
        'annual_sales',
        'years_in_business',
        'sign_up_for_email'
    ];
}
