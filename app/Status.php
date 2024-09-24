<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $fillable = [

    	'leave_request_id',
    	'created_by', //user name of the status updater
    	'date_created',
    	'role', //Supervisor, HR, MD
    	'action', //Approve, Deny,
    	'remarks', //Useful for providing reasons for denial
    	'stage' //1. Submitted, 2. First App 3. Sec App 4. Third App 
    ];

    protected $dates = ['date_created'];
}
