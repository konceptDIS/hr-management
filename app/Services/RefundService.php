<?php

namespace App\Services;

use App\LeaveRequest;
use App\Recall;
use App\LeaveApproval;
use App\Holiday;
use \App\LeaveTransaction;
use \App\LeaveType;
use Carbon\Carbon;
use \App\User;

use Illuminate\Support\Facace\Log;

class RefundService{

    public static function getRefunds(User $user, LeaveType $leave_type){
        if($user == null){
            throw new \Exception("self::getRefunds requires an App\User user parameter");
        }
        if($leave_type == null){
            throw new \Exception("self::getRefunds requires an App\LeaveType user parameter");
        }
        $refunds = Recall::where('applicant_username', $user->username)
        ->where('leave_type', '=', $leave_type->name)
        ->where('supervisor_response', '=', true)
        ->get();
        $transactions = [];
        foreach($refunds as $item){ 
            //add to the transaction log
            $t = new  LeaveTransaction();
            $t->date = $item->created_at;
            $t->type = "Refund";
            $t->amount = $item->days_credited;
            $t->remarks = "Refund of ". lcfirst($leave_type->name) . " leave"; 
            $t->application_id = $item->leave_request_id;
            $t->approval_id = $item->id;
            $transactions[] = $t;
        }
        // dd($transactions);
        return $transactions;
    }
}
?>