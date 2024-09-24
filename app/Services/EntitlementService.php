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

class EntitlementService{

    public static function getDeposits(User $user, LeaveType $leave_type){
        if($user == null){
            throw new \Exception("DepositService::getAnnualDeposits requires an App\User user parameter");
        }

        if($leave_type == null){
            throw new \Exception("DepositService::getAnnualDeposits requires an App\LeaveType leave_type parameter");
        }

        $entitlements = []; //will contain all leave deposits

        //if hired before 2016 -> return max values for each leave type
        if($user->carbonResumptionDate()->year <= 2015){
            $entitlements = self::getNonIncrementalLeaveDeposit($user, $entitlements, $leave_type);
        }
        else{
            //if hired afterwards, apply pro rating for 1st year
            if ($user->carbonResumptionDate()->year >= 2016) {
                if ($leave_type->name === "Annual") {//if annual leave do the incremental thing for the first 12 months
                    $entitlements = self::getAnnualLeaveDepositsForThoseEmployedAfter2016($user, $entitlements, $leave_type);
                } else { //is not annual leave, so give him the full yearly deposit
                    $entitlements = self::getNonIncrementalLeaveDeposit($user, $entitlements, $leave_type);
                }
            }
        }

        if($leave_type->name === "Maternity"){
            //get and deduct annual leave taken
            $annual_leave = LeaveType::where('name', "Annual")->first();
            $annual_leave_withdrawals_transactions = WithdrawalService::getWithdrawals($user, $annual_leave);
            $maternity_deposit_transactions = self::convertToTransactions($entitlements);
            $maternity_deposit_transactions = self::deductAnnualLeaveTakenFromMaternityLeaveBalance($maternity_deposit_transactions, $annual_leave_withdrawals_transactions);
            return $maternity_deposit_transactions;
        }
        return self::convertToTransactions(self::fillUpBeforeAndAmount($entitlements));
    }

    private static function getNonIncrementalLeaveDeposit(User $user, array $entitlements, LeaveType $leave_type){
        $start_year = $user->carbonResumptionDate()->year;
        $this_year = Carbon::now()->year; 
        $month =1;
        $day = 1;
        while($start_year <= $this_year){
            if ($start_year < 2017) {
                $start_year++;
                continue; //dont start processing until we gt to 2017 
            }
            $deposit = self::getAnnualDeposits($user, $start_year, $month, $leave_type, $day);
            $deposit->is_new_year = true; //because non incremenatal deposits happen once yearly
            array_push($entitlements, $deposit);
            $start_year++;
        }
        return $entitlements;
    }

    private static function getAnnualLeaveDepositsForThoseEmployedAfter2016(User $user, array $entitlements, $leave_type){
        $this_year = Carbon::now()->year;
        $hire_date = clone $user->carbonResumptionDate();
        $deposit_start_date = clone $user->carbonResumptionDate();
        $first_year_anniversary_date = clone $user->carbonResumptionDate();
        $first_year_anniversary_date->addYear();
        
        //move forward 6 months, because leave depositions begin 6 months after hire
        $deposit_start_date->addMonths(6);
        
        $start_day = $deposit_start_date->day; 

        $start_year = $deposit_start_date->year; //start year
        $current_year = $deposit_start_date->year; 
        $current_month = $deposit_start_date->month; 
        $start_month = $deposit_start_date->month; 
        $month_counter_date = null;
        $is_within_first_year = true;
        $is_future = false;
        $counter = 1;
        
        //what if i was hired in November, does this still work? Yes because the inner while loop handles the monthly increment
        while($current_year <= $this_year){
            if($is_future) break;
            $counter++;
            Log::info("Hire date: " . $hire_date->year . '/' . $hire_date->month . '/' . $hire_date->day);
            Log::info("Deposit start date: " . $start_year . '/' . $start_month . '/' . $start_day);
            Log::info("Pass". $counter . " current year: " . $current_year . " this_year: " . $this_year);

            // $check_date = Carbon::create($current_year, $current_month, $start_day-1);
            Log::info(["month_counter_date" => $month_counter_date, "first year anniversary_date" => $first_year_anniversary_date]);
            
            Log::info("Is within first year => " . $is_within_first_year);
            
            if($is_within_first_year){//$current_year === $user->carbonResumptionDate()->year
        
                //get the monthly deposits for the next 6 months
                $month_counter = 1;
                while ($month_counter <= 6 && !$is_future) {
                    Log::info("month counter:" . $month_counter . " current year: " . $current_year . " current_month: " . $current_month);
                    
                    $deposit = self::getMonthlyDeposits($user, $current_year, $current_month, $month_counter);
                    array_push($entitlements, $deposit);
                    self::addDepositEntryToLogFile($deposit, $counter, $current_year, $start_year);

                    $current_month++;
                    $start_month++;
                    $month_counter++;
                    if($start_month == 13){
                        $current_year++; //switch to new year
                        $current_month = 1; //switch to january
                    }
                    $month_counter_date = Carbon::create($current_year, $current_month, $start_day, 0, 0, 0);                    
                    $is_within_first_year = $first_year_anniversary_date->greaterThan($month_counter_date);
                    $is_future = $month_counter_date->greaterThan(Carbon::now());
                    // $counter++;
                }
                continue;
            }
            
            //anniverary date deposit
            if($month_counter_date and $month_counter_date->equalTo($first_year_anniversary_date) && Carbon::now()->greaterThan($month_counter_date)){
                Log::info("Anniversary date deposit");
                //if we enter here we are in the anniversary date
                $deposit = self::getAnnualDeposits($user, $current_year, $hire_date->month, $leave_type, $hire_date->day);
                array_push($entitlements, $deposit);
                self::addDepositEntryToLogFile($deposit, $counter, $current_year, $start_year);
                $month_counter_date = null; //so that anniveray date deposit is not repeated
                $current_year++;
                continue;
            }
            if(Carbon::now()->year > $first_year_anniversary_date->year){
                Log::info("Post anniversary date deposit");
                //thereafter let teh annual deposits drop in january
                $deposit = self::getAnnualDeposits($user, $current_year, 1, $leave_type);
                array_push($entitlements, $deposit);
                self::addDepositEntryToLogFile($deposit, $counter, $current_year, $start_year);
                $current_year++;
                continue;
            }
            //if we get this far we have to exit the loop;
            $current_year++;
        }
        return $entitlements;
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

    /* Get leave entitlement for every leave type in the selected year */
    public static function getAnnualDeposits(User $user, int $year, int $month = 1, LeaveType $leave_type, $day = 1){
        if($user == null){
            throw new \Exception("DepositService::getAnnualDeposits requires an App\User user parameter");
        }
        if($year == null){
            throw new \Exception("DepositService::getAnnualDeposits requires an int year parameter");
        }
        if($month == null){
            throw new \Exception("DepositService::getAnnualDeposits requires an int month parameter");
        }
        if($leave_type == null){
            throw new \Exception("DepositService::getAnnualDeposits requires an leave_type parameter");
        }
        $entitlement = new Deposit();
        $pointInTime = Carbon::create($year, $month, $day);
        Log::info("year: $year");
        Log::info("month: $month");
        Log::info("day: $day");
        Log::info("point in time: $pointInTime");
        $entitlement->name = $leave_type->name;
        $entitlement->year = $year;
        $entitlement->month = $pointInTime->format('F');
        $entitlement->leave_type = $leave_type;
        $entitlement->type = "Annual";
        $entitlement->description = $leave_type->name . " Leave Deposit";
        $entitlement->date = $pointInTime;
        $entitlement->days = self::getDaysEntitledToAsOfXPointInTime($leave_type, $user, $pointInTime);
        $entitlement->is_new_year = true;
        return $entitlement;
    }

    /* Get all leave entitlements for all leave types for this user since hire date */
    public static function getAllAnnualDeposits(User $user, LeaveType $leave_type){
        if($user == null){
            throw new \Exception("DepositService::getAllAnnualDeposits requires an App\User user parameter");
        }
        $year = $user->carbonResumptionDate()->year;
        $this_year = Carbon::now();
        $entitlements = [];
        $leave_types = LeaveType::all();
        foreach ($leave_types as $leave_type) {
            while ($year <= $this_year) {
                array_push($entitlements, self::getAnnualDeposits($user, $year, $leave_type));
                $year++;
            }
        }
        return $entitlements;
    }

    private static function getPositionName(int $index){
        $positional_names = ['Zero', 'Sixth', 'Seventh', 'Eighth', 'Ninth', 'Tenth', 'Eleventh'];
        if($index >= 0 && $index <= 7 ){
            return $positional_names[$index];
        }
        return "Unknown";
    }

    /* Get all leave entitlements for this user, in the selected month of the selected year */
    public static function getMonthlyDeposits(User $user, int $year, int $month, int $month_counter){
        
        if($user == null){
            throw new \Exception("DepositService::getMonthlyDeposits requires an App\User user parameter");
        }
        if($month == null){
            throw new \Exception("DepositService::getMonthlyDeposits requires an int month parameter");
        }
        if($year == null){
            throw new \Exception("DepositService::getMonthlyDeposits requires an int year parameter");
        }
        $day = $user->carbonResumptionDate()->day;
        $this_year = Carbon::now();
        $leave_type = LeaveType::where('name', 'Annual')->first();
        $pointInTime = Carbon::create($year, $month, $day);
        $entitlement = new Deposit();
        $entitlement->name = $leave_type->name;
        $entitlement->year = $year;
        $entitlement->type = "Monthly";
        $entitlement->description = self::getPositionName($month_counter) . " month annual leave increment (for new staff)";
        $entitlement->leave_type = $leave_type;
        $entitlement->month = $pointInTime->format('F');
        $entitlement->date = $pointInTime;
        $entitlement->is_new_year = false;
        $entitlement->days = self::getDaysEntitledToAsOfXPointInTime($leave_type, $user, $pointInTime);
        return $entitlement;
    }

     //-------------------------------------------------------------------------------------------------//
    //
    // This function returns the number of leave days a user is entitled
    // ---->It does not deduct the days a user has taken<------
    // Parameter 1 : LeaveType e.g. Annual 
    // Parameter 2 : The User
    // Parameter 3 : The Point in time
    //-------------------------------------------------------------------------------------------------//
    public static function getDaysEntitledToAsOfXPointInTime(LeaveType $leave_type, \App\User $user, Carbon $pointInTime = null){
        //echo("<br/>================================START getDaysEntitledToAsOfXPointInTime=================================");
        //echo("<br/>" . __LINE__ . $this->indent .  "pointInTime: $pointInTime");
       
        $days_since_resumption = self::calculateDaysSinceResumption($user, $pointInTime);
        
        if($days_since_resumption>360){ 
           //grant old (1 year+) staff the max privileges 
           $days_since_resumption = 360;
        }

        //echo("<br/>" . __LINE__ .$this->indent . " Days since resumption efore upper ceiling and flooring " . $days_since_resumption);
        
        //This algorithm works perfectly if the days since resumption is not factor or multiple of 30
        $upper_bound = ceil($days_since_resumption/30)*30;
        $lower_bound = floor($days_since_resumption/30)*30;
        $upper_bound -=1;

        //echo("<br/>" . __LINE__ . $this->indent . "days since resumption: ");////echo($days_since_resumption);
        //echo("<br/>" . __LINE__ . $this->indent . "Band before %30 code block");
        //echo("<br/>" . __LINE__ . $this->indent . "Lower band: " .  $lower_bound . " and upper band: ". $upper_bound);
        
        //This block of code takes care of the case where the days since resumption is a factor or multiple of 30
        if($days_since_resumption%30==0){
            $upper_bound = $days_since_resumption;
            $lower_bound = $days_since_resumption-29; //floor(($days_since_resumption-1)/30)*30;//$days_since_resumption;
        }
        if($days_since_resumption>=360){ //grant old (1 year+) staff the max privileges
            $lower_bound = $upper_bound = 360;
        }
        //echo("<br/>" . __LINE__ . $this->indent . "Band AFTER %30 code block");
        //echo("<br/>" . __LINE__ . $this->indent .  "LOWER Band: " .  $lower_bound . " and UPPER band: ". $upper_bound);
        
        $daysEntitledTo = \App\LeaveEntitlement::
        where('leave_type','=',$leave_type->name)
        ->where('salary_grade', '=', $user->salary_grade)
        ->where('days_since_resumption', '>=', $lower_bound)
        ->where('days_since_resumption', '<=', $upper_bound)
        ->sum('days_allowed');
    
        if($leave_type->name =='Compassionate' or $leave_type->name =='Paternity' or $leave_type->name =='Maternity' or $leave_type->name =='Examination'){
            $daysEntitledTo = \App\LeaveEntitlement::
            where('leave_type','=', $leave_type->name)
            ->where('salary_grade', '=', $user->salary_grade)
            ->max('days_allowed');
            // dd($daysEntitledTo);
        }

        if($user->is_contract_staff or $days_since_resumption>=360){
            $daysEntitledTo = LeaveEntitlement::
            where('leave_type','=',$leave_type->name)
            ->where('salary_grade', '=', $user->salary_grade)
            ->max('days_allowed');
        }
        
        // if(strtolower($leave_type->name) == "maternity"){
        //     $annualLeave = new LeaveType();
        //     $annualLeave->name = "Annual";
        //     $annualLeaveTaken = $this->queryLeaveApprovalsForDaysTaken($annualLeave, $user, $pointInTime);
        //     if(isset($annualLeaveTaken)){
        //         $daysEntitledTo = $daysEntitledTo - $annualLeaveTaken;
        //     }
        // }     
        //echo("<br/>" . __LINE__ . " Days entitled to at this point : " . $daysEntitledTo);
        //echo("<br/>================================END getDaysEntitledToAsOfXPointInTime=================================");
        return $daysEntitledTo;
    }

    public static function calculateDaysSinceResumption(\App\User $user, Carbon $pointInTime=null){
  
        if(!isset($pointInTime)){
        $pointInTime = Carbon::now();
        }
        if($user->resumption_date==null){
        return 360; //assume the user has spent a year if the resumption date is not set
        }
        $resumption_date = Carbon::parse($user->resumption_date);
        if($pointInTime < $resumption_date){
            return 0;
        }
        //////var_dump('<br/>pointInTime: ' . $pointInTime. '<br/>');
        $days_since_resumption = $resumption_date->diffInDays($pointInTime);
        //////var_dump('$days_since_resumption: ' . $days_since_resumption. '<br/>');
        return $days_since_resumption;
    }

    public static function deductAnnualLeaveTakenFromMaternityLeaveBalance($maternity_deposit_transactions, $annual_leave_withdrawal_transactions){
        Log::info("context => DepositService->deductAnnualLeaveTakenFromMaternityLeaveBalance");
        $maternity_leave_deductions = [];
        $size = 0;
        foreach($annual_leave_withdrawal_transactions as $transaction){
            $amount = 0;
            if($transaction->type === "Withdrawal" && $transaction->leave_type->name === "Annual"){
                //we have something to deduct from maternity leave balance
                $maternity_leave_deductions[] = [$transaction->date->year, $transaction->amount];
                $size++;
            }
        }
        // dd($maternity_leave_deductions);
        $total = 0;
        for($i = 0; $i < $size; $i++) {
            $year = $maternity_leave_deductions[$i][0];
            $days = $maternity_leave_deductions[$i][1];
            foreach ($maternity_deposit_transactions as $transaction) {
                if($transaction->date->year === $year && $transaction->leave_type->name === "Maternity" && $transaction->type === "Deposit"){
                    try{
                        $total += abs((int)$days);
                    }catch(\Exception $e){}
                    $transaction->amount += $days;
                    if($total != 0 && $i == $size-1)
                        $transaction->remarks = $transaction->remarks  . "  minus  annual leave days already taken"; 
                    break;
                }else{
                    
                    // $total = 0;
                    Log::info([
                        "year_of_interest" => $year,
                        "message" => "this transaction is not  a maternity leave deposit in the year of interest",
                        "year" => $transaction->date->year,
                    "leave type" => $transaction->leave_type->name,
                    "type" => $transaction->type
                    ]);
                }
            }
        }   
        return $maternity_deposit_transactions;
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
        // foreach($transactions as $transaction){
        //     Log::info([
        //         "date" => $transaction->date->toDateString(), 
        //         "type" => $transaction->type,
        //         "leave_type" => $transaction->leave_type->name,
        //         "before" => $transaction->before,
        //         "amount" => $transaction->amount,
        //         "balance" => $transaction->balance,
        //         "remarks" => $transaction->remarks,
        //         "is_new_year" => $transaction->is_new_year,
        //         ]);
        // }
        Log::info('end of => DepositService->convertToTransactions');
        return $transactions;
    }
}
?>
