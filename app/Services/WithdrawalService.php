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

use Illuminate\Support\Facades\Log;

class WithdrawalService{

    public static function getAllWithdrawals(User $user){
        $leave_types = LeaveType::where('gender', $user->gender);
        $entries = [];
        foreach($leave_types as $leave_type){
            array_merge($entries, self::getWithdrawals($user, $leave_type));
        }
    }

    public static function getWithdrawals(User $user, LeaveType $leave_type){
        if($user == null){
            throw new \Exception("self::getWithdrawals requires an App\User user parameter");
        }
        if($leave_type == null){
            throw new \Exception("WithdrawalService::getWithdrawals requires an App\LeaveType leave_type parameter");
        }
        Log::info(["context"=>"WithdrawalService->getWithdrawals", "user"=> $user->username, "leave_type" => $leave_type->name]);
        $this_year = Carbon::now()->year;
        $start_year = $user->carbonResumptionDate()->year;
        while($start_year < 2016){
            $start_year++;
        }
        // $approvals = LeaveApproval::where('applicant_username', $user->username)->get();
        //new - withdrawals in 2019 == 2019 applications that got approved, irrespective of when the approval happend
        $leave_request_ids_in_target_year = LeaveRequest::where('created_by', $user->username)
        ->whereYear('created_at', '>=', $start_year)
        ->whereYear('created_at', '<=', $this_year)
        ->where('leave_type', '=', $leave_type->name)
        ->get(['id']);

        $withdrawals = LeaveApproval::whereIn('leave_request_id', $leave_request_ids_in_target_year)->get();

        $transactions = [];
        for ($i=0; $i < sizeof($withdrawals); $i++) { 

            //get this transaction
            $withdrawal = $withdrawals[$i];

            //add to the transaction log
            $line = new  LeaveTransaction();
            $line->leave_type = $leave_type;
            $line->date = $withdrawal->created_at; //July 27 2019 - Because if approved this is the correct start date 
            $line->type = "Withdrawal";
            $line->amount = '-' . $withdrawal->days; //making it show as negative
            if($withdrawal->application()){
                $line->remarks = $withdrawal->application()->reason;
                if($withdrawal->application()->created_at){
                    $line->date = $withdrawal->application()->created_at;
                }else{
                    $line->date = Carbon::parse($withdrawal->application()->created_at); //July 27 2019 - Because if approved this is the correct start date
                }
            }
            $line->application_id = $withdrawal->leave_request_id;
            $line->approval_id = $withdrawal->id;

            $transactions[$withdrawal->leave_request_id] = $line;
            Log::info([
                "tran_id" => $withdrawal->id,
                "tran_type" => $line->type,
                "leave_type" => $leave_type->name,
                "date" => $line->date,
                "withdrawal->created_at" => $withdrawal->created_at,
                "amount" => $line->amount,
                "remarks" => $line->remarks,
                "application_id" => $line->application_id,
                "approval_id" => $line->approval_id
            ]);
        }
        return $transactions;
    }

}
?>