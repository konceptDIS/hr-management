<?php

namespace App\Services;

use \App\LeaveRequest;
use \App\Recall;
use \App\LeaveApproval;
use \App\LeaveEntitlement;
use App\Holiday;
use \App\LeaveTransaction;
use \App\LeaveType;
use Carbon\Carbon;
use \App\User;

use Illuminate\Support\Facades\Log;

class DepositService{

    public static function getDeposits(User $user, LeaveType $leave_type){
        if($user == null){
            throw new \Exception("DepositService::getAnnualDeposits requires an App\User user parameter");
        }

        if($leave_type == null){
            throw new \Exception("DepositService::getAnnualDeposits requires an App\LeaveType leave_type parameter");
        }

        $entitlements = []; //will contain all leave deposits

        if($user->username == "samuel.kyakilika" && $leave_type->name === "Annual"){ // && Carbon::now()->year === 2019){
            try {
	        //i received email insructions to add 22 days to the boss leave in nov 2019
                $dep = self::directDeposit($leave_type, 2019, 11, 1, 22);
                array_push($entitlements, $dep);
		//i received email instructions from (medinat.elelu/joseph.adeniyi/samuel.iheadindu) on August 18, 2020 @ 6:37pm to 
		//add 15 days to the CIO's leave in 2020
	        //because his contract was extended by 6 months up till nov 2020
                $dep = self::directDeposit($leave_type, 2020, 9, 18, 15);
                array_push($entitlements, $dep);
            } catch (\Throwable $th) {
		//dd($th);
                Log::error($th);
            }
        }
        //$data = self::convertToTransactions(self::fillUpBeforeAndAmount($entitlements));
        $data = self::convertToTransactions($entitlements);
	//dd($data);
	return $data;
    }

    private static function addDepositEntryToLogFile($deposit, $counter, $current_year, $start_year){
        $desc = $counter-2 . " annual deposit";
        Log::info(
            [
                'desc' => $desc,
                'counter' => $counter,
                'current_year' => $current_year,
                'start_year' => $start_year,
                "date" => $deposit->date->toDateString(),
                "days" => $deposit->days,
                "remarks" => $deposit->description
            ]);
    }

    public static function fillUpBeforeAndAmount($deposits){
        Log::info('context => DepositService->fillUpBeforeAndAmount');
        $previous = null;
        foreach ($deposits as $deposit) {
            try{
                if($previous && $deposit->leave_type->id == $previous->leave_type->id) {
                    $deposit->before = $previous->days;
                }else{
                    $deposit->before=0;
                }
                $deposit->amount = $deposit->days - $deposit->before;
                $previous = $deposit;
            }catch(\Exception $e){
                Log::error($e->getMessage());
            }
            Log::info(
                [
                    "date" => $deposit->date->toDateString(),
                    "days" => $deposit->days,
                ]);
        }
        return $deposits;
    }

    public static function convertToTransactions($deposits){
        Log::info('context => DepositService->convertToTransactions');
        $transactions = [];
        foreach ($deposits as $deposit) {
            try{
                $t = new  LeaveTransaction();
                $t->date = $deposit->date;
                $t->type = "Deposit";
                $t->leave_type = $deposit->leave_type;
                $t->balance = $deposit->days;
                $t->remarks = $deposit->description;
                $t->before = $deposit->before;
                if(strpos($t->remarks, "increment")){
                    $t->amount = $t->balance - $t->before;
                }else{
                    $t->amount = $deposit->days;
                }
                $t->is_new_year = $deposit->is_new_year;
                $transactions[] = $t;
                $previous = $deposit;
            }catch(\Exception $e){
                Log::error($e->getMessage());
            }
        }
        Log::info('end of => DepositService->convertToTransactions');
        return $transactions;
    }

    /** To handle cases of executive order to deposit */
    private static function directDeposit($leave_type, $year, $month, $day, $days){
        
        $entitlement = new Deposit();
        $pointInTime = Carbon::create($year, $month, $day);
        Log::info("year: $year");
        Log::info("month: $month");
        Log::info("day: $day");
        Log::info("pointInTime: $pointInTime");
        $entitlement->name = $leave_type->name;
        $entitlement->year = $year;
        $entitlement->month = $pointInTime->format('F');
        $entitlement->leave_type = $leave_type;
        $entitlement->type = "Annual";
        $entitlement->description = "Special " . $leave_type->name . " Leave Deposit";
        $entitlement->date = $pointInTime;
        $entitlement->days = $days;
        $entitlement->is_new_year = false;
        return $entitlement;
    }
}
?>
