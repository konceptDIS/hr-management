<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\LeaveRequest;
use \App\LeaveApproval;
use \App\LeaveType;
use \App\LeaveTransaction;
use \App\Recall;
use \App\LeaveEntitlement;
use \App\LeaveHistoryReport;
use \App\LeaveHistoryViewModel;
use \App\LeaveHistoryViewModelLineItem;
use Carbon\Carbon;
use \App\User;
use Auth;
use DB;
use \App\Services\CalculatorService;

class HistoryController extends Controller
{
    //=============================================================================
    // Page Level Var
    //=============================================================================
    var $last_years_total_deposit = 0;
    var $last_years_balance = 0;
    var $annual_to_be_deducted_from_maternity = [];
    //=============================================================================
    // End of Page Level Var
    //=============================================================================

    var $page_name = "<br/>HC: ";
    var $indent = "------------>";
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('session-timeout');
    }

    public function Index(Request $request, $returnData = null){
        $user = null;
        $username = "";
        if($request->has('username')){
            $username = $request->input('username');
            $user = User::where('username', $username)->first();
            if($user->username != Auth::user()->username && Auth::user()->hasRole('HR') == FALSE){
                $user = $request->user();
            }
        }
        $id = "";
        if($request->has('id')){
            $id = $request->input('id');
            $user = User::where('id', $id)->first();
        }
        if(!$user){
            Log::info(["exception" => "User not found", "id" => $id, "username" => $username]);
            return "User was not found";
        }
        $title = " Leave Activity of " . $user->name;
        //echo("<br/>" . __LINE__ . $this->indent .  $title);
        $leaves = LeaveType::all();
        $entries = [];
        foreach($leaves as $leave){
            if($user->gender == "Male" && $leave->name == "Maternity"){
                continue;
            }
            if($user->gender == "Female" && $leave->name == "Paternity"){
                continue;
            }
            if($leave->name == "Casual"){
                continue;
            }
            $entries[$leave->name] = CalculatorService::getTransactions($user, $leave);
        }
        //$reports = $this->generate($user);
        return view('history.index', compact('entries', 'title'));
    }
    
    /**
     * Deprecated, use Calculator Service
     */
    public function generate($user){
        // ////echo "This page will explain a users leave history in detail. Please stay tuned";
        // return;
        $previous_annual_balance;
        $has_online_approvals;
        
        if($user == null){
            ////echo "I didnt find that user so, I got confused, I'm sorry.";
            return;
        }

        $leaveTypes = LeaveType::where('name', 'Annual')->get();
        
        $resumption_date = Carbon::parse($user->resumption_date);
        //echo("<br/>" . __LINE__ . $this->indent . "Hire Date: " . $resumption_date->toDateString());
        $ad = clone $resumption_date;
        //echo("<Br/>Anniversary Date: " . $ad->addYear()->toDateString());
        // $start_date = clone $resumption_date;
        // $end_date = Carbon::create($start_date->year, 12, 31);
        // $days_old = Carbon::now()->diffInDays($start_date);
        // if($days_old < 366){
        //     //use resumption year
        //     $end_date = $end_date->addYear();
        // }
        // else{
        //     //use calendar year
        //     $today = Carbon::now();
        //     $start_date = Carbon::create($today->year-1, 1,1);
        //     $end_date = Carbon::create($today->year, 12,31);
        // }
        //echo("<br/>Before Year Loop");
        //echo("<br/>Start date: " . $start_date);
        //echo("<br/>End date: " .$end_date); 
        $years = $this->getYears($user);

        $reports = []; //stores the report for each year
        //echo("<br/>" . __LINE__ . $this->indent . "Number of years with aedc = " . sizeof($years));
        //echo("<br/>" . __LINE__ . $this->indent . "About to generate report for each year beginning in 2017");
        for ($i=0; $i < sizeof($years); $i++) { 
            //#$year = $years[$i];
            //echo("<br/>" . __LINE__ . $this->indent .  " Current Year: " . $year);
            
            $current_year_loop = $i;
            
            // if($days_old > 365){ //Use calendar year for staff more than 1 year old
            //oridinarily start date should be year start date
            $start_date = $years[$i][0];//Carbon::create($year->year, 1,1, 0,0,0);
                //echo("<br/>Start date: " . $start_date);
            //If current index year  == year user resumed, shift the start date to his resumption date
            //#$start_date = $user->carbonResumptionDate()->year == $year->year ? Carbon::create($year->year, $user->carbonResumptionDate()->month, $user->carbonResumptionDate()->day, 0,0,0) : $start_date;
                //echo("<br/>Start date after first year check: " . $start_date);

            //oridinarily end date should be year end date
            $end_date = $years[$i][1]; //Carbon::create($year->year, 12,31, 23,59, 59);
            /*if($user->carbonResumptionDate()->year === $year->year){
                //If the current year == users hire year, dont use calendar year end as end date - instead use anniversary date
                $end_date = Carbon::create($year->year + 1, $user->carbonResumptionDate()->month, $user->carbonResumptionDate()->day -1, 0,0,0);
            }
            if($year->year===Carbon::now()->year){ //prevent processing into the future
                $today = Carbon::now();
                $end_date = Carbon::create($year->year, $today->month, $today->day, $today->hour,$today->minute, $today->second);
            }

            //echo("<br/>Start date: " . $start_date);
            //echo("<br/>End date: " .$end_date); 
        	*/
            $report = new LeaveHistoryReport();
            $report->year = $start_date->year;
            $report->entries = [];
            for ($y =0; $y<sizeof($leaveTypes); $y++) {
                
                $type = $leaveTypes[$y];
                if($user->gender == "Male" && $type->name == "Maternity"){
                    continue;
                }
                if($user->gender == "Female" && $type->name == "Paternity"){
                    continue;
                }
                if($type->name == "Casual"){
                    continue;
                }
                $leave_type = new LeaveHistoryViewModel();
                $leave_type->name = $type->name;
                //Commented out while tryin to solve George.Chinda's issue -> clocked six months but balance not found - Mar 26, 2019
                // $leave_type->entries = $this->getEntries($user, $start_date, Carbon::create($year->year, 12, 31, 23,59, 59), $type);
                $leave_type->entries = $this->getEntries($user, $start_date, $end_date, $type);
                $report->entries[$y] = $leave_type;
            }
            // if($i > 0 and $has_online_approvals and $previous_annual_balance){

            // }

            $reports[$i] = $report;
            // if($days_old < 366 && $year->year == $resumption_date->year){
            //     break; //New staff dont have records beyoned 1 year, so 
            // }
        }
        // dd($reports);
        return $reports;
    }

    // public $has_online_approvals;
    // public $previous_annual_balance;
    // public $current_year_loop;

    public function getEntries($user, $year_start, $year_end, $leave_type){
        $lines = [];
        $last_years_deposits = 0;
        //get withdrawals
        $credit_lines = $this->getRecallCredit($lines, sizeof($lines), $user, $year_start, $year_end, $leave_type);
        $lines = $this->getWithdrawals($lines, 0, $user, $year_start, $year_end, $leave_type);
        $lines = $this->getDeposits($lines, sizeof($lines) ,$user, $year_start, $year_end, $leave_type);
        
        $this->critical_echo("<br/>================================== In Consolidation Block ==========================================");
        $this->critical_echo("<Br/>--- year_start == $year_start | year_end == $year_end | leave_type $leave_type->name");
        //sort        
        usort($lines, array($this,"date_sort"));

        // dd($lines);
        
        //2. Dump to screen
        
        $balance = 0;
        $deductExcess =false;
        $amountToDeduct = 0;
        $this->critical_echo("$this->page_name opening balance== $balance");
        $this->critical_echo("<br/>" . __LINE__ . $this->indent .  " deductExcess== " . var_export($deductExcess, true));
        $this->critical_echo("$this->page_name amountToDeduct== $amountToDeduct");
        $counter = 0;
        $prev_line=null;
        foreach($lines as $line){
            //echo("<br/>" . __LINE__ . $this->indent .  " processing row " . $counter);
            //echo("$this->page_name balance== $balance");
            //echo("<br/>" . __LINE__ . $this->indent .  " deductExcess== " . var_export($deductExcess, true));
            //echo("$this->page_name amountToDeduct== $amountToDeduct");

            if($line->type == "Deposit" && $leave_type->name =="Annual" and $line->leave_type->name == "Annual"){
                //echo("<br/>" . __LINE__ . $this->indent .  "Condition met line->type == Deposit and Leave type == Annual");
                //This block ensures that when depositing a new annual leave allocation on anniversary date, that a max of 10 days
                //is carried over from the previous balance
                if($line->amount > 15 && $balance > 10 && $user->wasFirstYear($line->date)){ 
                    //echo("<br/>" . __LINE__ . $this->indent .  "Condition met line->type == Deposit and line->amount == $line->amount Leave type == Annual");
                    
                    $deductExcess = true;
                    if($prev_line and $prev_line->leave_type and $prev_line->leave_type == "Annual"){
                        $amountToDeduct = $prev_line->balance;
                    }
                    
                    //echo("<br/>" . __LINE__ . $this->indent .  " deductExcess== " . var_export($deductExcess, true));
                    //echo("$this->page_name amountToDeduct== $amountToDeduct");

                }elseif($line->amount > 0 and $line->amount < 4 and $line->remarks != "Brought Forward"){
                    $line->remarks = "Monthly Annual leave increment for staff under 1";
                }
                if($line->remarks == "Annual" and ($line->amount == 15 or $line->amount == 11)){
                    $line->remarks = "New staff get 11 or 15 days after first 6 months";
                } 
                $this->last_years_total_deposit += $line->amount;
            }
            //track annual leave withdrawals by ladies
            if($line->type == "Withdrawal" and $leave_type->name == "Annual" and $user->gender == "Female"){
                $this->annual_to_be_deducted_from_maternity[] = array('year_start' => $year_start, 'year_end' => $year_end,'amount' => $line->amount);
            }
            //reduce their maternity by the annual leave taken
            if($line->type == "Deposit" and $leave_type->name == "Maternity" and $user->gender == "Female"){
                //echo ("<Br/> Checking for maternity to deduct. Size of deduction list: " . sizeof($this->annual_to_be_deducted_from_maternity)); 
                foreach($this->annual_to_be_deducted_from_maternity as $entry){
                    //echo('<Br/> deducting ' . $entry['amount']);
                    $line->amount += $entry['amount']; //withdrawals are -ve numbers that is why we are adding instead of deducting them
                    //echo('<br> amount after ' . $line->amount);
                }
                $line->remarks = $line->remarks . " allocation minus annual leave taken";
                $this->annual_to_be_deducted_from_maternity = []; //clear the list for next year
            }
            // if($line->type == "Refund"){
            //     $balance += $line->amount;
            // }

            //this line ensures that we populate the before
            $line->before = $balance;
            
            $balance += $line->amount;
            if($deductExcess){
                $balance -= $amountToDeduct;
                $balance += 10;
                $deductExcess=false; //to avoid repeated baseless deductions
                $line->remarks = "You clocked 1 year, so you get a new full annual leave deposit plus carry over to a max of 10 days";
            }
            $line->balance = $balance;
            $prev_line = clone $line;
            $counter++;
        }
        if($leave_type->name =="Annual"){
            //echo("<br/>" . __LINE__ . $this->indent .  " this->last_years_balance == ". $this->last_years_balance);
            $this->last_years_balance = $balance;
        }
        $this->critical_echo("$this->indent $this->page_name closing balance== $balance");
        $this->critical_echo($this->indent . $this->page_name . " deductExcess== " . var_export($deductExcess, true));
        $this->critical_echo("$this->indent $this->page_name amountToDeduct== $amountToDeduct");
        $this->critical_echo("$this->indent this->page_name this->last_years_balance== $this->last_years_balance");
        $this->critical_echo("<br/>================================== END OF Consolidation Block ==========================================");

        return $lines;
    }

    function date_sort($a, $b) {

        // return strtotime($a['date']) - strtotime($b['date']);
        // return strtotime($a->date) - strtotime($b->date);
        return $a->date > $b->date;
    }

    private function showMethodIntent($desc, $lines, $start_index, $user, $year_start, $year_end, $leave_type){
        // echo("<br/>=========================================" . $desc . "=========================================" . "<br/>" . __LINE__ . $this->indent . " user: " . $user->username . "<br/>" . __LINE__ . $this->indent." start_index: " . $start_index . "<br/>" . __LINE__ . $this->indent ." year_start: " . $year_start . "<br/>" . __LINE__ . $this->indent ." year_end: " . $year_end . "<br/>" . __LINE__ . $this->indent ." leave_type: " . $leave_type->name);
    }
    public function getWithdrawals($lines, $start_index, $user, $year_start, $year_end, $leave_type){
        $this->showMethodIntent("Getting withdrawals with the following params ", $lines, $start_index, $user, $year_start, $year_end, $leave_type);

        // $lines = [];
        //old - previously withdrawals in 2019 where applications approved in 2019
        // $withdrawals = LeaveApproval::where('applicant_username', $user->username)
        // ->where('created_at', '>=', $year_start)
        // ->where('created_at', '<=', $year_end)
        // ->where('leave_type', '=', $leave_type->name)
        // // ->where('leave_approvals.applicant_username', 'anna.coker')
        // ->get();

        //new - withdrawals in 2019 == 2019 applications that got approved, irrespective of when the approval happend
        $leave_request_ids_in_target_year = LeaveRequest::where('created_by', $user->username)
        ->where('created_at', '>=', $year_start)
        ->where('created_at', '<=', $year_end)
        ->where('leave_type', '=', $leave_type->name)
        ->get(['id']);
        
        $withdrawals = LeaveApproval::whereIn('leave_request_id', $leave_request_ids_in_target_year)->get();

        //echo("<br/>" . __LINE__ . $this->indent . "Found "  . sizeof($withdrawals) . " withdrawals");

        for ($i=$start_index; $i < sizeof($withdrawals); $i++) { 

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
                $line->date = $withdrawal->application()->start_date; //July 27 2019 - Because if approved this is the correct start date 
            }
            $line->application_id = $withdrawal->leave_request_id;
            $line->approval_id = $withdrawal->id;
            $lines[$i] = $line;
        }
        //echo("$this->page_name completed looping through and adding all withdrawals to the collection. Start index is now "  . sizeof($lines) . " withdrawals");

        return $lines;
    }

    //Aug 2019
    public function carryOver($lines, $start_index, $user, $year_start, $year_end, $leave_type){

    }

    public function getDeposits($lines, $start_index, $user, $year_start, $year_end, $leave_type){
        // $lines = [];
        $this->showMethodIntent("Getting Deposits with the following params", $lines, $start_index, $user, $year_start, $year_end, $leave_type);
        
        $start_date = clone $year_start; //Carbon::parse($user->resumption_date);

        //if you were employed after July 1, 2016
        // $cut_off_date = Carbon::create(2016, 06, 01)
        // if(Carbon::now()->diffInMonths($start_date) < 365){
        if(1==1){    
            //echo("<br/>User was employed less than a year ago ");
            //we will use your employment date to calculate Deposits to your leave
            $previous_value=0; //Holds the users previous leave balance so we can mimick incremental deposits
            //echo("<br/>" . __LINE__ . $this->indent .  " previous value == " . $previous_value);
            //to ensure we carry over previous total annual leave deposits for LAST YEAR bc of those under 1
            //---------------------------------------------------------
            // The block below ensures that the correct balance is carried over and displayed in the next years annual leave
            // It factors in how long the users has been here
            // Users under 1 carry over everything
            // Day after a user clocks 1, user receives brand new annual leave  deposit
            // After user clocks 1 user receives deposits on new years day
            //---------------------------------------------------------
            if($leave_type->name =="Annual"){
                //-------------------------------------------------------------------
                // March 23, 2018
                // This check ensures that users under 1 see their prorated balance
                // it also ensures that users older than 1 see their full jan 1 allocation 
                //-------------------------------------------------------------------
                $day_before = clone $start_date;
                // $day_before = $day_before->subDay();
                //echo("<br/>" . __LINE__ . $this->indent .  " day before == " . $day_before);
                if($user->wasFirstYear($day_before)){
                    //echo("<br/>" . __LINE__ . $this->indent .  " day before was in users first year");
                    $previous_value = $this->last_years_total_deposit;
                }
                //echo("<br/>" . __LINE__ . $this->indent .  " previous value is now == " . $previous_value);
                //end of block

                //Do carry over only for those who applied only and got approvals online or are in first year
                $has_online_approvals = LeaveApproval::where('applicant_username', '=',$user->username)
                ->whereYear('created_at', $start_date->year-1)
                ->where('leave_request_id', '>', 0)->count()>0;
                if(!$has_online_approvals){
                    $has_online_approvals = LeaveRequest::where('created_by', '=',$user->username)
                    ->where('supervisor_response', '=',true)
                    ->where('stand_in_response', '=',true)
                    ->whereYear('created_at', $start_date->year-1)
                    ->count()>0;
                }
                $online_approvals_year = $start_date->year-1;
                $this->critical_echo("<br/>" . __LINE__ . $this->indent .  " has online approvals in  $online_approvals_year ? ". var_export($has_online_approvals, true));
                //echo("$this->page_name Previous Value is : " . $previous_value);
                $this->critical_echo($this->page_name . __LINE__ . " Last Years Balance was: " . $this->last_years_balance);
                $has_online_approvals_or_is_first_year = false;
                if($user->wasFirstYear($day_before)){
                    $has_online_approvals_or_is_first_year = true;
                }
                if($has_online_approvals){
                    $has_online_approvals_or_is_first_year = true;
                }
                $this->critical_echo($this->page_name . __LINE__ . " has_online_approvals_or_is_first_year was: " . var_export($has_online_approvals_or_is_first_year, true));
                if($this->last_years_balance <> 0 and $has_online_approvals_or_is_first_year){ //Feb 18, 2019 while debugging bad balance i added then removed this or $user->wasFirstYear($day_before)){ //This will ensure that 
                    $this->critical_echo($this->page_name . __LINE__ . "condition met: last_years_balance <> 0 and has_online_approvals_or_is_first_year");
                    $line = new  LeaveTransaction();
                    $line->leave_type = $leave_type;
                    $line->date = clone $start_date;
                    //If a users balance was -ve that is user withdrew without having deposit
                    $line->type = $this->last_years_balance > 0 ? "Deposit" : "Withdrawal";
                    //echo("<BR/>Was First Year as @: " . $day_before->year . "/". $day_before->month . "/". $day_before->day . " ? ". var_export($user->wasFirstYear($day_before), true));
                    if($user->wasFirstYear($day_before)){ //($user->isFirstYear() carry over everything for under 1
                        $this->critical_echo($this->page_name . __LINE__ . " user was first year as at day_before " . $day_before);
                        //echo("$this->page_name this->last_years_balance == " . $this->last_years_balance);
                        $line->remarks = "Brought Forward";
                        $line->amount = $this->last_years_balance;
                    }else{
                        $this->critical_echo("<br/>" . __LINE__ . "$this->page_name  This user is no longer in his first year, so carrying over maximum of 10 days");
                        //if as at the $start_date point they had exceeded 1 year carry over just 10
                        if($user->wasFirstYear($start_date) == false){
                            $this->critical_echo("<br/>" . __LINE__ . "$this->page_name User was no longer in first year as at start_date" . $start_date);
                            //$start_date->diffInDays($user->carbonResumptionDate())> 366){
                            $line->remarks = "Brought Forward";
                            $line->amount = $this->last_years_balance > 10 ? 10 : $this->last_years_balance;
                        }else{
                            $line->remarks = "Brought Forward";
                            $line->amount = $this->last_years_balance;
                        }
                    }
                    $line->remarks = "Brought Forward";
                    $lines[$start_index] = $line;
                    $start_index+=1;
                }
            }
            //end of block

            

            $i=0;
            
            $force_end =false;

            // dd($start_date->diffInDays(Carbon::now()));
            // while($start_date <= Carbon::now()){ //dont calculate into the future, original loop for staff under 1
                //echo("<br/>Year end: " . $year_end->toDateString());
            while($start_date <= $year_end){
                //echo("<br/>" . __LINE__ . $this->indent .  " condition met? start_date: $start_date <= year_end $year_end");
                //Ensure that user gets deposit on anniversary date
                if($start_date->month === $user->carbonResumptionDate()->month && $start_date->day === $user->carbonResumptionDate()->day){
                    //echo "<br/>Ann Month: " . $start_date;
                    $days_to_add = $user->carbonResumptionDate()->day - $start_date->day;
                    //echo "<br/>Days to add: " . $days_to_add;
                    $start_date->addDays($days_to_add);
                    //echo "<br/>Ann date: " . $start_date;
                    
                }

                //lets check for the entitlement
                //echo("$this->page_name calling getDaysEntitledToAsOfXPointInTime(leave_type = $leave_type->name, start_date = $start_date)");
                $days = $this->getDaysEntitledToAsOfXPointInTime($leave_type, $user, $start_date);
                //echo("$this->page_name days entitled to == $days");
                //we only want to create a transaction if user was qualified for a deposit as at this point in time
                if($user->wasFirstYear($start_date)){
                    //echo("$this->page_name this block of code fires only if a user is in his first year ");
                    //echo("<br/>Was first year? ". $user->wasFirstYear($start_date) . " as at " . $start_date);
                    //echo("<br/>" . __LINE__ . $this->indent .  " about to evaluate whether days $days != previous_value $previous_value Line:" . __LINE__);
                    if($days != $previous_value){ //fEB 18, 2019 - Restore after you fix the bad balance bug
                        //echo("<br/>" . __LINE__ . $this->indent .  " there has been an increment, so I am making a deposit " . __LINE__);
                        //add to the transaction log
                        $line = new  LeaveTransaction();
                        $line->leave_type = $leave_type;
                        $line->date = clone $start_date;
                        $line->type = "Deposit";
                        $line->amount = $days - $previous_value;
                        $line->remarks = $leave_type->name;
                        $lines[$start_index] = $line;
                        //lets take note of the new value so that we can determine if theres been a change
                        $previous_value = $days;
                        //Deposit the counter
                        $start_index++;
                    }
                }
                //What this actually does is ensure that 30 days are added only
                //on your anniversay date 2nd year
                // elseif($user->wasSecondYear($start_date)){
                //     //echo("<Br/>In else block: " . $start_date);
                //     // if(sizeof($lines) <= $start_index){
                //     //     //echo "<br/>Balance B4 Anniversary deposit: " . $lines[$start_index-1]->balance;
                //     // }
                //     //echo "<br/> Balance: " . $lines[$start_index-1]->balance;
                //     //This is supposed to fix a bug wherein just over 1 guys dont see their brand new 30 day deposit
                //     //just because $days==$previous_value
                //     //This is an old staff give him the max
                //     $line = new  LeaveTransaction();
                //     $line->date = clone $start_date;
                //     $line->type = "Deposit";
                //     $line->amount = $this->getDaysEntitledToAsOfXPointInTime($leave_type, $user, $start_date);
                //     $line->remarks = $leave_type->name;// . "In 2nd Year block";
                //     $lines[$start_index] = $line;
                //     $start_index++;
                //     //this loop is needed only for annual leave,
                //     if($leave_type->name=="Annual"){
                //         $force_end=true;
                //     }
                // }
                // //To ensure that if you reach your third calendar year,
                // //You just get your one time deposit of 30 days
                // elseif($user->onThirdCalendarYear($start_date)){
                //     //This is an old staff give him the max
                //     $line = new  LeaveTransaction();
                //     $line->date = clone $start_date;
                //     $line->type = "Deposit";
                //     $line->amount = $this->getDaysEntitledToAsOfXPointInTime($leave_type, $user, $start_date);
                //     $line->remarks = $leave_type->name;// . "In 3rd Year Block";
                //     $lines[$start_index] = $line;
                //     $start_index++;
                //     //this loop is needed only for annual leave,
                //     if($leave_type->name=="Annual"){
                //         $force_end=true;
                //     }
                // }
                else{
                    //echo("$this->page_name -----------------------------> this block of code runs only for old staff " . __LINE__);
                    //This is an old staff give him the max
                    $line = new  LeaveTransaction();
                    $line->leave_type = $leave_type;
                    $line->date = clone $start_date;
                    $line->type = "Deposit";
                    $line->amount = $this->getDaysEntitledToAsOfXPointInTime($leave_type, $user, $start_date);
                    $line->remarks = $leave_type->name;// . "In 3rd Year Block";
                    $lines[$start_index] = $line;
                    $start_index++;
                    //this loop is needed only for annual leave,
                    if($leave_type->name=="Annual"){
                        $force_end=true;
                    }
                }
            
                //this loop is needed only for annual leave,
                if($leave_type->name!="Annual"){
                    $force_end=true;
                }
                
                //end the loop if this is the anniversary date of current year 
                //if user is in second year
                if($user->wasSecondYear($start_date) && $leave_type->name=="Annual"){
                    $force_end = true;
                }

                //days are added to new employees on a monthly basis, 
                //so lets add a month before the next Deposit check
                $start_date = $start_date->addMonth();

                //This block ensures that any Deposits between the monthly resumption anniversary day say 3rd and the current day
                //are factored in



                //This block ensures that we do not process into the future
                if($start_date > Carbon::now()){
                    $start_date = Carbon::now();
                    $force_end = true; //when this variable is set, the while block must end, given the date adustment  
                }

                if($force_end){
                    break; //without this line the block below would make this loop infinite
                }                
                
                $i++;
            }

        }
        //else{
            // //This is an old staff give him the max
            // $line = new  LeaveTransaction();
            // $line->date = clone $start_date;
            // $line->type = "Deposit";
            // $line->amount = $this->getDaysEntitledToAsOfXPointInTime($leave_type, $user, $start_date);
            // $line->remarks = $leave_type->name;
            // $lines[$start_index] = $line;
        // }
        // dd($start_date);
        //echo("<br/>" . __LINE__ . $this->indent . "======================================END GetDeposits =========================================");
        return $lines;
    }

    public function createTransaction($date, $type, $amount, $remarks){

    }
   
    /*
     Returns the number of years the user has been with AEDC, counting from 2017 when the new leave system was introduced 
    */
    public function getYears($user){
        $years = [];
        
        $startYear = $user->carbonResumptionDate()->year < 2017 ? 2017 : $user->carbonResumptionDate()->year;
        $maxYear = Carbon::now()->year;
        // dd($years);
        $difference = $maxYear - $startYear;

        //Marcch 26, 2019
        //This logic stops the system from calculating beyond the calender year 
        //if the user is still within his first year
        if($user->isFirstYear() and $user->carbonResumptionDate()->year > 2016)
        {
            $start_date = $user->carbonResumptionDate();
            $end_date = Carbon::create($user->carbonResumptionDate()->year+1, $user->carbonResumptionDate()->month, $user->carbonResumptionDate()->day-1);
            $years[0] = [$start_date, $end_date];
            return $years; 
        }
        if($user->carbonResumptionDate()->year > 2016){
            $prev_start_date = null;
            $prev_end_date = null;
            for ($i=0; $i <= $difference; $i++) { 
                //First year -> prorating year
                if($i === 0){
                    $start_date = $user->carbonResumptionDate();
                    $end_date = Carbon::create($user->carbonResumptionDate()->year+1, $user->carbonResumptionDate()->month, $user->carbonResumptionDate()->day-1);
                }
                //Second year -> 
                if($i === 1){
                    $start_date = Carbon::create($prev_end_date->year, $prev_end_date->month, $prev_end_date->day + 1);
                    $end_date = Carbon::create($start_date->year, 12, 31);
                }
                //Third year & after
                if($i > 1){
                    $start_date = Carbon::create($end_date->year + 1, 1, 1);
                    $end_date = Carbon::create($end_date->year + 1, 12, 31);
                }
                $prev_start_date = $start_date;
                $prev_end_date = $end_date;
                $years[$i] = [$start_date, $end_date];
                $startYear++;
            }
            return $years;
        }
        for ($i=0; $i <= $difference; $i++) { 
            $start_date = Carbon::create($startYear, 1, 1);
            $end_date = Carbon::create($startYear, 12, 31);
            $years[$i] = [$start_date, $end_date]; 
            $startYear++;           
        }
        // dd($years);
        return $years;
    }

    public function getYearsBackup($user){
        $years = [];
        
        $startYear = $user->carbonResumptionDate()->year < 2017 ? 2017 : $user->carbonResumptionDate()->year;
        $maxYear = Carbon::now()->year;
        // dd($years);

        //Marcch 26, 2019
        //This logic stops the system from calculating beyond the calender year 
        if($user->isFirstYear() and $user->carbonResumptionDate()->year > 2016)
        {
            $startYear = $user->carbonResumptionDate()->year;
            $years[0] = Carbon::create($startYear, 1, 1);
        }
        else{        
            $difference = $maxYear - $startYear;
            for ($i=sizeof($years); $i <= $difference; $i++) { 
                $years[$i] = Carbon::create($startYear, 1, 1);
                $startYear++;
            }
        }
        //echo(sizeof($years));
        return $years;
    }

     //-------------------------------------------------------------------------------------------------//
    //
    // This function returns the number of leave days a user is entitled
    // ---->It does not deduct the days a user has taken<------
    // Parameter 1 : LeaveType e.g. Annual 
    // Parameter 2 : The User
    // Parameter 3 : The Point in time
    //-------------------------------------------------------------------------------------------------//
    public function getDaysEntitledToAsOfXPointInTime(LeaveType $leave_type, \App\User $user, Carbon $pointInTime = null){
        //echo("<br/>================================START getDaysEntitledToAsOfXPointInTime=================================");
        //echo("<br/>" . __LINE__ . $this->indent .  "pointInTime: $pointInTime");
       
        $days_since_resumption = $this->calculateDaysSinceResumption($user, $pointInTime);
        
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

    
    public function calculateDaysSinceResumption(\App\User $user, Carbon $pointInTime=null){
  
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

    
    // public function getRecallCredit($user,  $leave_type, $year=null, $pointInTime=null){
    public function getRecallCredit($lines, $start_index, $user, $year_start, $year_end, $leave_type){

        $credit = null;

        if(!isset($user)){
            return null;        
        }
         
        // $contract_start_date = Carbon::parse($user->resumption_date);
        // if( $user->is_contract_staff or $contract_start_date->diffInDays(Carbon::now())<365){
        if(true==false){
            if(!isset($year)){
                $year = Carbon::now();
            }
            
            if(!isset($pointInTime)){
                $pointInTime = Carbon::now();
            }
            
                
            if(!isset($contract_start_date)){
                      
                $contract_start_date = $year;
                  
            }
            $current_year = Carbon::now();
            $current_contract_start_date = Carbon::create($contract_start_date->year, $contract_start_date->month, $contract_start_date->day);
            $current_contract_end_date = clone $current_contract_start_date; //Carbon::create($contract_start_date->year+1, $contract_start_date->month, $contract_start_date->day);
            $current_contract_end_date = $current_contract_end_date->addYear();
        
            //if a contracct staff began his contract years ago, then in the absence of an accurate contrac date in the last 1 year, guess it
            if($contract_start_date->diffInDays($current_year)>365){
                $current_contract_start_date = Carbon::create($current_year->year, $contract_start_date->month, $contract_start_date->day);
                $current_contract_end_date = clone $current_contract_start_date; //Carbon::create($current_year->year+1, $contract_start_date->month, $contract_start_date->day);
                $current_contract_end_date = $current_contract_end_date->addYear();
            }
                  
            if($pointInTime){
                //if this query wants record for a particular point in time, then use the contracct dates in that time range
                $current_contract_start_date = Carbon::create($pointInTime->year, $contract_start_date->month, $contract_start_date->day);
                // $current_contract_end_date =Carbon::create($pointInTime->year+1, $contract_start_date->month, $contract_start_date->day);
                $current_contract_end_date = clone $current_contract_start_date; //Carbon::create($current_year->year+1, $contract_start_date->month, $contract_start_date->day);
                $current_contract_end_date = $current_contract_end_date->addYear();
            }
            //just for a change means nothing
            $start = $current_contract_start_date->format('Y-m-d') . " 00:00:00";
            $end = $current_contract_end_date->format('Y-m-d') . " 23:59:59" ;
                  
            $credit = Recall::where('applicant_username', $user->username)
                ->where('supervisor_response', '=', true)
                ->whereBetween('leave_approval_date', [$start, $end])
                // ->where('leave_approval_date', '<=', $end)                
                ->where('leave_type',$leave_type->name)
                ->get();
        }
        
        $start = $year_start->format('Y-m-d') . " 00:00:00";
        $end = $year_end->format('Y-m-d') . " 23:59:59" ;
        $credit = Recall::where('applicant_username', $user->username)
            ->where('supervisor_response', '=', true)
            // ->whereYear('leave_approval_date', $year->toDateString())
            ->whereBetween('leave_approval_date', [$start, $end])
            ->where('leave_type',$leave_type->name)
            ->get();
        // ////echo $start . '<br/>';
        // ////echo $end . '<br/>';
        // ////echo $leave_type->name . '<br/>';
        // $credit = Recall::all();
        // dd($credit);
        $max = sizeof($lines) + sizeof($credit);
        for ($i=$start_index; $i < $max; $i++) { 
            //get this transaction
            $item = $credit[$i-sizeof($lines)];

            //add to the transaction log
            $line = new  LeaveTransaction();
            $line->date = $item->created_at;
            $line->type = "Refund";
            $line->amount = $item->days;
            $line->remarks = $leave_type->name;
            $line->application_id = $item->leave_request_id;
            $line->approval_id = $item->id;
            $lines[$i] = $line;
        }
        // dd($lines);
        return $lines;
    }

    public function critical_echo($input){
        //echo($input);
    }
}
