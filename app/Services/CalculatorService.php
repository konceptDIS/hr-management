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
use App\Services\DepositService;
use App\Services\EntitlementService;
use App\Services\WithdrawalService;
use App\Services\RefundService;
use Illuminate\Support\Facades\Log;

class CalculatorService{


    public static function getTransactions(User $user, LeaveType $leave_type){
        Log::info("Fetch transactions for $user->username");
        //A list of leave entitlements in transaction data model
        $entitlements = EntitlementService::getDeposits($user, $leave_type);

        //A list of direct leave deposits for this user
	$deposits = DepositService::getDeposits($user, $leave_type);

        //A list of all refunds
        $refunds = RefundService::getRefunds($user, $leave_type);
        //dd($refunds);

        //All withdrawals
        $withdrawals = WithdrawalService::getWithdrawals($user, $leave_type);
        
        $transactions = array_merge($entitlements, $refunds, $withdrawals, $deposits);

        return self::processCollection($transactions, $user,  $leave_type);
    }

    public static function processCollection($leaveTransactions, User $user,  $leave_type){
        // return $leaveTransactions;
        // foreach($leaveTransactions as $transaction){
            
        // }
        //1. merge into a transaction
        $transactions = collect($leaveTransactions);

        //2. sort by date
        $transactions = self::bubbleSort($transactions); 

        
        //4. fill before amount and balance
        $transactions = self::fillBeforeAmountAndBalance($transactions, $user);
        
        //5. Group by year
        $transactions = self::groupByYear($transactions);
        
        //6. Wipe unused carry over leave after July 1
        $transactions = self::wipeCarryOver($transactions, $leave_type, $user);

        //Done: 1. Verify that ordering by date takes place - done
        //Done: 2. Ensure that all leave transaction date fields are real date objects or strings whichever will achieve the sort effect
        //Done: 3. Write a function here that will fill the before, amount and balance fields for all transactions before shipping
        //Done: 4. Group by year
        return $transactions;
    }

    private static function wipeCarryOver($transactions_grouped_by_year, $leave_type, $user){
        if(trim($leave_type->name) != trim("Annual") || $user->isOnProrated()){ 
            return $transactions_grouped_by_year;
        }
        $max_leave_days = $user->getAnnualLeaveEntitlement();
        $now = Carbon::now();
        //$now = Carbon::create(Carbon::now()->year, 1, 1, 0, 0, 0);

        for($year = 2020; $year <= $now->year; $year++){
            //if july first of that year has has not come, skip
            $july_that_year = Carbon::create($year, 7, 1, 0, 0, 0);
        //     if($now->month < $july_that_year->month){
		// //dd("is not yet july");
        //         continue;
        //     }
            //else, get that year's balance and add a withdrawal transation if the balance is in excess
            if(array_key_exists($year, $transactions_grouped_by_year)){
                //get transactions for the current $year
                $year_group = $transactions_grouped_by_year[$year];
                
                //if we have transactions for this year
                if($year_group){
                    
                    $length = sizeof($year_group); //check if we have at least one transaction

                    if($length > 0){
                        $balance_transaction = $year_group[0]; //default to the 1st transaction
                        
			//testing prev july trx
			/*$line = new  LeaveTransaction();
                        $line->leave_type = $leave_type;
                        $date_clone = clone $july_that_year;
                        $line->date = $date_clone->subDay();
                        $line->type = "Withdrawal";
                        $line->before = $balance_transaction->balance;
                        $amount = 5; //$balance_transaction->balance - $max_leave_days;
                        $line->amount = '-' . $amount; //- to make it show as -ve
                        $line->balance = $balance_transaction->balance - $amount;
                        $line->remarks = "";
                        $year_group[$length] = $line;
			$length++;
                        Log::info([
                            "tran_id" => 0,
                            "tran_type" => $line->type,
                            "leave_type" => $leave_type->name,
                            "date" => $line->date,
                            "withdrawal->created_at" => $line->date,
                            "amount" => $line->amount,
                            "remarks" => $line->remarks
                        ]);*/

			//look for the last balance before july 1st that year
                        foreach($year_group as $t){

                            //if you get to a transaction after july first, stop
                            if($t->date->month >= $july_that_year->month){
				break;
			    }
                            $balance_transaction = $t; //set to last transaction before july

                        }
//dd($balance_transaction->date);
                        //check if balance contains carry over, wipe it off
                        if($balance_transaction && $balance_transaction->balance > $max_leave_days){
                            
                            //create a withdrawal transaction to document the wiping off
                            $line = new  LeaveTransaction();
                            $line->leave_type = $leave_type;
                            $line->date = $july_that_year;
                            $line->type = "Withdrawal";
                            $line->before = $balance_transaction->balance;
                            $amount = $balance_transaction->balance - $max_leave_days;
                            $line->amount = '-' . $amount; //- to make it show as -ve
                            $line->balance = $balance_transaction->balance - $amount;
                            $line->remarks = "Unused carry over leave is wiped off on July 1st every year";
                            $year_group[$length] = $line;
                            Log::info([
                                "tran_id" => 0,
                                "tran_type" => $line->type,
                                "leave_type" => $leave_type->name,
                                "date" => $line->date,
                                "withdrawal->created_at" => $line->date,
                                "amount" => $line->amount,
                                "remarks" => $line->remarks
                            ]);

			    //sort by date
			    $year_group = collect($year_group)->sortBy("date");

                            //update the balance of all subsequent transactions
			    $fix = false;
			    $prev = null;
                            foreach($year_group as $t){
				if($t == $line){
				    //its time to start start fixing amount and balance
				    $fix = true;
				}
				if($fix){
				    if($prev){
					$t->before = $prev->balance;
					$t->balance = $t->before + ($t->amount);
				    }
				}
				$prev = $t;
			    }
                        }
                        
                    }
                }
//dd($year_group);
                $transactions_grouped_by_year[$year] = $year_group;
            }
        }
        return $transactions_grouped_by_year;
    }

    private static function groupByYear($transactions){
        Log::info("context => CalculatorService->groupByYear");
        $groups = [];
        foreach ($transactions as $transaction) {
            try {
                if (!array_key_exists($transaction->date->year, $groups)) {
                    $groups[$transaction->date->year] = [];
                }
                array_push($groups[$transaction->date->year], $transaction);
            } catch (\Throwable $t) {
                Log::error($t);
            }
        }
        return $groups;
    }

    private static function appliedOnlineLastYear(User $user, $range){
        Log::info(["function_start" => "appliedOnlineLastYear", "username" => $user->username]);
        $has_online_approvals = LeaveApproval::where('applicant_username', '=',$user->username)
                ->whereBetween('created_at', $range)
                ->where('leave_request_id', '>', 0)->count()>0;
        if(!$has_online_approvals){
            $has_online_approvals = LeaveRequest::where('created_by', '=',$user->username)
            ->where('supervisor_response', '=',true)
            ->where('stand_in_response', '=',true)
            ->whereBetween('created_at', $range)
            ->count()>0;
        }
        Log::info(["function_end" => "appliedOnlineLastYear", "has_online_approvals" => $has_online_approvals, "username" => $user->username]);
        return $has_online_approvals;
    }

    private static function getLastYearRangeForUser(User $user, LeaveTransaction $transaction){
        $from = clone $transaction->date;
        $year = $transaction->date->year;
        $anniversary_date = clone $user->carbonResumptionDate();
        $anniversary_date->addYear();
        // dd(["transaction_date" => $transaction->date, "anniversary_date" => $anniversary_date]);
        //to ensure an exact comparison, in case transaction date has hours, mins and seconds
        $anniversary_date->addHours($transaction->date->hour);
        $anniversary_date->addMinutes($transaction->date->minute);
        $anniversary_date->addSeconds($transaction->date->second);
        //if transaction was in annivesary year
        $was_in_anniversary_year = false;
        $range = [];
        if($anniversary_date->greaterThanOrEqualTo($transaction->date)){
            $to = $transaction->date;
            $from->addYear(-1);
            $was_in_anniversary_year = true;
            $range = [ 'from' => $from, 'to' => $to];
        }
        else{
            $year--;
            $range = [ 'from' => Carbon::create($year, 1, 1), 'to' => Carbon::create($year, 12, 31)];
        }
        Log::info(["function" => "getLastYearRangeForUser", "input_date" => $transaction->date, "verdict of was_in_anniversary_year?" => $was_in_anniversary_year, "anniversary_date" => $anniversary_date, "username" => $user->username]);
        Log::info($range);
        return $range;
    }

    private static function fillBeforeAmountAndBalance($transactions, User $user){
        $previous = null;
        $data  = [];
        foreach($transactions as $transaction){
            if ($previous != null) {

                $opening_balance = $previous->balance; //becaue we will need to modify the copied wewillt
                //dont carry over over more than 10 years into a new year
                if ($transaction->is_new_year) {
                    if ($transaction->leave_type  && $transaction->leave_type->name  && $transaction->leave_type->name === "Annual"){
                        $last_year_range = self::getLastYearRangeForUser($user, $transaction);
                        $applied_online_last_year = self::appliedOnlineLastYear($user, $last_year_range);
                        if ($applied_online_last_year) {
                            Log::info(["applied_online_last_year" => $applied_online_last_year, "last_year_range" => $last_year_range, "username" => $user->username]);
                            Log::info("Balance before carry over only 10 check $transaction->balance");
                            // $opening_balance = min(10, $previous->balance);
                            $opening_balance = 0;
                            //explain why the opening balance is less than the closing balance
                            if (($previous->balance > $opening_balance) or ($transaction->date->month === 1 && $transaction->date->day === 1 && $opening_balance > 0)) {
                                $transaction->remarks = $transaction->remarks . " plus " . $opening_balance . " days carried over from last year";
                            }
                            Log::info("Balance after carry over only 10 check $opening_balance");
                        }else{
                            //dont carry over balance for those who failed to secure online approval last year
                            Log::info("User did not apply online last year, so not carrying over any annual leave");
                            $transaction->remarks = $transaction->remarks . " (No carry over as only those who applied online last year get carry over)";
                            $opening_balance = 0; //ensure he carries nothing over
                        }   
                    }else{
                        //dont carry over last years balance if its not annual leave
                        $opening_balance = 0; 
                    }
                }
                //fill the before of the current row using the previous rows balance
                $transaction->before = $opening_balance; 
            }
            $transaction->balance = $transaction->before + $transaction->amount;
            Log::info([
                "date" => $transaction->date->toDateString(), 
                "type" => $transaction->type,
                "leave_type" => $transaction->leave_type ? $transaction->leave_type->name : null,
                "before" => $transaction->before,
                "amount" => $transaction->amount,
                "balance" => $transaction->balance,
                "remarks" => $transaction->remarks,
                "is_new_year" => $transaction->is_new_year,
                ]);
            $previous = $transaction;
        }
        return $transactions;
    }

    private static function fillBeforeAmountAndBalance1($transactions, User $user){
        $previous = null;
        $data  = [];
        foreach($transactions as $transaction){
            if($previous != null){
                
                //dont carry over over more than 10 years into a new year
                if($transaction->is_new_year){
                    if($transaction->leave_type->name === "Annual"){
                        Log::info("Balance before carry over only 10 check $transaction->balance");
                        // $previous->balance = min(10, $previous->balance);
                        $previous->balance = 0;
                        Log::info("Balance after carry over only 10 check $previous->balance");
                    }
                }
                if ($transaction->leave_type->name !== "Annual" && $transaction->is_new_year) {
                    $transaction->before = 0; //dont carry over last years balance if its not annual leave
                }else{
                    //dont carry over balance for those who failed to secure online approval last year
                    if (self::appliedOnlineLastYear($user, $transaction->date->year)) { 
                        $transaction->before = 0;
                    }else{
                        $transaction->before = 0;
                    }
                }
                $transaction->balance = $transaction->before + $transaction->amount;
            }
            Log::info([
                "date" => $transaction->date->toDateString(), 
                "type" => $transaction->type,
                "leave_type" => $transaction->leave_type->name,
                "before" => $transaction->before,
                "amount" => $transaction->amount,
                "balance" => $transaction->balance,
                "remarks" => $transaction->remarks,
                "is_new_year" => $transaction->is_new_year,
                ]);
            $previous = $transaction;
        }
        return $transactions;
    }

    private static function bubbleSort($leaveTransactions){
        if($leaveTransactions === null){
            Log::info(['context' => 'CalculatorService.bubbleSort', 'message' => "Parameter leave transactions was null, aborting"]);
            return $leaveTransactions;
        }
        $size = sizeof($leaveTransactions);
        for($i = 0; $i < $size; $i++){
            $current = $leaveTransactions[$i];
            for($j = $i+1; $j < $size; $j++){
                $next = $leaveTransactions[$j];
                if($leaveTransactions[$i]->date->greaterThan($leaveTransactions[$j]->date)){
                    $temp = $leaveTransactions[$i];
                    $leaveTransactions[$i] = $leaveTransactions[$j];
                    $leaveTransactions[$j] = $temp;
                }
            }
        }
        return $leaveTransactions;
    }

    

}

?>