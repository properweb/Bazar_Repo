<?php

namespace Modules\Brand\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model {

    protected $guarded = [];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($brand) {
            $brand->brand_slug = $brand->createSlug($brand->brand_name);
            $brand->save();
        });
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    private function createSlug($brand_name){
        if (static::whereBrand_slug($brand_slug = Str::slug($brand_name))->exists()) {
            $max = static::whereBrand_name($brand_name)->latest('id')->skip(1)->value('brand_slug');

            if (is_numeric($max[-1])) {
                return preg_replace_callback('/(\d+)$/', function ($matches) {
                    return $matches[1] + 1;
                }, $max);
            }

            return "{$brand_slug}-2";
        }

        return $brand_slug;
    }

}
