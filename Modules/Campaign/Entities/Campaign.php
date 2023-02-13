<?php

namespace Modules\Campaign\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Campaign extends Model {

    //Declare statuses
    const STATUS_DRAFT = 'draft';

    protected $fillable = [
        'title',
        'user_id',
        'campaign_key',
        'subject',
        'preview_text',
        'email_design',
        'scheduled_date',
        'status'
    ];

}
