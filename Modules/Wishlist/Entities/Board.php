<?php

namespace Modules\Wishlist\Entities;

use Illuminate\Database\Eloquent\Model;

class Board extends Model {

    protected $fillable = ['board_key','name', 'visibility'];

    public function whishlist()
    {
        return $this->hasMany(Wishlist::class);
    }
}
