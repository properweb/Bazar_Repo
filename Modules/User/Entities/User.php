<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Modules\Shipping\Entities\Shipping;
use Modules\Product\Entities\Product;
use Modules\Brand\Entities\Brand;
use Modules\Retailer\Entities\Retailer;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const ROLE_BRAND = 'brand';
    const ROLE_RETAILER = 'retailer';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'role'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getAllShippings()
    {
        return $this->hasMany(Shipping::class);
    }
    public function getAllProducts()
    {
        return $this->hasMany(Product::class);
    }
    public function brand()
    {
        return $this->hasOne(Brand::class);
    }

    public function retailer()
    {
        return $this->hasOne(Retailer::class);
    }
}
