<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeaveApproval extends Model
{
	protected $primaryKey = 'id';

    protected $table = 'leave_approvals';

    protected $fillable = [

    	'leave_request_id',
    	'applicant_username',
    	'date_approved',
    	'approved_by',
    	'type',
    	'days',
		'applicant_name',
		'sn'
	];
	
	public function application(){
		return LeaveRequest::find($this->leave_request_id);
	}
}
