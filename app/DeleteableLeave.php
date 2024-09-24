<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeleteableLeave extends Model
{
    protected $primaryKey = 'id';
    
    protected $table = "deleteable_leave";

    protected $fillable = [
        'leave_request_id',
        'reason',
        'created_by',
        'applicant_username',
        'application_data',
        'approval_data'
    ];

    protected $casts = [
        'application_data' => 'array',
        'approval_data' => 'array'
    ];

}
