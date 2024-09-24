<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActionReversal extends Model
{
    protected $primaryKey = 'id';
    
    protected $table = "action_reversals";

    protected $fillable = [
        'leave_request_id',
        'reason',
        'created_by',
        'reversed_action',
        'applicant_username',
        'application_data',
        'approval_data'
    ];

    protected $casts = [
        'application_data' => 'array',
        'approval_data' => 'array'
    ];

}
