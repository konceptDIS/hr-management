<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Auth;

class Recall extends Model
{
    protected $fillable = [
        'id',
        'leave_approval_id',
        'applicant_username',
        'date_recalled',
        'reason',
        'supervisor_username',
        'supervisor_response',
        'supervisor_response_reason',
        'supervisor_response_date',
        'days_credited',
        'leave_type',
        'leave_approval_date',
        'name',
        'approval',
        'application'
    ];

    public function getStatus(){
        if($this->supervisor_response === 1){
            return 'Approved';
        }else if($this->supervisor_response === 0){
            return 'Dismissed';
        }
        return 'Pending';
    }

   
}
