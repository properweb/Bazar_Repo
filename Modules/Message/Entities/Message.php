<?php

namespace Modules\Message\Entities;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'reciever_id',
        'message',
        'read_at',
    ];
}
