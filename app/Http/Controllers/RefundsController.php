<?php

namespace App\Http\Controllers;

use App\Recall;
use App\LeaveRequest;
use App\LeaveApproval;
use Session;
use Auth;
use Illuminate\Http\Request;
use App\User;
use App\LeaveType;
use Carbon\Carbon;
use App;
use App\Holiday;

class RefundsController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

  
      public function create(Request $request, $id=null){
          if(!isset($id)){
            if($request->has('id')){
              $id = $request->input('id');
            }
            else{
              return redirect()->back()->with('flash_message', 'Leave Request not found');
            }
          }
          $application = LeaveRequest::where('id', '=', $id)->where('created_by', '=', Auth::user()->username)->get();
          if(!isset($application)){
              return redirect()->back()->with('flash_message', 'Leave Request not found');
          }
          // var_dump()
        return view('refunds.create', ['application_id'=>$id, 'title' => 'New Leave Refund Application']);
    }

    //September 26, 2017 - If you are recalled from leave, this function ensures that you get credit for lost days
  public function store(Request $request, $id){
      //1. locate the application
      // dd($id);
      $application = LeaveRequest::find($id);
      // dd($application);
      if($application == null){
        return redirect()->back()->withInput()->with('flash_message', 'Request not found, truncate failed!');
      }

      $this->validate($request, array(
              'reason' => 'bail|required',
              'date_recalled' => 'required',
              )); 
      //2. locate the approval
      $approval = LeaveApproval::where('leave_request_id', $application->id)->first();
      // dd($approval);
      if(!isset($approval)){
        return redirect()->back()->withInput()->with('flash_message', 'Approval not found, truncate failed!');;
      }


      require_once('datefunctions.php');
      $recall_date = setDate($request['date_recalled']);
      $application->start_date = parseDate($application->start_date);
      // dd($application->start_date);
      //prevent a recall date earlier than approved start date
      $approved_start_date = Carbon::create($application->start_date->year, $application->start_date->month, $application->start_date->day);
      if($approved_start_date->gt($recall_date)){
        return redirect()->back()->withInput()->with('flash_message', 'Your recall date cannot be earlier than your approved start date!');;
      }
      
      //prevent duplicate recall requests for an already approved refund requests 
      $recall =  Recall::where('leave_approval_id', $approval->id)->where('supervisor_response', '=', true)->first();

      if(isset($recall)){
        return redirect()->back()->with('flash_message', 'Your leave refund request was already approved!');
      }

      $recall =  Recall::where('leave_approval_id', $approval->id)->where('supervisor_response', '=', null)->first();

      if(isset($recall)){
        return redirect()->back()->with('flash_message', 'Your previously submitted leave refund request is pending confirmation!');
      }
      //3. document the recall
      $recall = new Recall();
      $recall->applicant_username = $approval->applicant_username;
      require_once('datefunctions.php');
      $recall->supervisor_username = $application->supervisor_username;
      $recall->date = setDate($request['date_recalled']);
      $workingDaysLeft = $this->getWorkingDaysBetweenTwoDates($recall->date, Carbon::parse($application->end_date));
      if($recall->date->isToday()){
        $workingDaysLeft--;
      }
      $recall->days_credited = $workingDaysLeft;      
      $recall->reason = $request['reason'];
      $recall->leave_approval_id = $approval->id;
      $recall->leave_approval_date = $application->supervisor_response_date;
      $recall->leave_type = $approval->leave_type;

      try{
            $recall->save();
            return redirect()->back()->with('flash_message', 'Request submitted!');
      }
      catch(\Exception $ex){
            return redirect()->back()->with('flash_message', 'An error occured: ' . $ex->getMessage());
      }
  }


  public function view(Request $request, $id=null){
      if(!isset($id)){
         if($request->has('id')){
            $id = $request->input('id');
         }
         else{
            return redirect()->back()->with('flash_message', 'Leave Request not found');
         }
      }
      if(!is_numeric($id)){
        return redirect()->back()->with('flash_message', 'Leave refund request not found');
      }

      $recall = Recall::find($id);
      if(!isset($recall)){
        return redirect()->back()->with('flash_message', 'Leave refund request not found');
      }
      
      $approval = LeaveApproval::find($recall->leave_approval_id);
      
      if(!isset($approval)){
        return redirect()->back()->with('flash_message', 'Approval not found');
      }

      $application = LeaveRequest::find($approval->leave_request_id);
      
      if(!isset($application)){
        return redirect()->back()->with('flash_message', 'Leave request not found');
      }

      $recall->approval = $approval;
      $recall->application=$application;

      return view('refunds.process', ['recall' => $recall, 'title' => 'Leave refund application #' . $recall->id . " - " . $application->name]);
  }
  
  
   //September 26, 2017 - Your supervisor uses this function to ensures that you get credit for lost days
  public function confirm(Request $request, $id){
     
      //1. locate the recall
      $recall = Recall::find($id);

      if($recall == null){
        Session::flash('flash_message', 'Request not found, Leave refund confirmation failed!');
        return redirect()->back();
      }

      if($recall->supervisor_response){
        return redirect()->back()->with('flash_message', 'Your already approved this Leave refund request!');
      }
      
      //get the approval
      $approval = LeaveApproval::find($recall->leave_approval_id);
      
      if(!isset($approval)){
          return redirect()->back()->withInput()->with('flash_message', 'Approval not found');
      }

      //get the leave application
      $application = LeaveRequest::find($approval->leave_request_id);//->first();

      if(!isset($application)){
          return redirect()->back()->withInput()->with('flash_message', 'Application not found');
      }
      
      //4. calculate credit, commented out because it has already been done
      // $workingDaysLeft = $this->getWorkingDaysBetweenTwoDates($recall->date_recalled, $application->end_date);
      // dd('working days left: '. $workingDaysLeft);
      // $recall->days_credited = $workingDaysLeft;
      $recall->supervisor_response = true;
      $recall->supervisor_response_date = Carbon::now();

      //update the applicaiton
      $application->recalled = true;
      // $application->date_recalled = $recall->date; //This column doesnt exist
      try{
            $recall->save();
            $application->save();
            return redirect()->back()->with('flash_message', 'Leave refund request confirmed!');
      }
      catch(\Exception $ex){
            return redirect()->back()->with('flash_message', 'An error occured: ' . $ex->getMessage());
      }
  }

  public function dismiss(Request $request, $id){
    
      //1. locate the recall
      $recall = Recall::find($id);
      if($recall == null){
        Session::flash('flash_message', 'Request not found, Leave refund confirmation failed!');
        return redirect()->back();
      }

      $this->validate($request, array(
              'reason' => 'bail|required'
              )); 
      
      $recall->supervisor_response = false;
      $recall->supervisor_response_reason = $request['reason'];
      $recall->supervisor_response_date = Carbon::now();
      // dd($recall);
      try{
            $recall->save();
            return redirect()->back()->with('flash_message', 'Leave refund request dismissed!');
      }
      catch(\Exception $ex){
        // dd($ex);
            return redirect()->back()->with('flash_message', 'An error occured: ' . $ex->getMessage());
      }
  }

  public function getRecallCredit($user, App\LeaveType $leave_type, $year=null){

      if(!isset($user)){
          return 0;        
      }

      if(!isset($year)){
        $year = Carbon::now();
      }
      $credit =  0;//Recall::where('applicant_username', $user->username)->where('supervisor_response', '=', true)->whereYear('')
      if($user->is_contract_staff){
          $contract_start_date = Carbon::parse($user->resumption_date);
        //   //////dd($contract_start_date);
          if(!isset($contract_start_date)){
              $contract_start_date = $year;
          }
          $current_year = Carbon::now();
          $current_contract_start_date = Carbon::create($contract_start_date->year, $contract_start_date->month, $contract_start_date->day);
          $current_contract_end_date =Carbon::create($contract_start_date->year+1, $contract_start_date->month, $contract_start_date->day);
          //if a contracct staff began his contract years ago, then in the absence of an accurate contrac date in the last 1 year, guess it
          if($contract_start_date->diffInDays($current_year)>365){
            $current_contract_start_date = Carbon::create($current_year->year, $contract_start_date->month, $contract_start_date->day);
            $current_contract_end_date =Carbon::create($current_year->year+1, $contract_start_date->month, $contract_start_date->day);
          }
          // $d = $contract_start_date->diffInDays($current_year)>365;
          // ////dd($d);
          //just for a change means nothing
          $start = $current_contract_start_date->format('Y-m-d') . " 00:00:00";
          $end = $current_contract_end_date->format('Y-m-d') . " 23:59:59" ;
          
          $credit = Recall::where('applicant_username', $user->username)
          ->where('supervisor_response', '=', true)
          ->whereBetween('leave_approval_date', [$start, $end])
          ->where('leave_type',$leave_type->name)
          ->sum('days_credited');
      }
      else{
        $credit = Recall::where('applicant_username', $user->username)
          ->where('supervisor_response', '=', true)
          ->whereYear('leave_approval_date', $year->toDateString())
          ->where('leave_type',$leave_type->name)
          ->sum('days_credited');
      }
      return $credit;
  }

   public function getWorkingDaysBetweenTwoDates($start, $end){
      $workdays = 0;
      $hols=0;
      $starting = Carbon::create($start->year, $start->month, $start->day);
      $ending = Carbon::create($end->year, $end->month, $end->day);
      $counter=0;
      $maxDays = $ending->diffInDays($starting);
      
      // var_dump('<br/>Starting: '. $starting);
      // dd('Ending: ' . $ending);
      while ($counter<=$maxDays) {

        //echo("Counter = ");echo($counter);
        $isHoliday = Holiday::whereDay('date', $starting->day)
            ->whereMonth('date', $starting->month)
            ->whereYear('date', $starting->year)
            ->count()>0;

        // if($starting->isWeekend()===true || $isHoliday===true){
        //     $hols+=1;
        // }
        if($starting->isWeekend()===false && $isHoliday===false){
              $workdays+=1;
        }
        // echo("<br/>Date: ");echo($starting->toDayDateTimeString());
        // echo("<br/>Is Holiday: ");////var_dump($isHoliday);
        // echo("<br/>Is Weekend: ");////var_dump($starting->isWeekend());
        // echo("<br/>Work Days: ");echo($workdays);
        // echo("<br/>Holidays: ");echo($hols);
        // echo("<br/>Counter: ");echo($counter);
        // echo("<br/>--------------------------------<br/>");
        $starting = $starting->addDays(1);
        $counter+=1;
      }
      echo("<br/>Counter Ended: ");echo($counter);
      var_dump('<br/>Max days: '.$maxDays);
      var_dump('<br/>Working days: '.$workdays);
      return $workdays;
  }

   public function destroy(Request $request, $id)
  {
        //$this->authorize('destroy', $leaveRequest);
        if($id == null){
          return;
        }
        $refundRequest = Recall::find($id);
        // dd($refundRequest);
        if($refundRequest!=null){

          if($refundRequest->created_by!=Auth::user()->username){
              Session::flash("flash_message","You cannot delete another's application");
          }
          if($refundRequest->supervisor_response==null){
              $refundRequest->delete();
            }
            else{
              Session::flash("flash_message","You can only delete unprocessed applications");
            }
        }
        return redirect('/home');
  }
}
