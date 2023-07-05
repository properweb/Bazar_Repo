<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model {

    //Declare statuses
    const STATUS = 'manual';
    const SOURCE = 'manual upload';
    const REFERENCE = 'lead';

    protected $fillable = [
        'user_id',
        'retailer_id',
        'customer_key',
        'first_name',
        'last_name',
        'store_name',
        'email',
        'shipping_name',
        'shipping_country',
        'shipping_street',
        'shipping_suite',
        'shipping_state',
        'shipping_town',
        'shipping_zip',
        'shipping_phone_code',
        'shipping_phone',
        'status',
        'source',
        'type',
        'reference'
    ];

    /**
     * Scope a query to only include popular users.
     */
    public function scopeAuth(Builder $query): void
    {
        $query->where('user_id', auth()->user()->id);
    }

}
