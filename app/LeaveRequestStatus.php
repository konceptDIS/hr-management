<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Requests;
use Session;
use App;
use App\LeaveRequest;
use App\LeaveType;
use App\Repositories\LeaveRequestsRepository;
use App\Repositories\OfficesRepository;
use Auth;
use Carbon\Carbon;

class LeaveRequestStatus extends Model
{
	 protected $primaryKey = 'id';

    protected $table = 'leave_request_status';

     protected $fillable = [

    	'leave_request_id',
    	'responder_username', //user name of the status updater
    	'response_date',
    	'role', //Supervisor, HR, MD
    	'response', //True = Approve, False = Deny
    	'remarks', //Useful for providing reasons for denial
    	'stage' //1. Submitted, 2. First App 3. Sec App 4. Third App 
    ];

}
//Email notification per stage

//Stage 0 - You submit
//Stage 1 - Stand In Accepts
//Stage 2 - HR Clerk checks for accuracy, and clears
//Stage 3 - Boss accepts
//Stage 4 - Head HR Accepts
//Stage 5 - 