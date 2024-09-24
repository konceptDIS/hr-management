<?php

namespace App\Http\Controllers;

use App\Document;
use App\Recall;
use Illuminate\Http\Request;
use App\Http\Requests;
use Session;
use App;
use App\LeaveRequest;
use App\LeaveRequestStatus;
use App\LeaveApproval;
use App\LeaveEntitlement;
use App\LeaveType;
use App\Repositories\LeaveRequestsRepository;
use App\Repositories\OfficesRepository;
use Auth;
use Carbon\Carbon;
use App\Role;
use App\Holiday;
use App\Mail\HRDenied;
use App\Mail\HRApproved;
use App\Mail\StandInDeclined;
use App\Mail\StandInApproved;
use App\Mail\SupervisorDenied;
use App\Mail\SupervisorApproved;
use App\Mail\LeaveApprovedNotification;
use App\Mail\StandInDeclinedNotification;
use App\Mail\SupervisorDeclinedNotification;
use App\Mail\SupervisorYouJustApproved;
use App\Mail\StandInYouJustApproved;
use App\Mail\SupervisorYouJustDeclined;
use App\Mail\StandInYouJustDeclined;
use App\User;
use App\Mail\NewHRPleaseApproveRequest;
use App\Mail\NewStandInPleaseApproveRequest;
use App\Mail\NewSupervisorPleaseApproveRequest;
use App\Stat;
use App\Mail\PlainTextTest;
//use Illuminate\Mail\Mailer;
use Mail;
use App\Permission;
use Excel;

class LeaveRequestsController extends Controller
{

  protected $leaveRequestsRepository;
  protected $officesRepository;
  protected $redirectTo = "/home";
  protected $ROWS_PER_TABLE = 50;

  public function __construct()
  {
    $this->middleware('auth');
    $this->middleware('session-timeout');
  }

  /**
  * Show the form for creating a leave rquest resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function newRequest(Request $request)
  {
    // if($request->user()->section == "Default"){
    //   return redirect("/complete-your-profile");
    // }
    $leave_type_name=null;
    $leave_type = null;
    $max=null;
    $leaveTypes = $this->getLeaveBalanceForUser($request->user()); //LeaveType::all();
    $leaveTypes = $this->filterOutOtherLeaveTypesIfAnnualLeaveAvailable($leaveTypes, $request->user());
    // ////////////dd($leaveTypes);
    $url = $request->fullUrl();
    if($request->has('name')){
      $leave_type_name = $request->input('name');
    }
    if($leave_type_name==null){
        $leave_type_name="Annual";
    }
    if($leave_type_name!=null){
      $leave_type = LeaveType::where('name',$leave_type_name)->first();
      if($leave_type==null){
        Session::flash("flash_message", "That leave type does not exist");
        return redirect()->back();
      }
      $max = $this->getDaysLeftByLeaveType($leave_type);
    }
    if($max==null){
        $max=30;
    }
    return view('leaverequests.create', array(
      'title' => 'New Leave Request',
      'leavetypes' => $leaveTypes,
      'selected_leave_type'=> $leave_type,
      'max' => $max
    ));
  }

  ///not in user
  public function newRequestPost(Request $request)
  {
    $leave_type_name = $request->$name;
    $leave_type = LeaveType::where('name',$leave_type_name)->first();
    if($leave_type==null){
      Session::flash("flash_message", "That leave type does not exist");
      return redirect()->back();
    }

    // if($request->user()->section == "Default"){
    //   return redirect("/complete-your-profile");
    // }
    $leaveTypes = LeaveType::where('name', '<>', 'Casual')->get();
    
    $leaveTypes = $this->filterOutOtherLeaveTypesIfAnnualLeaveAvailable($leaveTypes, $request->user());
    return view('leaverequests.create', array(
      'title' => 'New Leave Request',
      'leavetypes' => $leaveTypes,
      'selected_leave_type'=> $leave_type,
      'max' => $this->getDaysLeftByLeaveType($leave_type)
    ));
  }

  public function setDate($input){

      $parts = explode('/', $input);

      if($parts!=null && sizeof($parts)==3){
        return Carbon::create($parts[2],$parts[1],$parts[0]);
      }
      return null;
  }


  public function removeSpaceAndConcatenateUsername($input){
    if(strpos($input, " ")>0){
      $parts = explode(" ",$input);
      if(sizeOf($parts)==2){
        $input= $parts[0] . "." .$parts[1];
      }
    }
    return $input;
  }

  public function convertEmailToUsername($username){
      if(!isset($username)){
          return $username;
      }
      if(strpos($username, "abujaelectricity") !== false){
          try{
            $parts = explode("@",$username);
            if(sizeOf($parts)>1){
                  $username = $parts[0];
            }
          }catch(\Exception $ex){

          }
      }
      return $username;
  }

  /**
  * Store a newly created leave request
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request)
  {
      if(\Auth::check()==false){
        Session::flash('flash_message', 'Please login first!');
        return redirect()->back();
      }

      $this->validate($request, array(
              'start_date' => 'bail|required',
              'days_requested' => 'bail|required',
              'leave_type' => 'bail|required|min:4',
              'supervisor_username' => 'required|max:50',
              'stand_in_username' => 'max:50',
            )
      );

      $current = Carbon::now()->toDateTimeString();
      $input = new App\LeaveRequest;
      try{
        if($request->has('id')){
          $id = $request->input('id');
          $input = LeaveRequest::find($id);
        }
      }catch(\Exception $ex){

      }
      
      $input -> start_date = $this->setDate($request->start_date);
      ////var_dump('Start date: ' . $input -> start_date);
      $input -> days_requested = $request->days_requested;

      ////var_dump('days requested: ' . $input -> days_requested);
      $input -> end_date = Carbon::parse($this->calculateLeaveEndDate($input));

      ////var_dump('end_date : ' . $input -> end_date);
      // //dd($input);
      //$input -> end_date = $this->setDate($request->end_date);
      $input -> leave_type = $request->leave_type;
      $input -> supervisor_username = $this->convertEmailToUsername($this->removeSpaceAndConcatenateUsername($request->supervisor_username));
      $input -> stand_in_username = $this->convertEmailToUsername($this->removeSpaceAndConcatenateUsername($request->stand_in_username));
      $input -> created_by = Auth::user()->username;
      $input -> date_submitted = $current;//->toDateTimeString();
      $input -> date_created = $current;//->toDateTimeString();
      $input -> created_at = $current;
      $input -> updated_at = $current;
      $input -> name = Auth::user()->getName();
      $input -> md_approval_required = Auth::user()->is_contract_staff;

      $leave_type = LeaveType::where('name', $input->leave_type)->first();

      if($leave_type->requireDocument()){//$input->leave_type == "Sick" or $input->leave_type == "Paternity" or $input->leave_type == "Maternity" or $input->leave_type == "Examination"){
        if(!$request->file('documents')){
          Session::flash("flash_message", "Please attach a document or picture to support your application");
          return redirect()->back()->withInput();
        }
      }
              
      // $input-> days_left = $this->getDaysLeftByLeaveType($leave_type, Auth::user(), Carbon::now());
      $input-> days_left = $this->getLeaveBalanceLeftByLeaveType($leave_type, Auth::user(), Carbon::now());
      $input -> section = Auth::user()->section;
      $input-> reason = $request->reason;
      $input->supervisor_response=null;

      $validate = true;

      if($request->has('dont_validate_usernames')){
        $validate =false;
      }
      if($validate){
        //verify that stand in is correct
        try{
            $usersADAccount = $this->searchForUserInAD($this->getADConnection(), $input ->stand_in_username, null);
            if(!$usersADAccount){
              Session::flash("flash_message", "Stand in username is incorrect, please fix");
              Session::flash('show_bypass_validation_option', true);
              return redirect()->back()->withInput();
            }
        }catch(\Exception $e){

        }

        //verify that supervisor is correct
        try{
          $usersADAccount = $this->searchForUserInAD($this->getADConnection(), $input ->supervisor_username, null);
          if(!$usersADAccount){
            Session::flash("flash_message", "Supervisor username is incorrect, please fix");
            Session::flash('show_bypass_validation_option', true);
            return redirect()->back()->withInput();
          }
        }catch(\Exception $e){
          
        }
      }
      if($leave_type->requireBalance() && $input->days_left < $input->days_requested){
          Session::flash('flash_message', 'You cannot take more than ' . $input->days_left . ' day(s) ' . $input->leave_type . ' leave!');
          return redirect()->back()->withInput();
      }
      try {
          // dd($input);
        $input->save();
      // dd($input->days_left);
        
        Session::flash('flash_message', 'Request submitted successfully!');
        $this->upload($request, $input);
      } catch (\Exception $e) {
        //   ////////////dd($e);
        Session::flash('flash_message', "An error occured: " . $e->getMessage());
      }

      $applicant = App\User::where('username', '=', $input->created_by)->first();
      $approver = $this->getUser($input->stand_in_username);
      $supervisor = $this->getUser($input->supervisor_username);
      // ////////////dd($supervisor);
      try{
          Mail::setSwiftMailer($this->getSwiftMailer());
          // $cc = $this->getHREmailAddresses($applicant);
          $cc = [];
          array_push($cc, $request->user()->email());
          array_push($cc, $supervisor->email());
        	Mail::to($approver->email(), $approver->name)->cc($cc)//to stand in, copy applicant, copy boss
            ->send(new NewStandInPleaseApproveRequest($input, $applicant, $approver));
      }catch(\Exception $ex){
	         Session::flash("flash_message","Your request was processed successfully, but the email notifications failed");
        // //////////////dd($ex);
      }
      return redirect()->action('LeaveRequestsController@home')->withInput();
  }

  public function findUserByStaffID($staff_number){

  }
  public function findUserByStaffIDAPI($staff_number, $url = "https://pics.abujaelectricity.com/ID/Get"){
        
    $fields = array(
        'id' => urlencode($staff_number),
    );

    $fields_string = null;
    //url-ify the data for the POST
    foreach($fields as $key=>$value) 
    { 
        $fields_string .= $key.'='.$value.'&'; 
    }
    rtrim($fields_string, '&');

    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, count($fields));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //execute post
    $result = curl_exec($ch);

    $result = json_decode($result, true);

    //close connection
    curl_close($ch);

    return $this->objectify($result);
  }

  //Objective API Call result
  protected function objectify($apiCallResult){
    $obj = new \App\IDCard();
    $obj->msg = $apiCallResult['msg'];
    $obj->status_code = $apiCallResult['status_code'];
    if(array_key_exists('data', $apiCallResult)){
        $obj->data = new \App\ApiUserData();
        $obj->data->name = $apiCallResult['data']['name'];
        $obj->data->firstname = $apiCallResult['data']['firstname'];
        $obj->data->surname = $apiCallResult['data']['surname'];
        $obj->data->mobile_phone = $apiCallResult['data']['mobile_phone'];
        $obj->data->job_title = $apiCallResult['data']['job_title'];
        $obj->data->department = $apiCallResult['data']['department'];
        $obj->data->region = $apiCallResult['data']['region'];
        try{
            $obj->data->yearResumed = $apiCallResult['data']['yearResumed'];
            $obj->data->monthResumed = $apiCallResult['data']['monthResumed'];
            $obj->data->dayResumed = $apiCallResult['data']['dayResumed'];
        }catch(\Exception $e){

        }
    }
    return $obj;
  }

   /**
  * Show the form for creating a leave rquest resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function applyForGet(Request $request)
  {
    if(!$request->user()->hasRole('HR')){
      return back()->withInput()->with('flash_message',"Only HR can apply for"); 
      // return abort(403, "Only HR can apply for"); 
    }
    if(!$request->has('username')){
      return back()->withInput()->with('flash_message',"Please specify username or staff number"); 
    }
    $username = $request->input('username');

    $user = null;

    if(is_numeric($username)){
      //case of staff number, get username
      $user = User::where('staff_number', $username)->first();
    }else{
      $user = User::where('username', $request->input('username'))->first();
    }
    if($user==null){
      // dd("User null");
      return back()->withInput()->with('flash_message',"User not found"); 
    }
    $leave_type_name=null;
    $leave_type = null;
    $max=null;
    $leaveTypes = $this->getLeaveBalanceForUser($user); //LeaveType::all();
    $leaveTypes = $this->filterOutOtherLeaveTypesIfAnnualLeaveAvailable($leaveTypes, $user);
    // ////////////dd($leaveTypes);
    $url = $request->fullUrl();
    if($request->has('name')){
      $leave_type_name = $request->input('name');
    }
    if($leave_type_name==null){
        $leave_type_name="Annual";
    }
    if($leave_type_name!=null){
      $leave_type = LeaveType::where('name',$leave_type_name)->first();
      if($leave_type==null){
        Session::flash("flash_message", "That leave type does not exist");
        // dd("Leave Type not found");
        return redirect()->back();
      }
      $max = 30;
      // $this->getDaysLeftByLeaveType($leave_type, $user);
    }
    if($max==null){
        $max=30;
    }
    // Session::flash('applying_for', $user->username);
    session(['applying_for' => $user->username]);
    // dd($user->username);
    return view('leaverequests.applyFor', array(
      'title' => 'New Leave Request for ' . $user->getName(),
      'leavetypes' => $leaveTypes,
      'selected_leave_type'=> $leave_type,
      'max' => $max,
      'created_by' => $user->username
    ));
  }

  //POST :: Save a leave request created for another
  public function applyFor(Request $request)
  {
      $this->validate($request, array(
              'start_date' => 'bail|required',
              'pre_approved' => 'bail|required',
              'days_requested' => 'bail|required',
              'leave_type' => 'bail|required|min:4',
              'approver_username' => 'required|max:50',
              // 'applicant_username' => 'required|max:50',
            )
      );
      $pre_approved = $request->get('pre_approved');
      if($pre_approved == "0"){
        Session::flash('flash_message','Please specify whether or not this request was approved');
        return redirect()->back()->withInput();
      }
      $created_by = session('applying_for', null);
      if($created_by== null){
        Session::flash('flash_message','Applying for username is null');
        return redirect()->back()->withInput();
      }
      $current = Carbon::now()->toDateTimeString();
      $input = new App\LeaveRequest;
      try{
        if($request->has('id')){
          $id = $request->input('id');
          $input = LeaveRequest::find($id);
        }
      }catch(\Exception $ex){

      }
      
      $input -> start_date = $this->setDate($request->start_date);
      ////var_dump('Start date: ' . $input -> start_date);
      $input -> days_requested = $request->days_requested;

      ////var_dump('days requested: ' . $input -> days_requested);
      $input -> end_date = Carbon::parse($this->calculateLeaveEndDate($input));

      ////var_dump('end_date : ' . $input -> end_date);
      // //dd($input);
      //$input -> end_date = $this->setDate($request->end_date);
      $input -> leave_type = $request->leave_type;
      $input -> supervisor_username = $this->convertEmailToUsername($this->removeSpaceAndConcatenateUsername($request->approver_username));
      $input -> stand_in_username = $this->convertEmailToUsername($this->removeSpaceAndConcatenateUsername($request->stand_in_username));
      $input -> created_by = $created_by;//$this->convertEmailToUsername($this->removeSpaceAndConcatenateUsername($request->applicant_username));//Auth::user()->username;
      $input -> date_submitted = $current;//->toDateTimeString();
      $input -> date_created = $current;//->toDateTimeString();
      $input -> created_at = $current;
      $input -> updated_at = $current;
      $input -> assisted_by = $request->user()->username;
      
      // dd($request->applicant_username);
      $applicant = User::where('username', $input->created_by)->first();
      $input -> name = $applicant->getName(); 
      // $input -> md_approval_required = Auth::user()->is_contract_staff;

      $leave_type = LeaveType::where('name', $input->leave_type)->first();

      if($leave_type->requireDocument()){//$input->leave_type == "Sick" or $input->leave_type == "Paternity" or $input->leave_type == "Maternity" or $input->leave_type == "Examination"){
        if(!$request->file('documents')){
          Session::flash("flash_message", "Please attach a document or picture to support your application");
          return redirect()->back()->withInput();
        }
      }
              
      // $input-> days_left = $this->getDaysLeftByLeaveType($leave_type, Auth::user(), Carbon::now());
      $input-> days_left = $this->getLeaveBalanceLeftByLeaveType($leave_type, Auth::user(), Carbon::now());
      $input -> section = Auth::user()->section;
      $input-> reason = $request->reason;

      if($pre_approved){
        $input->stand_in_response=true; //When apply for the request is pre-approved
        $input->stand_in_response_date = Carbon::now();
        $input->supervisor_response=true; //When apply for the request is pre-approved
        $input->supervisor_response_date=Carbon::now(); 
      }

      //verify that stand in is correct
      if($input ->stand_in_username){
        try{
            $usersADAccount = $this->searchForUserInAD($this->getADConnection(), $input ->stand_in_username, null);
            if(!$usersADAccount){
              Session::flash("flash_message", "Stand in username is incorrect, please fix");
              return redirect()->back()->withInput();
            }
        }catch(\Exception $e){

        }
      }

      //verify that supervisor is correct
      try{
        $usersADAccount = $this->searchForUserInAD($this->getADConnection(), $input ->supervisor_username, null);
        if(!$usersADAccount){
          Session::flash("flash_message", "Supervisor username is incorrect, please fix");
          return redirect()->back()->withInput();
        }
      }catch(\Exception $e){
        
      }
      if($leave_type->requireBalance() && $input->days_left < $input->days_requested){
          Session::flash('flash_message', 'You cannot take more than ' . $input->days_left . ' day(s) ' . $input->leave_type . ' leave!');
          return redirect()->back()->withInput();
      }
      try {
          // dd($input);
        $input->save();
      // var_dump($input);
        
        Session::flash('flash_message', 'Request submitted successfully!');
        $this->upload($request, $input);
      } catch (\Exception $e) {
          // dd($e);
        return redirect()->back()->withInput()->with('flash_message', $e->getMessage());
      }
      $this->hrApprove($request, $input->id);
      return redirect()->back()->with('flash_message','Request saved successully');
  }

  public function upload(Request $request, $application)
  {
      if($request->documents == null){
        return;
      }
      foreach($request->documents as $file)
      {
          try
          {
              $doc = new Document();
              //Display File Name
              $doc->description = $file->getClientOriginalName();
              //Display File Extension
              $doc->ext = $file->getClientOriginalExtension();
              //Display File Size
              $doc->size = $file->getSize();
              //Display File Mime Type
              $doc->type = $file->getMimeType();
              //Save Uploaded File
              $doc->filename = $file->store('documents');
              $doc->uploaded_by = $request->user()->username;
              $doc->leave_request_id = $application->id;
              $doc->save();
          }
          catch(\Exception $ex)
          {
            dd($ex);
          }
      }
  }

  public function getUser($username){
    $user = App\User::where('username', '=', $username)->first();
    if(!isset($user)){
      $user = new App\User();
      $user->username = strtolower($username);

      if(strpos($username, ".")>0){
        $parts = explode(".", $username);
        if(sizeOf($parts)==2){
          $user->name = ucwords(strtolower(trim($parts[0]))) . " " . ucwords(strtolower(trim($parts[1])));
        }
      }
    }
    return $user;
  }

  public function getSwiftMailer(){
    $transport = \Swift_SmtpTransport::newInstance(
    \Config::get('mail.host'),
    \Config::get('mail.port'),
    \Config::get('mail.encryption'))
    ->setUsername(\Config::get('mail.username'))
    ->setPassword(\Config::get('mail.password'))
    ->setStreamOptions(['ssl' => \Config::get('mail.ssloptions')]);

    $mailer = \Swift_Mailer::newInstance($transport);
    return $mailer;
  }

  public function getRoleMembers($roleName){
      $roleWithMembers = Role::with('users')->where('name', $roleName)->get();
      return $roleWithMembers;
  }


  public function getWorkingDays(LeaveRequest $input){
    if(!isset($input)){
      return 0;
    }
    return $this->getWorkingDaysBetweenTwoDates($input->start_date, $input->end_date);
  }

public function getWorkingDaysBetweenTwoDates(Carbon $start, Carbon $end){
      ////var_dump('At beginnig of get working days between two dates<br/>');
      ////var_dump('<br/>Start : ' . $start);
      ////var_dump('<br/>End : ' . $end);
      $workdays = 0;
      $hols=0;
      $starting = Carbon::create($start->year, $start->month, $start->day);
      $ending = Carbon::create($end->year, $end->month, $end->day);
      $maxDays = $ending->diffInDays($starting);
      // dd($maxDays);
      $counter=0;
      while ($counter<=$maxDays) {

        ////echo("Counter = ");//echo($counter);
        $isHoliday = Holiday::whereDay('date', $starting->day)
            ->whereMonth('date', $starting->month)
            ->whereYear('date', $starting->year)
            ->count()>0;

        // if($starting->isWeekend()===true || $isHoliday===true){
        //     $hols+=1;
        // }
        if($starting->isWeekend()==false && $isHoliday==false){
              $workdays+=1;
        }
        // //echo("<br/>Date: ");//echo($starting->toDayDateTimeString());
        // //echo("<br/>Is Holiday: ");////////var_dump($isHoliday);
        // //echo("<br/>Is Weekend: ");////////var_dump($starting->isWeekend());
        // //echo("<br/>Work Days: ");//echo($workdays);
        // //echo("<br/>Holidays: ");//echo($hols);
        // //echo("<br/>Counter: ");//echo($counter);
        // //echo("<br/>--------------------------------<br/>");
        $starting = $starting->addDays(1);
        $counter+=1;
      }
      //echo("<br/>Counter Ended: ");//echo($counter);
      //////////dd($maxDays);
      ////var_dump('<br/>At end of get working days between two dates<br/>');
      ////var_dump('<br/>Starting date: ' . $starting);
      ////var_dump('<br/>Ending date: ' . $ending);
      ////var_dump('<br/>Start : ' . $start);
      ////var_dump('<br/>End : ' . $end);
      return $workdays;
  } 

  public function getResumptionDate(Request $request){
    $url = $request->fullUrl();
    $days = 0;
    $date = 0;

    if($request->has('days')){
      $days = $request->input('days');
    }
    if($request->has('date')){
      $date = $request->input('date');
    }

    $input = new App\LeaveRequest;
    $input->start_date = $this->setDate($date);
    $input->days_requested = $days;
    // return "Days: " . $days . " Date: " . $date;

    $resumption_date =  $this->calculateLeaveEndDate($input);

    $parts = explode("-",$resumption_date);
    if(sizeof($parts))
    return $parts[2] . "/" . $parts[1] . "/" . $parts[0];
    return $resumption_date;
  }
  public function calculateLeaveEndDate(LeaveRequest $input){
      $workdays = 0;
      $hols=0;
      $ending = null; //Carbon::parse($input->end_date);
      $current = Carbon::parse($input->start_date);
      $maxDays = $input->days_requested;//$ending->diffInDays($starting);
      $nextDay = Carbon::create($current->year, $current->month, $current->day+1);
      if($maxDays ==1 && $nextDay->isWeekend()==false && $this->isHoliday($nextDay)==false){
        return $nextDay->toDateString();
      }
      $counter=0;
      while ($workdays<$maxDays) {

        ////echo("Counter = ");//echo($counter);
        $isHoliday = Holiday::whereDay('date', $current->day)
            ->whereMonth('date', $current->month)
            ->whereYear('date', $current->year)
            ->count()>0;

        // if($starting->isWeekend()===true || $isHoliday===true){
        //     $hols+=1;
        // }
        if($current->isWeekend()==false && $isHoliday==false){
              $workdays+=1;
        }
        // //echo("<br/>Date: ");//echo($starting->toDayDateTimeString());       // //echo("<br/>Is Holiday: ");//////////var_dump($isHoliday);        // //echo("<br/>Is Weekend: ");//////////var_dump($starting->isWeekend());        // //echo("<br/>Work Days: ");//echo($workdays);        // //echo("<br/>Holidays: ");//echo($hols);        // //echo("<br/>Counter: ");//echo($counter);        // //echo("<br/>--------------------------------<br/>");      //  if($workdays<=$maxDays){
          $current = $current->addDays(1);
          $counter+=1;
        //}
      }
      while($current->isWeekend() or $this->isHoliday($current)){
        $current=$current->addDays(1);
      }
      ////echo("<br/>Counter Ended: ");//echo($counter);
      //////////////dd($current);
      return $current->toDateString();
  }

  public function isHoliday($current){
    $isHoliday = Holiday::whereDay('date', $current->day)
        ->whereMonth('date', $current->month)
        ->whereYear('date', $current->year)
        ->count()>0;
        return $isHoliday;
  }


  public function acceptStandInRequest(Request $request, $id){

      $input = LeaveRequest::where('id', $id)->first();
      if($input==null){
          Session::flash('flash_message', 'Request not found');
        return back();
      }
      if($input->stand_in_response == true){
          Session::flash('flash_message', 'Request already accepted');
          return back();
      }
      if(strtolower($input->stand_in_username) != strtolower(Auth::user()->username)){
        Session::flash('flash_message', 'You cannot stand in for this request!');
        return back();
      }
      else{
        $current = Carbon::now();
        $input -> stand_in_response=true;
        $input -> stand_in_response_date = $current;//->toDateTimeString();
        if(array_key_exists('reason', $request))
        {
          $input -> stand_in_response_reason = $request->reason;
        }
        $input -> stand_in_username = Auth::user()->username;
        $input -> updated_at = $current;
        $input->save();
        Session::flash('flash_message', 'Your stand in acceptance was successful!');

        //$input->status = "Accepted";
        $applicant = App\User::where('username', '=', $input->created_by)->first();
        $approver = $this->getUser($input->stand_in_username);
        $supervisor = $this->getUser($input->supervisor_username);
        try{
          Mail::setSwiftMailer($this->getSwiftMailer());
          $cc = $this->getHREmailAddresses();
          array_push($cc, $request->user()->email());
          Mail::to($applicant->email(), $applicant->name)->cc($cc)
              ->send(new StandInApproved($input, $applicant, $approver));
        }catch(\Exception $ex){
	           Session::flash("flash_message","Your request was processed successfully, but the email notifications failed");
            //    ////////////dd($ex);
        }
        try{
         Mail::setSwiftMailer($this->getSwiftMailer());
         $cc = $this->getHREmailAddresses();
         array_push($cc, $applicant->email());
         Mail::to($supervisor->email(), $supervisor->name)->cc($cc)
        ->send(new NewSupervisorPleaseApproveRequest($input, $applicant, $supervisor));
        }catch(\Exception $ex){
	           Session::flash("flash_message","Your request was processed successfully, but the email notifications failed");
          // //////////////dd($ex);
        }
      }
      return redirect()->back();
  }


  public function hrApprove(Request $request, $id){

      $input = LeaveRequest::find($id);

      if($input == null){
        Session::flash('flash_message', 'Request not found, approval failed!');
        return redirect()->back();
      }

      if($input->hr_response == true){
          Session::flash('flash_message', 'Already approved!');
          return redirect()->back();
      }

    //HR Approval can be done by supervisor
    //   if(!Auth::user()->hasRole("HR")){
    //     Session::flash('flash_message', 'You cannot accept this request!');
    //     return redirect()->back();
    //   }
      else{
        $current = Carbon::now();
        $input -> hr_response=true;
        $input -> hr_response_date = $current;//->toDateTimeString();
        if(array_key_exists('reason', $request))
        {
          $input -> hr_response_reason = $request->reason;
        }
        $input -> hr_username = Auth::user()->username;
        $input -> updated_at = $current;
        $input->save();
        if($input->md_approval_required == false){

        }
          $approval = LeaveApproval::where('leave_request_id', $input->id)->first();
          if(isset($approval)){
              return redirect()->back()->with('flash_message', 'This request has already been approved!');
          }
          $approval = new App\LeaveApproval();
          $approval->leave_request_id = $input->id;
          $approval->applicant_username = $input->created_by;
          $approval->date_approved = $current;
          $approval->approved_by = Auth::user()->username;
          $approval->leave_type = $input->leave_type;
          $approval->days = $input->days_requested;
          $approval->save();
        Session::flash('flash_message', 'You approved successfully!');

        //update this users leave balance across all request of the said type
        // $pending_supervisor = LeaveRequest::where('created_by', $approval->applicant_username)
        //     ->where('leave_type','=', $approval->leave_type)
        //     ->where('stand_in_response','=', null)
        //     ->orWhere('supervisor_response','=', null)
        //     ->whereDay('end_date', '<', $current)
        //     ->get();

        // $this->updateLeaveDaysLeft($pending_supervisor, $approval);
          $this->fixLeaveDaysLeft($request->user());

        // $applicants_pending_requests = LeaveRequest::where('');
        $applicant = $this->getUser($input->created_by);
        $approver = $this->getUser($input->hr_username);
        $dev = App::isLocal();
        try{
          if(!$dev){
            Mail::setSwiftMailer($this->getSwiftMailer());
            Mail::to($applicant->email(), $applicant->name)->send(new HRApproved($input, $applicant, $approver));
          }
        }catch(\Exception $ex){
	       Session::flash("flash_message","Your request was processed successfully, but the email notifications failed");
        }
        if($dev){
          return redirect()->back();
        }
        try{
          Mail::setSwiftMailer($this->getSwiftMailer());
          $cc = $this->getHREmailAddresses();
          array_push($cc, $request->user()->email());//cc hr and the boss
          array_push($cc, "leave@abujaelectricity.com");//cc hr and the boss
          Mail::to($request->user()->email(), $request->user()->name)->cc($cc)
          ->send(new LeaveApprovedNotification($input, $applicant, $approver));
        //  Mail::to($approver->email(), $approver->name)->send(new LeaveApprovedNotification($input, $applicant, $approver));
        }catch(\Exception $ex){
	           Session::flash("flash_message","Your request was processed successfully, but the email notifications failed");
        }
      }
      return redirect()->back();
  }

  public function getDaysLeftByLeaveTypeWeb(Request $request, $type, $user= null){
    return getDaysLeftByLeaveType($type, $user);
  }


  //----------------------------------------------------------------------------------------//
  // Returns the number of leave days available for this user in the current year for the specified leave type
  //----------------------------------------------------------------------------------------//
  public function getDaysLeftByLeaveType($leave_type, App\User $user = null, $year = null, $pointInTime= null){
    if($user===null) {
      
         $user = Auth::user();
        }
  // dd($user);
    //var_dump("<br/>In fn getDaysLeftByLeaveType() <br/>");
    //var_dump("<br/>Year: " . $year . "<br/>");  
    //var_dump("<br/>PointInTime: " . $pointInTime . "<br/>");  
    //var_dump("<br/>User: " . $user . "<br/>");  
    //var_dump("<br/>Leave Type: " . $leave_type->name . "<br/>");  
    
    if($year == null){
          $year =  Carbon::now();
      }
      if($pointInTime == null){
        $pointInTime =  Carbon::now();
    }
      if(\Auth::check()==false){
          Session::flash('flash_message', 'Please login first!');
          return;
      }

     
      if($user == null){
        return 0;
      }

      //lets assume he has 0 days left
      $daysLeft =0;

      if($user->is_contract_staff){
            // $year = $user->
      }

      //Lets check for the number of days he has already taken for this leave type
      $daysGranted = $this->queryLeaveApprovalsForDaysTaken($leave_type,$user, $year, $pointInTime);

      // //////////dd($year);
      //var_dump("<br/> Days taken for " . $year->year . " as @ " . $pointInTime . " = " . $daysGranted);
      $last_year = Carbon::create($year->year-1, 1,1); //I replace $year->subYear() //This may be the bug?? Jan 3, 2018 @ 3:28pm

      //Lets get the number of days he was given last year
      $days_granted_last_year =$this->queryLeaveApprovalsForDaysTaken($leave_type,$user, $last_year, $pointInTime);
      
      // //////////dd($days_granted_last_year);
      //var_dump("<br/> Days taken last year" . $last_year->year . " as @ " . $pointInTime . " = " . $days_granted_last_year);
      
      //Lets get days entitled to last year
      $daysEntitledToLastYear = $this->getDaysEntitledToAsOfXPointInTime($leave_type, $user, $last_year);
      //var_dump("<br/> Days Entitled to Last year" . $last_year->year . " as @ " . $pointInTime . " = " . $daysEntitledToLastYear);
      
      //Lets assume he is entitled to 0 days this year
      $daysEntitledTo=0;

      //////var_dump('<br/>before call to getDaysEntitledToAsOfXPointInTime<br/>');
      //Lets ccheck for te number of days he is entitled to at this point in time
      $daysEntitledTo = $this->getDaysEntitledToAsOfXPointInTime($leave_type, $user);
      //var_dump("<br/> Days Entitled to THIS year" . $year->year . " as @ " . $pointInTime . " = " . $daysEntitledTo);
      
      //////var_dump($leave_type->name);
      // ////dd($daysEntitledTo);

      //Now that we know how many days he is entitled to and how many days have been approved, lets calculate how 
      //many days he has left
      //***Bug*** This code does not take cognizance of the fact that this guy may have been refunded maybe this check 
      //occcurs somewhere else
      $daysLeft = $daysEntitledTo -$daysGranted;

      //var_dump("<br/> Days Left after deducting days taken from days granted THIS year = " . $daysLeft);
      

      // if($leave_type->name =="Annual")
      // {
      //   //var_dump("EntitledTo: " . $daysEntitledTo);
      //   //var_dump("Granted: " . $daysGranted);
      //   //var_dump("Left: " . $daysLeft);
      //   dd($daysLeft);
      // }
      // $extraDaysGranted = LeaveGrant::where('leave_type', $leave_type->name)
      //   ->whereYear('date_granted', $year->year)->get();//->whereMon
      //   ->whereYear('expiry_date', $year->year)
      //   ->whereMonth('expiry_date', '<=', $year->month)
      //   ->whereDay('expiry_date', '<=', $year->day)->get();
      
      // if(isset($extraDaysGranted)){
      //   foreach($extraDaysGranted as $grant){
      //     if()
      //   }
      // }

      //??? Why would days left be null
      // if(!isset($daysLeft)){
      //   $daysLeft=$daysEntitledTo;
      // }

      //Lets assume he didnt carry over any days
      $days_carried_over=0;
        // dd($daysEntitledToLastYear);
      //Carry over over annual leave to a max of 10 days
      //December 6, 2017 @ 2:18pm Tweaked this logic to make calculations based on days entitled to last year
      if($leave_type->name =='Annual'){

          //Do carry over only for those who applied only and got approvals online
          $has_online_approvals = LeaveApproval::where('applicant_username', '=',$user->username)
          ->where('leave_request_id', '>', 0)->count();

          if($has_online_approvals){

            if($days_granted_last_year < $daysEntitledToLastYear && $last_year->year > 2016){ //This system came into use in 2017, so the first last year will be 2017
                $days_carried_over = $daysEntitledToLastYear - $days_granted_last_year; 
                if($days_carried_over > 10){
                    $days_carried_over = 10;
                }
            }
            $daysLeft += $days_carried_over;
          }
      }
      //var_dump("<br/> Days carried over from last year = " . $days_carried_over);
      
      ////////var_dump($daysEntitledTo);
      //////////var_dump($daysGranted);
      ////////dd($daysLeft);
      return $daysLeft;
  }

  public function getDaysCarriedOver($thisYear, $user){

    $last_year = Carbon::create($thisYear->year -1, 1,1); //I replace $year->subYear() //This may be the bug?? Jan 3, 2018 @ 3:28pm

    //Do carry over only for those who applied only and got approvals online
    $has_online_approvals = LeaveApproval::where('applicant_username', '=',$user->username)
        ->where('leave_request_id', '>', 0)
        ->whereYear('created_at', '=', $last_year->year)
        ->count();
    
    if(!$has_online_approvals){
      return 0;
    }
    
    //check if user has a last year, bc for those on contract and new staff, their years are not calendar years
    $contract_start_date = Carbon::parse($user->resumption_date);

    //Note contract staff are special, that is why they are excluded from the rule below
    if($user->is_contract_staff == false and $contract_start_date->diffInDays(Carbon::now())<365){
      //if user was employed less than a year ago abort, he does not have records for last year
      return 0;
    }


    if($user->is_contract_staff){
      //calculate their start and end date and use that to query their records
      $previous_contract_dates = $this->getUsersPreviousContractDates($user);
      if(isset($previous_contract_dates)){
        
      }
    }

    $pointInTime = $last_year;
    $leave_type = new LeaveType();
    $leave_type->name = "Annual";
    // //echo("Last Year = ".$last_year->year);
    // die();
    //Lets assume user will carry over nothing
    $days_carried_over = 0; 

          //Lets get the number of days he was given last year
          $days_granted_last_year =$this->queryLeaveApprovalsForDaysTakenB($leave_type,$user, $last_year, $pointInTime);
          
          // //////////dd($days_granted_last_year);
          //var_dump("<br/> Days taken last year" . $last_year->year . " as @ " . $pointInTime . " = " . $days_granted_last_year);
          
          //Lets get days entitled to last year
          $daysEntitledToLastYear = $this->getDaysEntitledToAsOfXPointInTime($leave_type, $user, $last_year);
          //var_dump("<br/> Days Entitled to Last year" . $last_year->year . " as @ " . $pointInTime . " = " . $daysEntitledToLastYear);
          
          //Carry over over annual leave to a max of 10 days
      //December 6, 2017 @ 2:18pm Tweaked this logic to make calculations based on days entitled to last year
      if($leave_type->name =='Annual'){
        
        if($days_granted_last_year < $daysEntitledToLastYear && $last_year->year > 2016){ //This system came into use in 2017, so the first last year will be 2017
            $days_carried_over = $daysEntitledToLastYear - $days_granted_last_year; 
            if($days_carried_over > 10){
                $days_carried_over = 10;
            }
        }
      }
    // var_dump("<br/> Days carried over from last year = " . $days_carried_over);
    return $days_carried_over;
  }

  //----------------------------------------------------------------------------------------//
  // This method is used specifically by the the Leave Days Left Module and the DDL of the New Request Form
  // Because it has a unique clause that takes care of new recruits
  // Returns the number of leave days available for this user in the current year for the specified leave type
  //----------------------------------------------------------------------------------------//
  public function getLeaveBalanceLeftByLeaveType($leave_type, App\User $user = null, $year = null, $pointInTime= null){
    if($user===null) {
      
         $user = Auth::user();
        }
  
    //var_dump("<br/>In fn getDaysLeftByLeaveType() <br/>");
    //var_dump("<br/>Year: " . $year . "<br/>");  
    //var_dump("<br/>PointInTime: " . $pointInTime . "<br/>");  
    //var_dump("<br/>User: " . $user . "<br/>");  
    //var_dump("<br/>Leave Type: " . $leave_type->name . "<br/>");  
    
    if($year == null){
          $year =  Carbon::now();
      }
      if($pointInTime == null){
        $pointInTime =  Carbon::now();
    }
      if(\Auth::check()==false){
          Session::flash('flash_message', 'Please login first!');
          return;
      }

     
      if($user == null){
        return 0;
      }

      //lets assume he has 0 days left
      $daysLeft =0;

      if($user->is_contract_staff){
            // $year = $user->
      }

      //Lets check for the number of days he has already taken for this leave type
      $daysGranted = $this->queryLeaveApprovalsForDaysTaken($leave_type,$user, $year, $pointInTime);
      // if($leave_type->name =="Annual"){
      //   dd($daysGranted);
      // }
      $contract_start_date = Carbon::parse($user->resumption_date);
      $current_year = Carbon::now();
      if($contract_start_date->diffInDays($current_year)<365){
          $start = $contract_start_date->format('Y-m-d') . " 00:00:00";
          $end = $current_year->format('Y-m-d') . " 23:59:59" ;
          
          $daysGranted = LeaveApproval::where('leave_type',$leave_type->name)
          ->where('applicant_username', $user->username)
          ->whereBetween('created_at', [$start, $end])
          // ->where('created_at', '<=', $pointInTime)
          ->sum('days');
          // if($leave_type->name == "Annual"){
          //   dd($daysGranted);
          // }
      }
      

      // //////////dd($year);
      //var_dump("<br/> Days taken for" . $year->year . " as @ " . $pointInTime . " = " . $daysGranted);
      $last_year = Carbon::create($year->year-1, 1,1); //I replace $year->subYear() //This may be the bug?? Jan 3, 2018 @ 3:28pm

      //Lets get the number of days he was given last year
      $days_granted_last_year =$this->queryLeaveApprovalsForDaysTaken($leave_type,$user, $last_year, $pointInTime);

      // //////////dd($days_granted_last_year);
      //var_dump("<br/> Days taken last year" . $last_year->year . " as @ " . $pointInTime . " = " . $days_granted_last_year);
      
      //Lets get days entitled to last year
      $daysEntitledToLastYear = $this->getDaysEntitledToAsOfXPointInTime($leave_type, $user, $last_year);
      //var_dump("<br/> Days Entitled to Last year" . $last_year->year . " as @ " . $pointInTime . " = " . $daysEntitledToLastYear);
      
      // dd($daysEntitledToLastYear);

      //Lets assume he is entitled to 0 days this year
      $daysEntitledTo=0;

      //////var_dump('<br/>before call to getDaysEntitledToAsOfXPointInTime<br/>');
      //Lets ccheck for te number of days he is entitled to at this point in time
      $daysEntitledTo = $this->getDaysEntitledToAsOfXPointInTime($leave_type, $user);
      //var_dump("<br/> Days Entitled to THIS year" . $year->year . " as @ " . $pointInTime . " = " . $daysEntitledTo);
      // if($leave_type->name =="Annual"){
      //   dd($daysEntitledTo);
      // }
      //////var_dump($leave_type->name);
      // ////dd($daysEntitledTo);

      //Now that we know how many days he is entitled to and how many days have been approved, lets calculate how 
      //many days he has left
      //***Bug*** This code does not take cognizance of the fact that this guy may have been refunded maybe this check 
      //occcurs somewhere else
      $daysLeft = $daysEntitledTo -$daysGranted;

      //var_dump("<br/> Days Left after deducting days taken from days granted THIS year = " . $daysLeft);
      

      // if($user->)

      // if($leave_type->name =="Annual")
      // {
      //   //var_dump("EntitledTo: " . $daysEntitledTo);
      //   //var_dump("Granted: " . $daysGranted);
      //   //var_dump("Left: " . $daysLeft);
      //   dd($daysLeft);
      // }
      // $extraDaysGranted = LeaveGrant::where('leave_type', $leave_type->name)
      //   ->whereYear('date_granted', $year->year)->get();//->whereMon
      //   ->whereYear('expiry_date', $year->year)
      //   ->whereMonth('expiry_date', '<=', $year->month)
      //   ->whereDay('expiry_date', '<=', $year->day)->get();
      
      // if(isset($extraDaysGranted)){
      //   foreach($extraDaysGranted as $grant){
      //     if()
      //   }
      // }

      //??? Why would days left be null
      // if(!isset($daysLeft)){
      //   $daysLeft=$daysEntitledTo;
      // }

      //Lets assume he didnt carry over any days
      $days_carried_over=0;

      // dd($daysEntitledToLastYear);
      // dd($days_granted_last_year);
      //Carry over over annual leave to a max of 10 days
      //December 6, 2017 @ 2:18pm Tweaked this logic to make calculations based on days entitled to last year
      
      //Feb 1, 2018, Dont carry over for staff who never applied online
          //Do carry over only for those who applied only and got approvals online
          $has_online_approvals = LeaveApproval::where('applicant_username', '=',$user->username)
          ->where('leave_request_id', '>', 0)->count();
          
      
      if($contract_start_date->diffInDays(Carbon::now())>365 && $has_online_approvals){ //Jan 11, 2018 - Non-contracct employees under 1 year dont carry over, cause they are still being prorated
        if($leave_type->name =='Annual'){
            if($days_granted_last_year < $daysEntitledToLastYear && $last_year->year > 2016){ //This system came into use in 2017, so the first last year will be 2017
                $days_carried_over = $daysEntitledToLastYear - $days_granted_last_year; 
                if($days_carried_over > 10){
                    $days_carried_over = 10;
                }
            }
            $daysLeft += $days_carried_over;
        }
      }
      //var_dump("<br/> Days carried over from last year = " . $days_carried_over);
      
      ////////var_dump($daysEntitledTo);
      //////////var_dump($daysGranted);
      ////////dd($daysLeft);
      
        
      return $daysLeft;
  }

  public function getLeaveBalanceLeftByLeaveTypeBetween($leave_type, App\User $user = null, $start = null, $end= null){
    if($user===null) {
         $user = Auth::user();
    }
  
    //var_dump("<br/>In fn getDaysLeftByLeaveType() <br/>");
    //var_dump("<br/>Year: " . $year . "<br/>");  
    //var_dump("<br/>PointInTime: " . $pointInTime . "<br/>");  
    //var_dump("<br/>User: " . $user . "<br/>");  
    //var_dump("<br/>Leave Type: " . $leave_type->name . "<br/>");  
    
    if($start == null){
      return null;
    }
    if($end == null){
      return null;
    }
    if($leave_type == null){
      return null;
    }
    if($user == null){
      return null;
    }

    //lets assume he has 0 days left
    $daysLeft =0;

      //Lets check for the number of days he has already taken for this leave type
    $daysGranted = 0;
      
    $contract_start_date = Carbon::parse($user->resumption_date);
    $current_year = Carbon::now();
    
    //Leave system is calendar year based after a user has exhausted his 1st year
    if($contract_start_date->diffInDays($current_year)<365){
      $daysGranted = $this->queryLeaveApprovalsForDaysTakenBetween($leave_type,$user, $start, $end);
    }else{
      $daysGranted = $this->queryLeaveApprovalsForDaysTaken($leave_type,$user);
    }
    
    //Commented out on 11th jan 2018 while testing out date range filters
    // if($contract_start_date->diffInDays($current_year)<365){
    //       $start = $contract_start_date->format('Y-m-d') . " 00:00:00";
    //       $end = $current_year->format('Y-m-d') . " 23:59:59" ;
          
    //       $daysGranted = LeaveApproval::where('leave_type',$leave_type->name)
    //       ->where('applicant_username', $user->username)
    //       ->whereBetween('created_at', [$start, $end])
    //       // ->where('created_at', '<=', $pointInTime)
    //       ->sum('days');
    //       if($leave_type->name == "Annual"){
    //         // dd($daysGranted);
    //       }
    //   }
      

      // //////////dd($year);
      //var_dump("<br/> Days taken for" . $year->year . " as @ " . $pointInTime . " = " . $daysGranted);
    $last_year = Carbon::create($year->year-1, 1,1); //I replace $year->subYear() //This may be the bug?? Jan 3, 2018 @ 3:28pm

    //Lets get the number of days he was given last year
    if($contract_start_date->diffInDays($current_year)<365){
      $previous_contract_dates = $this->getUsersPreviousContractDates($user);
      if(isset($previous_contract_dates) and sizeof($previous_contract_dates)>1){
        $start = $previous_contract_dates[0];
        $end = $previous_contract_dates[1];
      }
      $days_granted_last_year = $this->queryLeaveApprovalsForDaysTakenBetween($leave_type,$user, $start, $end);
    }else{
      $days_granted_last_year = $this->queryLeaveApprovalsForDaysTaken($leave_type,$user, $last_year);
    }
      // //////////dd($days_granted_last_year);
      //var_dump("<br/> Days taken last year" . $last_year->year . " as @ " . $pointInTime . " = " . $days_granted_last_year);
      
      //Lets get days entitled to last year
      $daysEntitledToLastYear = $this->getDaysEntitledToAsOfXPointInTime($leave_type, $user, $last_year);
      //var_dump("<br/> Days Entitled to Last year" . $last_year->year . " as @ " . $pointInTime . " = " . $daysEntitledToLastYear);
      
      //Lets assume he is entitled to 0 days this year
      $daysEntitledTo=0;

      //////var_dump('<br/>before call to getDaysEntitledToAsOfXPointInTime<br/>');
      //Lets ccheck for te number of days he is entitled to at this point in time
      $daysEntitledTo = $this->getDaysEntitledToAsOfXPointInTime($leave_type, $user);
      //var_dump("<br/> Days Entitled to THIS year" . $year->year . " as @ " . $pointInTime . " = " . $daysEntitledTo);
      
      // dd($daysEntitledTo);
      //////var_dump($leave_type->name);
      // ////dd($daysEntitledTo);

      //Now that we know how many days he is entitled to and how many days have been approved, lets calculate how 
      //many days he has left
      //***Bug*** This code does not take cognizance of the fact that this guy may have been refunded maybe this check 
      //occcurs somewhere else
      $daysLeft = $daysEntitledTo -$daysGranted;

      //var_dump("<br/> Days Left after deducting days taken from days granted THIS year = " . $daysLeft);
      

      // if($user->)

      // if($leave_type->name =="Annual")
      // {
      //   //var_dump("EntitledTo: " . $daysEntitledTo);
      //   //var_dump("Granted: " . $daysGranted);
      //   //var_dump("Left: " . $daysLeft);
      //   dd($daysLeft);
      // }
      // $extraDaysGranted = LeaveGrant::where('leave_type', $leave_type->name)
      //   ->whereYear('date_granted', $year->year)->get();//->whereMon
      //   ->whereYear('expiry_date', $year->year)
      //   ->whereMonth('expiry_date', '<=', $year->month)
      //   ->whereDay('expiry_date', '<=', $year->day)->get();
      
      // if(isset($extraDaysGranted)){
      //   foreach($extraDaysGranted as $grant){
      //     if()
      //   }
      // }

      //??? Why would days left be null
      // if(!isset($daysLeft)){
      //   $daysLeft=$daysEntitledTo;
      // }

      //Lets assume he didnt carry over any days
      $days_carried_over=0;

      // dd($daysEntitledToLastYear);
      // dd($days_granted_last_year);
      //Carry over over annual leave to a max of 10 days
      //December 6, 2017 @ 2:18pm Tweaked this logic to make calculations based on days entitled to last year
      if($leave_type->name =='Annual'){
          if($days_granted_last_year < $daysEntitledToLastYear && $last_year->year > 2016){ //This system came into use in 2017, so the first last year will be 2017
              $days_carried_over = $daysEntitledToLastYear - $days_granted_last_year; 
              if($days_carried_over > 10){
                  $days_carried_over = 10;
              }
          }
          $daysLeft += $days_carried_over;
      }
      //var_dump("<br/> Days carried over from last year = " . $days_carried_over);
      
      ////////var_dump($daysEntitledTo);
      //////////var_dump($daysGranted);
      ////////dd($daysLeft);
      
        
      return $daysLeft;
  }

  public function queryLeaveApprovalsForDaysTaken(LeaveType $leave_type, App\User $user, $year = null, $pointInTime =  null){
      
      if($year == null){
          $year = Carbon::now();
      }
      if($pointInTime == null){
        $pointInTime = Carbon::now();
      }
      $daysGranted = 0;
      //var_dump("<br/>In fn queryLeaveApprovalsForDaysTaken() <br/>");
      //var_dump("<br/>===============Year: " . $year . "<br/>");  
      //var_dump("<br/>===============PointInTime: " . $pointInTime . "<br/>");  
      //var_dump("<br/>===============User: " . $user->name . "<br/>");  
      //var_dump("<br/>===============Leave Type: " . $leave_type->name . "<br/>");  
      
      // $daysEntitledTo = $this->getDaysEntitledToAsOfXPointInTime($leave_type, $user);
      // //////////dd($daysEntitledTo);
      $contract_start_date = Carbon::parse($user->resumption_date);
      $current_year = Carbon::now();
      
      if($user->is_contract_staff){
          // dd($contract_start_date);
          if(!isset($contract_start_date)){
              $contract_start_date = $year;
          }
          $current_contract_start_date = Carbon::create($contract_start_date->year, $contract_start_date->month, $contract_start_date->day);
          // $current_contract_end_date =Carbon::create($contract_start_date->year+1, $contract_start_date->month, $contract_start_date->day);
          
          $current_contract_end_date = clone $current_contract_start_date; //Carbon::create($current_year->year+1, $contract_start_date->month, $contract_start_date->day);
          $current_contract_end_date = $current_contract_end_date->addYear();
          //if a contracct staff began his contract years ago, then in the absence of an accurate contrac date in the last 1 year, guess it
          if($contract_start_date->diffInDays($current_year)>365){
            $current_contract_start_date = Carbon::create($current_year->year, $contract_start_date->month, $contract_start_date->day);
            // $current_contract_end_date =Carbon::create($current_year->year+1, $contract_start_date->month, $contract_start_date->day);
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
          // $d = $contract_start_date->diffInDays($current_year)>365;
          // //////////dd($d);
          //just for a change means nothing
          $start = $current_contract_start_date->format('Y-m-d') . " 00:00:00";
          $end = $current_contract_end_date->format('Y-m-d') . " 23:59:59" ;
          // dd($start);
          $daysGranted = LeaveApproval::where('leave_type',$leave_type->name)
                ->where('applicant_username', $user->username)
                ->whereBetween('created_at', [$start, $end])
                ->where('created_at', '<=', $pointInTime)
                // ->where('created_at', '>=', $current_contract_start_date->toDateString())
                // ->where('created_at', '<=', $current_contract_end_date->toDateString())
                ->sum('days');
                // //////////dd($current_contract_start_date);
          // ////////dd($daysGranted);
      }
      // else if($contract_start_date->diffInDays($current_year)<365){
      //   $start = $contract_start_date->format('Y-m-d') . " 00:00:00";
      //   $end = $current_year->format('Y-m-d') . " 23:59:59" ;
        
      //   $daysGranted = LeaveApproval::where('leave_type',$leave_type->name)
      //   ->where('applicant_username', $user->username)
      //   ->whereBetween('created_at', [$start, $end])
      //   ->where('created_at', '<=', $pointInTime)
      //   ->sum('days');
      //   if($leave_type->name == "Annual"){
      //     dd($daysGranted);
      //   }
      // }
      else{
          $daysGranted = LeaveApproval::where('leave_type',$leave_type->name)
            ->where('applicant_username', $user->username)
            ->whereYear('created_at', $year)
            ->where('created_at', '<=', $pointInTime)
            ->sum('days');
      }
      // if($leave_type->name == "Annual" && $pointInTime->year==2018){
      //       dd($daysGranted);
      // }
      $recall_credit = $this->getRecallCredit($user, $leave_type, $year);
      //var_dump('<br/>Leave Credit: '.$recall_credit);
      //var_dump('<br/>Days granted B4 adding Recall Credit: ' . $daysGranted);
      //var_dump('<br/>Leave Type: ' . $leave_type->name);
      $daysGranted -= $recall_credit; //Deduct days refunded from days granted
      //var_dump('<br/>Days granted AFTER adding Recall Credit: ' . $daysGranted);
      
      // dd($daysGranted);

      return $daysGranted;
  }

  //------------------------------------------------------------------------------------------------------------------------
  // Unlike its name sake this function get days taken between two dates
  //------------------------------------------------------------------------------------------------------------------------
  public function queryLeaveApprovalsForDaysTakenBetween(LeaveType $leave_type, App\User $user, $start = null, $end =  null){
    
    if($start == null){
      //echo("Start date not specified");
      die();
    }
    if($start == null){
      //echo("End date not specified");
      die();
    }
    $daysGranted = 0;
    //var_dump("<br/>In fn queryLeaveApprovalsForDaysTakenBetween() <br/>");
    //var_dump("<br/>===============Start: " . $start . "<br/>");  
    //var_dump("<br/>===============End: " . $end . "<br/>");  
    //var_dump("<br/>===============User: " . $user->name . "<br/>");  
    //var_dump("<br/>===============Leave Type: " . $leave_type->name . "<br/>");  
    
    $current_year = Carbon::now();
    
    
        // dd($start);
        $daysGranted = LeaveApproval::where('leave_type',$leave_type->name)
              ->where('applicant_username', $user->username)
              ->whereBetween('created_at', [$start, $end])
              // ->where('created_at', '<=', $pointInTime)
              // ->where('created_at', '>=', $current_contract_start_date->toDateString())
              // ->where('created_at', '<=', $current_contract_end_date->toDateString())
              ->sum('days');
    
    // if($leave_type->name == "Annual" && $pointInTime->year==2018){
    //       dd($daysGranted);
    // }
    $recall_credit = $this->getRecallCredit($user, $leave_type, $year);
    //var_dump('<br/>Leave Credit: '.$recall_credit);
    //var_dump('<br/>Days granted B4 adding Recall Credit: ' . $daysGranted);
    //var_dump('<br/>Leave Type: ' . $leave_type->name);
    $daysGranted -= $recall_credit; //Deduct days refunded from days granted
    //var_dump('<br/>Days granted AFTER adding Recall Credit: ' . $daysGranted);
    
    // dd($daysGranted);

    return $daysGranted;
  }

  //Returns array
  public function getUsersCurrentContractDates($user){
    $contract_start_date = Carbon::parse($user->resumption_date());


    $current_contract_start_date = Carbon::create($contract_start_date->year, $contract_start_date->month, $contract_start_date->day);
    // $current_contract_end_date =Carbon::create($contract_start_date->year+1, $contract_start_date->month, $contract_start_date->day);
    $current_contract_end_date = clone $current_contract_start_date; //Carbon::create($current_year->year+1, $contract_start_date->month, $contract_start_date->day);
    $current_contract_end_date = $current_contract_end_date->addYear();
    return array(
      "start_date" => $current_contract_start_date,
      "end_date" => $current_contract_end_date);
  }

  //returns array
  public function getUsersPreviousContractDates($user){
    $contract_start_date = Carbon::parse($user->resumption_date());

    $previous_contract_end_date = Carbon::create($contract_start_date->year, $contract_start_date->month, $contract_start_date->day-1);
    $previous_contract_start_date = clone $previous_contract_end_date; //Carbon::create($previous_contract_end_date->year-1, $previous_contract_end_date->month, $previous_contract_end_date->day);
    $previous_contract_start_date = $previous_contract_end_date->subDay();

      return array(
      "start_date" => $previous_contract_start_date->format('Y-m-d') . " 00:00:00",
      "end_date" => $previous_contract_end_date);
  }


  public function q1ueryLeaveEntitlementForDaysEntitledToOriginal(LeaveType $leave_type, App\User $user){
    $days_since_resumption = $this->calculateDaysSinceResumption($user);

    // if($days_since_resumption<180){ //grant new staff the same privileges as 6 month olds
    //     $days_since_resumption = 180;
    // }
    if($days_since_resumption>360){ //grant old (1 year+) staff the max privileges
        $days_since_resumption = 360;
    }
    ////echo("<br/>days since resumption: ");//echo($days_since_resumption);
    $upper_bound = ceil($days_since_resumption/30)*30;
    $lower_bound = floor($days_since_resumption/30)*30;
    $upper_bound -=1;
    // //////////var_dump($lower_bound);
    // ////////dd($upper_bound);
    if($days_since_resumption>=360){ //grant old (1 year+) staff the max privileges
        $lower_bound = $upper_bound = 360;
    }
    // //////////dd($days_since_resumption);
    $daysEntitledTo = LeaveEntitlement::
      where('leave_type','=',$leave_type->name)
      ->where('salary_grade', '=', $user->salary_grade)
      ->where('days_since_resumption', '>=', $lower_bound)
      ->where('days_since_resumption', '<=', $upper_bound)
      ->sum('days_allowed');
      if($leave_type->name =='Compassionate' or $leave_type->name =='Paternity' or $leave_type->name =='Maternity'){
        $daysEntitledTo = LeaveEntitlement::
            where('leave_type','=',$leave_type->name)
            ->where('salary_grade', '=', $user->salary_grade)
            ->max('days_allowed');
      }
      // //echo("<br/>leave_type: ");//echo($leave_type->name);
      // //echo("<br/>salary_grade: ");//echo($user->salary_grade);
      // //echo("<br/>days entitled: ");//echo($daysEntitledTo);
      // }
      if($user->is_contract_staff){
          $daysEntitledTo = LeaveEntitlement::
            where('leave_type','=',$leave_type->name)
            ->where('salary_grade', '=', $user->salary_grade)
            ->max('days_allowed');
      }
    //   ////////////dd($daysEntitledTo);
      if(strtolower($leave_type->name) == "maternity"){
        $annualLeave = new LeaveType();
        $annualLeave->name = "Annual";
        $annualLeaveTaken = $this->queryLeaveApprovalsForDaysTaken($annualLeave, $user);
        if(isset($annualLeaveTaken)){
          $daysEntitledTo = $daysEntitledTo - $annualLeaveTaken;
        }
      }
      // //////////dd($daysEntitledTo);
      return $daysEntitledTo;
  }

   //-------------------------------------------------------------------------------------------------//
   //
   // This function returns the number of leave days a user is entitled
   // ---->It does not deduct the days a user has taken<------
   // Parameter 1 : LeaveType e.g. Annual 
   // Parameter 2 : The User
   // Parameter 3 : The Point in time
   //-------------------------------------------------------------------------------------------------//
   public function getDaysEntitledToAsOfXPointInTime(LeaveType $leave_type, App\User $user, Carbon $pointInTime = null){
    //////var_dump($leave_type->name . '<br/>');

    $days_since_resumption = $this->calculateDaysSinceResumption($user, $pointInTime);

    // //echo('user:' . $user->username . "<br/>");
    // //echo('days since resumption:' . $days_since_resumption . "<br/>");
    //echo('<br/>pointInTime:' . $pointInTime . "<br/>");
    // //echo('LeaveType:' . $leave_type->name . "<br/>");

    // if($days_since_resumption<180){ //grant new staff the same privileges as 6 month olds
    //     $days_since_resumption = 180;
    // }
    if($days_since_resumption>360){ //grant old (1 year+) staff the max privileges
        $days_since_resumption = 360;
    }
    ////echo("<br/>days since resumption: ");//echo($days_since_resumption);
    
   
    //This algorithm works perfectly if the days since resumption is not factor or multiple of 30
    $upper_bound = ceil($days_since_resumption/30)*30;
    $lower_bound = floor($days_since_resumption/30)*30;
    $upper_bound -=1;
    
    //echo("<br/>days since resumption: ");//echo($days_since_resumption);
    //echo("<br/>Band before %30 code block");
    //echo("<br/>Band: " .  $lower_bound . " and ". $upper_bound);


    //This block of code takes care of the case where the days since resumption is a factor or multiple of 0
    if($days_since_resumption%30==0){
      $upper_bound = $days_since_resumption;
      $lower_bound = $days_since_resumption-29; //floor(($days_since_resumption-1)/30)*30;//$days_since_resumption;
    }
    
    //echo("<br/>Band AFTER %30 code block");
    //echo("<br/>Band: " .  $lower_bound . " and ". $upper_bound);
        
    //////var_dump('lower bound:' . $lower_bound);
    //////var_dump('upper bound:' . $upper_bound);
    // //////dd($upper_bound);
    if($days_since_resumption>=360){ //grant old (1 year+) staff the max privileges
        $lower_bound = $upper_bound = 360;
    }
    // //////////dd($days_since_resumption);
    $daysEntitledTo = LeaveEntitlement::
      where('leave_type','=',$leave_type->name)
      ->where('salary_grade', '=', $user->salary_grade)
      ->where('days_since_resumption', '>=', $lower_bound)
      ->where('days_since_resumption', '<=', $upper_bound)
      ->sum('days_allowed');
    
    if($leave_type->name =='Compassionate' or $leave_type->name =='Paternity' or $leave_type->name =='Maternity' or $leave_type->name =='Examination'){
      $daysEntitledTo = LeaveEntitlement::
          where('leave_type','=', $leave_type->name)
          ->where('salary_grade', '=', $user->salary_grade)
          ->max('days_allowed');
      // dd($daysEntitledTo);
    }
      // //echo("<br/>leave_type: ");//echo($leave_type->name);
      // //echo("<br/>salary_grade: ");//echo($user->salary_grade);
      // //echo("<br/>days entitled: ");//echo($daysEntitledTo);
      // }
    if($user->is_contract_staff){
          $daysEntitledTo = LeaveEntitlement::
            where('leave_type','=',$leave_type->name)
            ->where('salary_grade', '=', $user->salary_grade)
            ->max('days_allowed');
    }
      // //var_dump($leave_type->name);// == "Paternity"){
        // //var_dump('days entitled to: ' . $daysEntitledTo);
    //Theres a rule that the total number of 
    
    if(strtolower($leave_type->name) == "maternity"){
        $annualLeave = new LeaveType();
        $annualLeave->name = "Annual";
        $annualLeaveTaken = $this->queryLeaveApprovalsForDaysTaken($annualLeave, $user, $pointInTime);
        if(isset($annualLeaveTaken)){
          $daysEntitledTo = $daysEntitledTo - $annualLeaveTaken;
        }
    }     



    return $daysEntitledTo;
  }

   // THE START AND END variables are not in use
   // This function returns the number of leave days a user is entitled
   // ---->It does not deduct the days a user has taken<------
   // Parameter 1 : LeaveType e.g. Annual 
   // Parameter 2 : The User
   // Parameter 3 : The Point in time
   //-------------------------------------------------------------------------------------------------//
   public function getDaysEntitledToAsOfXPointInTimeBetween(LeaveType $leave_type, App\User $user, Carbon $start = null, Carbon $end = null, Carbon $pointInTime = null){
    //////var_dump($leave_type->name . '<br/>');

    $days_since_resumption = $this->calculateDaysSinceResumption($user, $pointInTime);
    //////var_dump('days since resumption:' . $days_since_resumption);
    // if($days_since_resumption<180){ //grant new staff the same privileges as 6 month olds
    //     $days_since_resumption = 180;
    // }
    if($days_since_resumption>360){ //grant old (1 year+) staff the max privileges
        $days_since_resumption = 360;
    }
    ////echo("<br/>days since resumption: ");//echo($days_since_resumption);
    
    //This algorithm works perfectly if the days since resumption is not factor or multiple of 30
    $upper_bound = ceil($days_since_resumption/30)*30;
    $lower_bound = floor($days_since_resumption/30)*30;
    $upper_bound -=1;
    
    //This block of code takes care of the case where the days since resumption is a factor or multiple of 0
    if($days_since_resumption%30==0){
      $upper_bound = $days_since_resumption;
      $lower_bound = $days_since_resumption-29; //floor(($days_since_resumption-1)/30)*30;//$days_since_resumption;
    }
    
    //////var_dump('lower bound:' . $lower_bound);
    //////var_dump('upper bound:' . $upper_bound);
    // //////dd($upper_bound);
    if($days_since_resumption>=360){ //grant old (1 year+) staff the max privileges
        $lower_bound = $upper_bound = 360;
    }
    // //////////dd($days_since_resumption);
    $daysEntitledTo = LeaveEntitlement::
      where('leave_type','=',$leave_type->name)
      ->where('salary_grade', '=', $user->salary_grade)
      ->where('days_since_resumption', '>=', $lower_bound)
      ->where('days_since_resumption', '<=', $upper_bound)
      ->sum('days_allowed');
    
    if($leave_type->name =='Compassionate' or $leave_type->name =='Paternity' or $leave_type->name =='Maternity' or $leave_type->name =='Examination'){
      $daysEntitledTo = LeaveEntitlement::
          where('leave_type','=', $leave_type->name)
          ->where('salary_grade', '=', $user->salary_grade)
          ->max('days_allowed');
      // dd($daysEntitledTo);
    }
      // //echo("<br/>leave_type: ");//echo($leave_type->name);
      // //echo("<br/>salary_grade: ");//echo($user->salary_grade);
      // //echo("<br/>days entitled: ");//echo($daysEntitledTo);
      // }
    if($user->is_contract_staff){
          $daysEntitledTo = LeaveEntitlement::
            where('leave_type','=',$leave_type->name)
            ->where('salary_grade', '=', $user->salary_grade)
            ->max('days_allowed');
    }
      // //var_dump($leave_type->name);// == "Paternity"){
        // //var_dump('days entitled to: ' . $daysEntitledTo);
    //Theres a rule that the total number of 
    
    if(strtolower($leave_type->name) == "maternity"){
        $annualLeave = new LeaveType();
        $annualLeave->name = "Annual";
        $annualLeaveTaken = $this->queryLeaveApprovalsForDaysTaken($annualLeave, $user, $pointInTime);
        if(isset($annualLeaveTaken)){
          $daysEntitledTo = $daysEntitledTo - $annualLeaveTaken;
        }
    }     



    return $daysEntitledTo;
  }

  // -------------------------------------------------------------------------------- //
  // This function deducts the total annual leave taken from the maternity leave type
  // Not in use
  // -------------------------------------------------------------------------------- //
  public function removeAnnualLeaveTakenFromMaternityLeave(App\LeaveType $leave_type, $user, $pointInTime){
    if(strtolower($leave_type->name) == "maternity"){
      $annualLeave = new LeaveType();
      $annualLeave->name = "Annual";
      $annualLeaveTaken = $this->queryLeaveApprovalsForDaysTaken($annualLeave, $user, $pointInTime);
      if(isset($annualLeaveTaken)){
        $daysEntitledTo = $daysEntitledTo - $annualLeaveTaken;
      }
  }
  }

  //September 26, 2017 - You should be able to cancel a leave provided you do so before the commencement
  //NOT IN USE YET
  public function cancel(Request $request, $id){
      $application = LeaveRequest::find($id);

      if($application == null){
        Session::flash('flash_message', 'Request not found, cancellation failed!');
        return redirect()->back();
      }

      $this->validate($request, array(
              'reason' => 'bail|required'));      

      $now = Carbon::now();
      //Check to see if the start date has already come
      if($now->diffInDays($application->start_date)>0){
          $application->cancelled =  true;
          $application->date_cancelled = $now;
          $application->cancellation_reason = $request['reason'];
          $application->save();

          $approval = LeaveApproval::where('leave_request_id', $application->id)->first();

          if(isset($approval)){
            $approval->delete();
          }
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
      $contract_start_date = Carbon::parse($user->resumption_date);
      
      if($user->is_contract_staff or $contract_start_date->diffInDays(Carbon::now())<365){
        //   ////////////dd($contract_start_date);
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
          // $d = $contract_start_date->diffInDays($current_year)>365;
          // //////////dd($d);
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
      // dd($credit);
      return $credit;
  }

  public function getRecallCreditBetween($user, App\LeaveType $leave_type, $year_start, $year_end){

    if(!isset($user)){
        return 0;        
    }

    if(!isset($year_start)){
      return 0;        
    }

    if(!isset($year_end)){
      return 0;
    }
    $year = Carbon::now();
    
    $credit =  0;//Recall::where('applicant_username', $user->username)->where('supervisor_response', '=', true)->whereYear('')
    $contract_start_date = Carbon::parse($user->resumption_date);
    
    if($user->is_contract_staff or $contract_start_date->diffInDays(Carbon::now())<365){
      //   ////////////dd($contract_start_date);
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
        // $d = $contract_start_date->diffInDays($current_year)>365;
        // //////////dd($d);
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
    // dd($credit);
    return $credit;
}

  public function approve(Request $request, $id)
  {
      $live = LeaveRequest::find($id);

      if($live == null){
        Session::flash('flash_message', 'Request not found, approval failed!');
        return redirect()->back();
      }

      if($live->supervisor_response == true){
          Session::flash('flash_message', 'Already approved!');
          return redirect()->back();
      }


      if(strtolower($live->supervisor_username) != strtolower(Auth::user()->username)){
        Session::flash('flash_message', 'You cannot approve this request!');
        return redirect()->back();
      }
      else{
        $current = Carbon::now();
        $live -> supervisor_response_date = $current;
        $live -> supervisor_response = true;
        $live -> supervisor_username = Auth::user()->username;
        $live -> updated_at = $current;
        $live->save();
      }
      Session::flash('flash_message', 'Request approved successfully!');

      $applicant = $this->getUser($live->created_by);
      $approver = $this->getUser($live->supervisor_username);
      try{
          //Commented out to avaoid numerous notifications to applicant //Aug 30, 2017
        Mail::setSwiftMailer($this->getSwiftMailer());
        Mail::to($applicant->email(), $applicant->name)->send(new SupervisorApproved($live, $applicant, $approver));
        }catch(\Exception $ex){
	           Session::flash("flash_message","Your request was processed successfully, but the email notifications failed");
        }
        try{
            //Commented out to avaoid numerous notifications to supervisor //Aug 30, 2017

        //  Mail::setSwiftMailer($this->getSwiftMailer());
        //  Mail::to($approver->email(), $approver->name)->send(new SupervisorYouJustApproved($live, $applicant, $approver));
        //  Mail::to($request->user()->email(), $request->user()->name)
        // ->send(new NewHRPleaseApproveRequest($live, $applicant, $approver));
        }catch(\Exception $ex){
	           Session::flash("flash_message","Your request was processed successfully, but the email notifications failed");
          //////////////dd($ex);
        }
    //   return redirect()->back();
        return $this->hrApprove($request, $id);
  }

  public function deny(Request $request, $id)
  {
      $input = LeaveRequest::find($id);

      if($input == null){
        Session::flash('flash_message', 'Request not found, decline failed!');
        return redirect()->back('flash_message', 'Request not found, decline failed!');
      }

      if($input -> supervisor_response===false){
          Session::flash('flash_message', 'Request not found, decline failed!');
          return redirect()->back()->with('flash_message', 'Request not found, decline failed!');
      }

      if(strtolower($input->supervisor_username) != strtolower(Auth::user()->username)){
        //echo(strtolower($input->supervisor_username));
        //echo(strtolower(Auth::user()->username));
        // ////////////dd($id);
        return redirect()->back()->with('flash_message', 'You are not in a position to decline this request!');
      }
      else{
        $current = Carbon::now();
        $input -> supervisor_response=false;
        $input -> supervisor_response_date = $current;//->toDateTimeString();
        // if(array_key_exists('reason', $request))
        // {
        //   $input -> supervisor_response_reason = $request->reason;
        // }
        $key = "supervisor-deny-reason-" . $id;
        $value = $request[$key];
        $input -> supervisor_response_reason = $value;
        $input -> supervisor_username = Auth::user()->username;
        $input -> updated_at = $current;
        $input->save();
        Session::flash('flash_message', 'Request denial was successful!');
        $message = 'Request denial was successful!';

        $applicant = App\User::where('username', '=', $input->created_by)->first();
        $approver = App\User::where('username', '=', $input->supervisor_username)->first();
        try{
          Mail::setSwiftMailer($this->getSwiftMailer());
          $cc = $this->getHREmailAddresses();
          array_push($cc, $request->user()->email());//cc hr and the boss who declined
          Mail::to($applicant->email(), $applicant->name)->cc($cc)
          ->send(new SupervisorDenied($input, $applicant, $approver));
        }catch(\Exception $ex){
	           Session::flash("flash_message","Your request was processed successfully, but the email notifications failed");
               $message = $message . "Your request was processed successfully, but the email notifications failed";
        }
      }
      return redirect()->back()->with('flash_message', $message);
  }


  public function declineStandInRequest(Request $request, $id){

      //////////////dd($request);
      $input = LeaveRequest::find($id);
    //   ////////////dd($input);
      if($input == null){
        Session::flash('flash_message', 'Request not found, decline failed!');
        return redirect()->back();
      }

      if($input -> stand_in_response===false){
        //   ////////////dd($input);
          Session::flash('flash_message', 'Request was already declined!');
          return redirect()->back();
      }

    //   ////////////dd($input);
      if(strtolower($input->stand_in_username) != strtolower(Auth::user()->username)){
        Session::flash('flash_message', 'You cannot stand in for this request!');
        return redirect()->back();
      }
      else{
        $current = Carbon::now();
        $input -> stand_in_response=false;
        $input -> stand_in_response_date = $current;//->toDateTimeString();
        $key = "decline-stand-in-request-reason-" . $id;
        $value = $request[$key];
        $input -> stand_in_response_reason = $value;
        //////////////dd($input->stand_in_response_reason);
        $input -> stand_in_username = Auth::user()->username;
        $input -> updated_at = $current;
        $input->save();
        $message = 'Your stand in refusal was successful!';
        Session::flash('flash_message', 'Your stand in refusal was successful!');

        $applicant = $this->getUser($input->created_by);
        $approver = $this->getUser($input->stand_in_username);
        try{
          Mail::setSwiftMailer($this->getSwiftMailer());
          $cc = $this->getHREmailAddresses();
          array_push($cc, $request->user()->email());//cc hr and the friend who declined
          Mail::to($applicant->email(), $applicant->name)->cc($cc)
          ->send(new StandInDeclined($input, $applicant, $approver));
        }catch(\Exception $ex){
	           Session::flash("flash_message","Your request was processed successfully, but the email notifications failed");
               $message = $message . "Your request was processed successfully, but the email notifications failed";
            //    ////////////dd($ex);
        }
      }
      return redirect()->back();//->with('flash_message', $message);
  }

    public function hrDeny(Request $request, $id){
    // ////////////dd($id);

      $input = LeaveRequest::find($id);

      if($input == null){
        Session::flash('flash_message', 'Request not found, denial failed!');
        return redirect()->back();
      }

      if($input -> hr_response===false){
          Session::flash('flash_message', 'Request not found, denial failed!');
          return redirect()->back();
      }


      if(!Auth::user()->hasRole("HR")){
        Session::flash('flash_message', 'You cannot deny this request!');
      }
      else{
        $current = Carbon::now();
        $input -> hr_response=false;
        $input -> hr_response_date = $current;//->toDateTimeString();
        // if(array_key_exists('reason', $request))
        // {
        //   $input -> hr_response_reason = $request->reason;
        // }
        $key = "hr-deny-reason-" . $id;
        $value = $request[$key];
        $input -> hr_response_reason = $value;
        $input -> hr_username = Auth::user()->username;
        $input -> updated_at = $current;
        $input->save();
        Session::flash('flash_message', 'Your denail was successful!');
        $applicant = $this->getUser($input->created_by);
        $approver = $this->getUser($input->hr_username);
        $supervisor = $this->getUser($input->supervisor_username);
        try{
          Mail::setSwiftMailer($this->getSwiftMailer());
          $cc = $this->getHREmailAddresses();
          array_push($cc, $request->user()->email());//cc hr and the hr who declined
          array_push($cc, $supervisor->user()->email()); //cc the boss who approved
          Mail::to($request->user()->email(), $request->user()->name)->cc($cc)
          ->send(new HRDenied($input, $applicant, $approver));
        }catch(\Exception $ex){
	           Session::flash("flash_message","Your request was processed successfully, but the email notifications failed");

        }
      }
      return redirect()->back();
  }

  public function gatherStats(){

    $stats = [];

    $current = Carbon::now();
    $applications = new Stat();
    //applications
    $applications->type = "Applications";
    $applications->count = LeaveRequest::where('id','>',0)->count();
    array_push($stats, $applications);

    //approvals
    $approvals = new Stat();
    $approvals->type = "Approvals";
    $approvals->count = LeaveApproval::where('leave_request_id', '>', 0)->where('id','>', 0)->count();
    array_push($stats, $approvals);

    //pending approvals
    $pendingApproval = new Stat();
    $pendingApproval->type = "Awaiting Approval";
    $pendingApproval->count = LeaveRequest::where('stand_in_response', '=', null)
    ->count();
    $pendingApproval->count += LeaveRequest::where('supervisor_response', '=', null)->where('stand_in_response', '=', true)
    ->count();
    $pendingApproval->count += LeaveRequest::where('hr_response', null)->where('supervisor_response', true)
    ->count();
    array_push($stats, $pendingApproval);

    //denial
    $hrdenials = new Stat();
    $hrdenials->type = "Declines by HR";
    $hrdenials->count = LeaveRequest::where('hr_response', false)
                                    ->where('supervisor_response', true)
                                    ->count();
    array_push($stats, $hrdenials);

    //denial_by_standin
    $denial_by_standin = new Stat();
    $denial_by_standin->type = "Declines by Stand-in";
    $denial_by_standin->count = LeaveRequest::where('stand_in_response', false)->count();
    array_push($stats, $denial_by_standin);

    //denial_by_supervisor
    $denial_by_supervisor = new Stat();
    $denial_by_supervisor->type = "Declines by Supervisor";
    $denial_by_supervisor->count = LeaveRequest::where('stand_in_response', true)->where('supervisor_response', false)->count();
    array_push($stats, $denial_by_supervisor);

    //those who have not applied this year
    // $yettoapply = new Stat();
    // $yettoapply->type = "Yet to apply";
    //
    // $users = User::select('id','username')->where('id', '>', 0)->get();
    //
    // for ($i=0; $i < sizeof($users); $i++) {
    //   $user = $users[$i];
    //
    //   $ca = LeaveRequest::where('created_by', '=', $user->userame)
    //                         ->whereYear('created_at', $current->year)
    //                         ->count();
    //
    //   if($ca ==0){
    //     $yettoapply->count++;
    //   }
    // }
    // array_push($stats, $yettoapply);
    return $stats;
    //return array($applications, $approvals, $yettoapply);
  }

  public function gatherStatsByYear($year){
    
        $stats = [];
        $current = $year;
        if($current==null){
          $current = Carbon::now();
        }
        $applications = new Stat();
        //applications
        $applications->type = "Applications";
        $applications->count = LeaveRequest::where('id','>',0)->whereYear('created_at', $current->year)->count();
        array_push($stats, $applications);
    
        //approvals
        $approvals = new Stat();
        $approvals->type = "Approvals";
        $approvals->count = LeaveApproval::where('leave_request_id', '>', 0)->where('id','>', 0)->whereYear('created_at', $current->year)->count();
        array_push($stats, $approvals);
    
        //pending approvals
        $pendingApproval = new Stat();
        $pendingApproval->type = "Awaiting Approval";
        $pendingApproval->count = LeaveRequest::where('stand_in_response', '=', null)
        ->whereYear('created_at', $current->year)->count();
        $pendingApproval->count += LeaveRequest::where('supervisor_response', '=', null)->where('stand_in_response', '=', true)
        ->whereYear('created_at', $current->year)->count();
        $pendingApproval->count += LeaveRequest::where('hr_response', null)->where('supervisor_response', true)
        ->whereYear('created_at', $current->year)->count();
        array_push($stats, $pendingApproval);
    
        //denial
        $hrdenials = new Stat();
        $hrdenials->type = "Declines by HR";
        $hrdenials->count = LeaveRequest::where('hr_response', false)
                                        ->where('supervisor_response', true)
                                        ->whereYear('created_at', $current->year)->count();
        array_push($stats, $hrdenials);
    
        //denial_by_standin
        $denial_by_standin = new Stat();
        $denial_by_standin->type = "Declines by Stand-in";
        $denial_by_standin->count = LeaveRequest::where('stand_in_response', false)->whereYear('created_at', $current->year)->count();
        array_push($stats, $denial_by_standin);
    
        //denial_by_supervisor
        $denial_by_supervisor = new Stat();
        $denial_by_supervisor->type = "Declines by Supervisor";
        $denial_by_supervisor->count = LeaveRequest::where('stand_in_response', true)->where('supervisor_response', false)->whereYear('created_at', $current->year)->count();
        array_push($stats, $denial_by_supervisor);
    
        //those who have not applied this year
        // $yettoapply = new Stat();
        // $yettoapply->type = "Yet to apply";
        //
        // $users = User::select('id','username')->where('id', '>', 0)->get();
        //
        // for ($i=0; $i < sizeof($users); $i++) {
        //   $user = $users[$i];
        //
        //   $ca = LeaveRequest::where('created_by', '=', $user->userame)
        //                         ->whereYear('created_at', $current->year)
        //                         ->count();
        //
        //   if($ca ==0){
        //     $yettoapply->count++;
        //   }
        // }
        // array_push($stats, $yettoapply);
        return $stats;
        //return array($applications, $approvals, $yettoapply);
      }

  public function getHREmailAddresses($applicant=null){
    $members = $this->getRoleMembers('HR');
    $emails = [];
    $role = null;
    $users = null;
    if(isset($members) == false){
      return $emails;
    }
    for ($i=0; $i < sizeof($members); $i++) {
      $role = $members[0];
      $users = $role->users;
    }
      //////////////dd($role->users);

    for ($b=0; $b < sizeof($users); $b++) {
        $user = $users[$b];
        if(isset($applicant)){
          if(strtolower($user->region)==strtolower($applicant->region) or strtolower($user->region)==strtolower('Abuja') or strtolower($user->region) == strtolower('Head Office')){
              array_push($emails, $user->email());
          }
        }
    }
    return $emails;
  }

  public function view(Request $request){
    $title = "Request Details";
    $leave =  null;
    if($request->has('applicationId') == false){
      Session::flash("flash_message", "Your request lacked an application Id");
    }
    $id = $request->input('applicationId');
    $applicant = new \App\User();
    if(isset($id)){
      $leave = LeaveRequest::where('id', $id)->first();
      // ////////////dd($id);
      if(!isset($leave)){
        $title ="Leave Request # " . $id . " no longer exists. This typically happens if the applicant deleted the request.";
        Session::flash("flash_message", $title);
        $leave = new LeaveRequest();
      }
      if(isset($leave)){
        // $user = Auth::user();
        // if(
        //   strtolower($leave->supervisor_username)!=strtolower($user->username)
        //   && strtolower($leave->stand_in_username)!=strtolower($user->username)
        //   && strtolower($leave->created_by)!=strtolower($user->username)
        //   && $user->hasRole("HR") == false)
        // {
        //   Session::flash("flash_message", "You are not authorized to view leave request # " . $id);
        //   $leave = new LeaveRequest();
        // }
        $applicant = $user = User::where('username', '=', $leave->created_by)->first();
        
        //Fix a strange situation where the applicant name returns empty
        if(!isset($leave->name) or $leave->name === "" or $leave->name === " "){
          if(isset($applicant)){
            $leave->name = $applicant->getName();
            try{
              $leave->save();
            }
            catch(\Exception $e){}
          }
        }

        //Fix the anomally whereby the days left goes into the negative when it shouldnt
        try{
            if(isset($applicant)){
              $this->fixLeaveDaysLeft($applicant);
            }
        }catch(\Exception $ex){

        }
      }
    }

    $documents = Document::where('leave_request_id', $leave->id)->get();
    return View('leaverequests.view',
                    [
                      'title' => $title,
                      'leavereq' => $leave,
                      'documents' => $documents,
                      'applicant' => $applicant
                    ]);
  }

  public function fixFailureToUpdateDaysLeftUrl(Request $request){
    if($request->has('user')){
      $user = User::where('username',$request->input('user'))->first();
      if(isset($user)){
          try{
            $this->fixLeaveDaysLeft($user);
            return "Done";
          }catch(\Exception $ex){
            return $ex->getMessage();
              ////////var_dump($ex);
          }
      }
    }
  }

  //For some unknown reason, the code that was supposed to update the leave days left for un approved applications wasnt working
  //The function below is designed to correct that anomally
  //----------------------------------------------------------------------------------------------------------------------//
  //  This is the most important function in the entire leave application at this moment //Jan 1, 2018
  //  It ensures that the balance reporting in the leave application list makes sense and respects the rules set by HR
  //-----------------------------------------------------------------------------------------------------------------------
  public function fixLeaveDaysLeft($user){
      
    $years = [];
    
    $startYear = 2017;
    $maxYear = Carbon::now()->year;
    $difference = $maxYear - $startYear;
    // dd($years);
    
    for ($i=0; $i <= $difference; $i++) { 
      $years[$i] = Carbon::create($startYear, 1, 1);
      $startYear++;
    }
    // dd($years);
    foreach($years as $year){
          //var_dump("Current Year: " . $year);
          // dd($year);
          //Get all approvals for the year in question, order them from first to last, group by leave type
          $applications = LeaveRequest::where('created_by', $user->username)
          ->whereYear('created_at', $year->year) //->whereYear('created_at', $current->toDateString()) 
          // ->orderBy('supervisor_response')
          ->orderBy('id', 'asc')->get()->groupBy('leave_type');

          try{
              //loop through the groups 
              foreach($applications as $current_leave_type_group){

                  //Keep track of total days taken so far for each leave type
                  $daysApprovedSoFar =0;
                  //This var was created today Jan 8, 2017 to ensure that the days left per request maketh sense
                  //It is used to determine the proper days left for the next request vis a vis the date of the last approval
                  //This bug took several days to solve
                  $lastApproval =  null; 

                  //loop through the applications 
                  for($z=0; $z < sizeOf($current_leave_type_group); $z++){

                    //var_dump("Current index: " . $z . "<br/>");
                    
                      //get the current application
                      $current_leave_req = $current_leave_type_group[$z];

                      if($z==0){ 
                        //Theres special code that needs to run if its the first application for this leave type
                        //Basically the unique thing is that if tnis is the first application of this leave type,
                        //then the days left is the total days entitled to
                        //Thats all
                          try{
                              //get the leave type by name
                              $leaveType = LeaveType::where('name', $current_leave_req->leave_type)->first();

                              //find out the correct balance the user should have for that leave type as at the time of the application
                              $current_leave_req_date = Carbon::parse($current_leave_req->date_submitted);
                              $correct_leave_days_left = $this->getDaysEntitledToAsOfXPointInTime($leaveType, $user, Carbon::parse($current_leave_req->date_submitted));
                              // $days_carried_over = $this->getDaysCarriedOver($year, $user);
                              // if($days_carried_over==10){
                                // //echo("Correct Days left = " . $correct_leave_days_left . "<br/>");
                                // //echo("Days Carried over = " . $days_carried_over . "<br/>");
                                // //echo("User = " . $user->username . "<br/>");
                                // //echo("Year = " . $year . "<br/>");
                                // die();
                              // }
                              $correct_leave_days_left += $days_carried_over;
                              $current_leave_req->days_left = $correct_leave_days_left;
                              //var_dump('$current_leave_req->leave_type: '.$current_leave_req->leave_type . '<br/>');
                              //var_dump('$current_leave_req->days_requested: '.$current_leave_req->days_requested. '<br/>');
                              //var_dump('$current_leave_req->days_left: '.$current_leave_req->days_left. '<br/>');
                              //var_dump('$correct_leave_days_left: '.$correct_leave_days_left. '<br/>');
                              $current_leave_req->save();
                          }
                          catch(\Exception $ddd){
                            // var_dump($ddd);
                          }
                      }
                      $next_index = $z+1; //Im still wondering why you bother for a next index
                      //I guess theres something special about the second leave application of a give type
                      //Yes we are trying to correct a problem 
                      //var_dump("Next index: " . $next_index . "<br/>");
                      if($next_index<sizeOf($current_leave_type_group))
                      { //check to preven index out of bounds exception
                          $next_leave_req = $current_leave_type_group[$next_index];
                                
                          //if the next request days left is less than or greater than the previous
                          //Reset the next applications days left to fix a bug that occured some time ago else this is unneeded
                          if($next_leave_req->days_left <> $current_leave_req->days_left){
                            //set it to the previous applications days left to achieve a clean slate
                            $next_leave_req->days_left = $current_leave_req->days_left; 
                            $next_leave_req->save();                  
                          }
                          if($current_leave_req->days_left == $next_leave_req->days_left){ //the problem
                              //Dec 6, 2017 @ 2:47pm, this is a problem only if the current request is approved
                              //In whicch case the next request days left should reduce by the days approved
                              if($current_leave_req->supervisor_response == true)
                              { 
                                  //if the current request was approved, then the next should decrement, 
                                  //***This is the bug I''m fighting -> this condition is critical IF the user did not gain days in the meantimee
                                  
                                  //get the approval for the current, so we can ddecrement the blance of the next accordingly
                                  $approval = LeaveApproval::where('leave_request_id', '=', $current_leave_req->id)->first();
                                  //var_dump("Approval found: " . $approval!=null);
                                  
                                  if(isset($approval)){
                                    $lastApproval = $approval;
                                        // //echo($next_leave_req->id);
                                        // //echo($current_leave_req->id);

                                        //now get the leave days left at the time when you submitted the next request
                                        //Dec 6, 2017, just fixed a bug where it used entitlement instead of balance
                                        // $correct_leave_days_left = $this->getDaysEntitledToAsOfXPointInTime($leaveType, $user, Carbon::parse($next_leave_req->date_submitted));
                                        $correct_leave_days_left = $this->getDaysLeftByLeaveType($leaveType, $user, Carbon::parse($next_leave_req->created_at), Carbon::parse($approval->created_at));

                                        //if current applications days left == previous applications days left and yet previous application was approved
                                        //
                                        //current application days left -= previous applications days approved

                                        
                                        // //////dd($correct_leave_days_left);
                                        //you can get credit if you are a new staff, you get extra days every month until u clock 1 year
                                        // $increment = $correct_leave_days_left - $current_leave_req->days_left;
                                        //var_dump("The current leave request was APPROVED, therefore I'm updating the days left for the next request below ... <br/>");
                                        //var_dump('$correct_leave_days_left SHOULD BE: '.$correct_leave_days_left. '<br/>');
                                  
                                        //var_dump('$next_leave_req->leave_type: '.$next_leave_req->leave_type . '<br/>');
                                        //var_dump('$next_leave_req->days_requested: '.$next_leave_req->days_requested. '<br/>');
                                        //var_dump('$next_leave_req->days_left (before correction): '.$next_leave_req->days_left. '<br/>');
                                        $next_leave_req->days_left = $correct_leave_days_left;//$current_leave_req->days_left;
                                        
                                        //Because im not calling getDaysLeftByLeaveType, i dont need to keep track of total approvals
                                        //That is why im commenting out the line below dec 6, 2017 @ 2:56pm
                                        // $next_leave_req->days_left -= $daysApprovedSoFar;//$approval->days;
                                        $next_leave_req->increment = 0;//$next_leave_req->days_left - $current_leave_req->days_left;
                                        $next_leave_req->save();
                                        //var_dump('$next_leave_req->days_left (AFTER correction): '.$next_leave_req->days_left. '<br/>');
                                        
                                  }else{
                                    //I am thinking I should //Jan 9, 2018 @ 11:11am
                                    //create the approval, but dont fire emails
                                    //ensure you use the date of the supervisors approval
                                  }
                              }
                              else{
                                  //since current request was not approved,
                                  //let undo the reset
                                  // dd($z);
                                  //var_dump("The current leave request was not approved, undoing the reset done to its successor below ...? <br/>");
                                  //var_dump('$next_leave_req->leave_type: '.$next_leave_req->leave_type . '<br/>');
                                  //var_dump('$next_leave_req->days_requested: '.$next_leave_req->days_requested. '<br/>');
                                  //var_dump('$next_leave_req->days_left: '.$next_leave_req->days_left. '<br/>');
                                  if($lastApproval == null){
                                    $lastApproval = $next_leave_req;
                                  }
                                  $correct_leave_days_left = $this->getDaysLeftByLeaveType($leaveType, $user, Carbon::parse($next_leave_req->created_at), Carbon::parse($lastApproval->created_at));
                                  // $correct_leave_days_left = $this->getDaysEntitledToAsOfXPointInTime($leaveType, $user, Carbon::parse($next_leave_req->date_submitted));//$this->getDaysLeftByLeaveType($leaveType, $user, Carbon::parse($next_leave_req->date_submitted));
                                  
                                  $next_leave_req->days_left = $correct_leave_days_left;
                                  $next_leave_req->save();
                                  //var_dump('$correct_leave_days_left: '.$correct_leave_days_left. '<br/>');
                                  //var_dump("Reset completed!<br/>");

                                  //also since it wasnt approved, lets set its days left to the current days left
                                  $correct_leave_days_left = $this->getDaysLeftByLeaveType($leaveType, $user, $year); 
                                  $current_leave_req->days_left = $correct_leave_days_left;
                                  $current_leave_req->save();
                              }
                          }
                          else{
                            //if we are not dealing with application #1 of the current leave type group
                            //then get the balance back to where it should be
                            //if the current request is not approved
                            if($z>0){
                              //var_dump("Current Index: " . $z . "if we are not dealing with application #1 of the current leave type group");
                              //var_dump("We are in the if block for app: " . $z);
                              //var_dump('$next_leave_req->leave_type: '.$next_leave_req->leave_type . '<br/>');
                              //var_dump('$next_leave_req->days_requested: '.$next_leave_req->days_requested. '<br/>');
                              //var_dump('$next_leave_req->days_left: '.$next_leave_req->days_left. '<br/>');
                              if($lastApproval == null){
                                $lastApproval = $next_leave_req;
                              }
                              $correct_leave_days_left = $this->getDaysLeftByLeaveType($leaveType, $user, Carbon::parse($next_leave_req->created_at),Carbon::parse($lastApproval->created_at));
                              $correct_leave_days_left += $days_carried_over;
                              $current_leave_req->days_left = $correct_leave_days_left;
                              $current_leave_req->save();
                              //var_dump('$correct_leave_days_left: '.$correct_leave_days_left. '<br/>');
                            }
                          }
                      }

                      //Ensure that all applications pending approval reflect the current balance
                      if($current_leave_req->supervisor_response == null){
                        // $correct_leave_days_left = $this->getDaysLeftByLeaveType($leaveType, $user); getLeaveBalanceLeftByLeaveType
                        $correct_leave_days_left = $this->getLeaveBalanceLeftByLeaveType($leaveType, $user); 
                        $correct_leave_days_left += $days_carried_over;
                        $current_leave_req->days_left = $correct_leave_days_left;
                        $current_leave_req->save();
                      }
                  }
              }
          }
          catch(\Exception $ex){
            //var_dump("<br/>" . $ex . "<br/>");
          }
        $previous_year = $year;
    }
  }

  public function updateLeaveDaysLeft($pendingApproval, $approval){
      // //////////dd($pendingApproval);
      try{
          for ($i=0; $i < sizeOf($pendingApproval); $i++) {
              $current_leave_req = $pendingApproval[$i];
              if($current_leave_req->leave_type == $approval->leave_type){
                //remove the days approved for each of that 
                $current_leave_req-> days_left -= $approval->days; // $this->getDaysLeftByLeaveType($leave_type, Auth::user(), Carbon::now());
                $current_leave_req->save();
              }
          }
      } catch( \Exception $ex){
        // //////////dd($ex);
      }
      return $pendingApproval;
  }

  public function home(Request $request)
  {
    $msg="";
    if(Session::has('first_login')){
      $msg = "10 days carry over has been restricted to users who applied online";
      if(Auth::user()->resumption_date == null or Auth::user()->salary_grade == null){
        $msg = " Please contact Mr. Ramalan of HR to update your employment date and annual leave entitlement. His email is suleiman.ramalan@abujaelectricity.com.";
      }
    }
    Session::flash('flash_message', $msg );
    $user = Auth::user();
    // if($request->has('username')){
    //   $user = User::where('username', $request->input($username));
    // }
    try{
      $this->fixLeaveDaysLeft($user);
      echo("<br/>Fix performed<br/>");
    }catch(\Exception $ex){
      // dd($ex);
    }

    // if($user->section == "Default"){
    //   return redirect("/complete-your-profile");
    // }

    // using paginate function to show 3 actions items per page
    $ROWS_PER_TABLE = 10;


    //hack add test account hr@abujaelectricity.com into hr approver role


      $requests = LeaveRequest::where('created_by', $user->username)
        ->orderBy('created_at', 'desc')->get();

        $today = Carbon::now();

        $pendingApproval = [];
        // ////////////dd($request->user()->username);
      $pendingApproval = LeaveRequest::where('stand_in_username', $user->username)
        // ->where('created_by', '<>', $request->user()->username)
        // ->where('hr_response','=', null)
        ->where('stand_in_response', '=', null)
        ->orderBy('created_at', 'desc')->get();


      //$pendingApproval = $this->updateLeaveDaysLeft($pendingApproval);

      $pendingSApproval = LeaveRequest::where('supervisor_username', $user->username)
                //->where('created_by', '<>', $request->user()->username)
                ->where('stand_in_response', '=', true)
                ->where('supervisor_response','=', null)
                // ->whereYear('created_at', $today->year)
                ->orderBy('created_at', 'desc')->get();

                      $pendingHRApproval = [];       
                      if($user->hasRole("HR")){         
                        $pendingHRApproval = LeaveRequest::where('supervisor_response', '=', true)         
                        ->where('stand_in_response', '=', true)         
                        ->where('hr_response','=', null)         
                        // ->whereYear('created_at', $today->year)        
                         ->orderBy('supervisor_response_date', 'desc')->get();
                      }

      $leaveTypes = $this->getLeaveBalanceForUser($user); //$this->getLeaveBalanceForUser($user);
      //dd($leaveTypes);
      
      $leaveTypes = $this->resetLeaveBalanceForUnavailableLeaveTypes($leaveTypes, $user);
      //////////////dd($leaveTypes);
      //////////////dd($requests);


      $notification = null;
      $hrnotification = null;
      if($user->salary_grade==false || $user->salary_grade==null){
        $notification = "For accurate leave balance, please ";
      }

      try{
        $this->createHRRole();
      }catch(\Exception $e){}

      if($user->hasRole('HR')){
        $no = $this->getNumberOfUsersWithIncompleteProfile();
        $suffix = "profile is ";
        if($no>1){
          $suffix = "profiles are ";
        }
        $hrnotification =  $no  . " user " . $suffix . "incomplete. Please treat. Thank you!";
        if($no<1){
          $hrnotification ="";
        }
      }

      $stats = $this->gatherStats();

      $countMyApplications =0;
      $countPending = 0;
      // $countPendingStandin=0;
      // $countPendingSupervisor=0;
      // $countPendingHR=0;
      $offline_approvals = LeaveApproval::where('leave_request_id', '=', 0)->where('applicant_username', $user->username)->get();
      try{
        $countMyApplications = sizeof($requests);
        $countPending = sizeof($pendingApproval) + sizeof($pendingSApproval) + sizeof($pendingHRApproval);
        $countPendingStandin = sizeof($pendingApproval);
        $countPendingSupervisor = sizeof($pendingSApproval);
        $countPendingHR =sizeof($pendingHRApproval);
      }catch(\Exception $e){}
      
      $refundApplications = [];
      $refundsPendingApproval = [];
      try{
        $refundApplications = Recall::where('applicant_username', $user->username)->get();
        $refundsPendingApproval = Recall::where('supervisor_username', $user->username)->where('supervisor_response','=', null)->get();
      }catch(\Exception $ex){

      }
      return view('leaverequests.index',
        [
          'refundsPendingApprovalTitle' => 'Leave refund applications for your attention',
          'countMyRefundApplications' => sizeOf($refundApplications),
          'countRefundsPendingApproval' => sizeOf($refundsPendingApproval),
          'refundApplications' => $refundApplications,
          'refundsPendingApproval' => $refundsPendingApproval,
          'offline_approvals' => $offline_approvals,
          'leaverequests' => $requests,
          'pendingApproval' => $pendingApproval,
          'pendingSApproval' => $pendingSApproval,
          'pendingHRApproval' => null,
          'mytitle' => 'Leave applications',
          'refundTitle' => 'Leave refund applications',
          'pendingTitle' => 'For my attention',
          'leavetypes' => $leaveTypes,
          'totalBalance' => 0,
          'notification' => $notification,
          'hrnotification' => $hrnotification,
          'stats' => $stats,
          'countMyApplications' => $countMyApplications,
          'countPending' => $countPending,
          'countPendingStandin' => $countPendingStandin,
          'countPendingSupervisor' => $countPendingSupervisor,
          'countPendingHR' => $countPendingHR
        ]
      );
  }
 
  public function getLeaveBalanceForUser($user){
      $leaveTypes = LeaveType::where('gender', "")
      ->where('name', '!=', 'Casual')
      ->orWhere('gender', null)
      ->orWhere('gender', $user->gender)
      ->get();

      $leave_balances = [];

      $totalBalance = 0;
      for ($i=0; $i < sizeof($leaveTypes); $i++) {

        $current =$leaveTypes[$i];

        //remove casual leave
        if($current->name=='Casual'){
                 unset($leaveTypes[$i]);
                 continue;
        }                
        ////////////var_dump($current);
        $current->balance = $this->getLeaveBalanceLeftByLeaveType($current, $user);//getDaysLeftByLeaveType($current);

        $totalBalance += $current->balance;

        $leave_type_view_model = new App\LeaveTypeViewModel();
        $leave_type_view_model->id = $current->id;
        $leave_type_view_model->name = $current->name;
        $leave_type_view_model->balance = $current->balance;

        $leave_balances[$i] = $leave_type_view_model;
      }

      return $leave_balances; //$leaveTypes;
  }

  public function getLeaveDaysLeft($user){
    $leaveTypes = LeaveType::where('gender', "")
    ->where('name', '!=', 'Casual')
    ->orWhere('gender', null)
    ->orWhere('gender', $user->gender)
    ->get();

    $totalBalance = 0;
    for ($i=0; $i < sizeof($leaveTypes); $i++) {

      $current =$leaveTypes[$i];

      //remove casual leave
      if($current->name=='Casual'){
               unset($leaveTypes[$i]);
               continue;
      }                
      ////////////var_dump($current);
      $current->balance = $this->getDaysLeftByLeaveType($current);
      $totalBalance += $current->balance;
    }
    return $leaveTypes;
}

  //This function removes other leave types for staff exceeding 6 months leaving only annual
  //For staff below 6 months it filters out all other leave types except compassionate
  public function filterOutOtherLeaveTypesIfAnnualLeaveAvailable($all_leave_types, $user){
      $leave_types =  $all_leave_types;
      $days_since_resumption = $this->calculateDaysSinceResumption($user);
      if(!isset($leave_types)){
          return $leave_types;
      }
      ////dd($leave_types);
      $annual_available = $this->hasAnnualLeave($leave_types);//false;
      for ($i=0; $i < sizeOf($leave_types); $i++) {
          $current =$leave_types[$i];

          //if a staff is more than 6 months old & has not exhausted his annual leave filter out casual and Compassionate

          if($annual_available){
              if($days_since_resumption >= 180){
                    if($current->name=='Casual' || $current->name=='Compassionate'){
                        unset($leave_types[$i]);
                    }
              }
          }
          if($days_since_resumption < 180){
              //if staff is less than 6 months old, show only compassionate option
              if($current->name!='Compassionate'){
                 unset($leave_types[$i]);
              }
          }
          if($current->name=='Casual'){
                 unset($leave_types[$i]);
          }
       }
       //////////dd($leave_types);
       return $leave_types;
  }

  public function hasAnnualLeave($leave_types){
      if(!isset($leave_types)){
          return false;
      }
      foreach ($leave_types as $current) {
          if(!isset($current)){
              return false;
          }
          if($current->name=='Annual' && $current->balance > 0){
              return true;
          }
      }
      return false;
  }

  public function resetLeaveBalanceForUnavailableLeaveTypes($leave_types, $user){
    //   $leave_types =  $all_leave_types;
      $days_since_resumption = $this->calculateDaysSinceResumption($user);
      if(!isset($leave_types)){
          return $leave_types;
      }
      $annual_available = $this->hasAnnualLeave($leave_types);

      // for ($i=0; $i < sizeOf($leave_types); $i++) {
      foreach($leave_types as $current){
          // $current =$leave_types[$i];
          
          //if a staff is more than 6 months old & has not exhausted his annual leave filter out casual and Compassionate
          //if you are a contract staff grant max days
          if($days_since_resumption > 180 or $user->is_contract_staff){
            if($annual_available && ($current->name=='Casual' | $current->name=='Compassionate')){
                $current->balance = 0;
            }
          }else{
              //if staff is less than 6 months old, show only compassionate option and is not contract staff
                if($current->name!='Compassionate'){
                  $current->balance = 0;
                }
          }
          if(!isset($current->balance)){
            $current->balance=0;
          }
       }
       
       return $leave_types;
  }

  //Not in use
  //The home page has a section leave days left, there are special rules governing that section, this function does it all
  //WARNING: This function should only be called after the getDaysLeftByLeaveType has already update the leave_type->balance
  public function UpdateLeaveDaysLeftForStaffUnder1($leave_types, $user=null, $pointInTime=null)
  {
    // $last_year = Carbon::now()->subYear();
    // if(!isset($pointInTime)){
    //   $pointInTime = Carbon::now();
    // }
    // if(!isset($user)){
    //   $user = Auth::user();
    // }
    // $contract_start_date = Carbon::parse($user->resumption_date);
    // $current_year = Carbon::now();

    // if($contract_start_date->diffInDays($current_year) < 365){
    //   foreach ($leave_types as $leave_type) {
    //    $days_granted_last_year =$this->queryLeaveApprovalsForDaysTaken($leave_type,$user, $last_year, $pointInTime);
    //    $leave_type->balance = $leave_type->balance - $days_granted_last_year;
    //   }
    // }
    return $leave_types;
  }

  public function getNumberOfUsersWithIncompleteProfile(){
    return User::where('verified',null)->count();
  }

  public function createHRRole(){
    $role = Role::where('name','HR');
    if($role==null){
      $role = new Role();
      $role->name = 'HR';
      $role->display_name = "Human Resource";
      $role->description = "A staff of HR department";
      $role->save();
    }

    $perms = ['RecordVerifier-Record Verifier-Has the right to verify records','LeaveApprover-Leave Approver-Has the right to approve leave'];
    for ($i=0; $i < sizeof($perms); $i++) {
      # code...
      $current = $perms[$i];
      $parts = explode("-",$current);
      if(sizeof($parts)==3){
        $perm = Permission::where('name', $parts[0]);
        if($perm === null){
          $perm = new Permission();
          $perm->name = $parts[0];
          $perm->display_name = $parts[1];
          $perm->description = $parts[2];
          $perm->save();
          $role->attachPermission($perm);
        }
      }
    }
  }

  public function addDaysLeft($requests, $user){
    for ($i=0; $i < sizeof($requests) ; $i++) {
      $requests[$i]->days_left = $this->getDaysLeft($requests[$i], $user);
    }
    return $requests;
  }

  public function calculateDaysSinceResumption(App\User $user, Carbon $pointInTime=null){
  
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

  //NOT IN USE
  public function getDaysLeft($thisRequest, App\User $user){
      $leave_type = LeaveType::where('name', '=', $thisRequest->leave_type)->first();//->get();
      $daysGranted = $this->queryLeaveApprovalsForDaysTaken($leave_type, $user);
      $days_since_resumption = $this->calculateDaysSinceResumption($user);
      $daysEntitledTo = $this->getDaysEntitledToAsOfXPointInTime($leave_type,$user);

      $daysLeft = $daysEntitledTo -$daysGranted;
      if(!isset($daysLeft)){
        $daysLeft=$daysEntitledTo;
      }
      return $daysLeft;
  }

  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function myRequests(Request $request)
  {
    $user = Auth::user();
    // using paginate function to show 3 actions items per page
    $ROWS_PER_TABLE = 10;

    $requests = LeaveRequest::where('created_by', $request->user()->username)->orderBy('created_at', 'desc')->take($ROWS_PER_TABLE)->get();

    for ($i=0; $i < sizeof($requests) ; $i++) {
      //   # code...
      $today = Carbon::now();
      $resumption_date = Carbon::parse($user->resumption_date);
      $days_since_resumption = Carbon::parse($today)->diffInDays(Carbon::parse($user->resumption_date));
      ////echo("today is = ");//////////var_dump($today->toDateTimeString());
      ////echo("resumption_date = ");//////////var_dump($resumption_date->toDateTimeString());

      $days_since_resumption = $resumption_date->diffInDays($today);
      $plus5 = $days_since_resumption+25;
      $minus5 = $days_since_resumption-25;
      ////////////var_dump($days_since_resumption);
      ////echo("days since resumption = ");
      ////echo($days_since_resumption);
      ////echo($user->resumption_date);
      $daysLeft =0;
      $thisRequest = $requests[$i];
      $daysGranted = LeaveApproval::where('leave_type','=',$thisRequest->type +'%')
      ->where('applicant_username', $user->username)
      ->sum('days');

      $leave_type_like = $thisRequest->leave_type . '%';
      $salary_grade_like = $user->salary_grade . '%';

      $daysEntitledTo = LeaveEntitlement::
      where('leave_type','=','Annual')
      ->where('salary_grade','like', $salary_grade_like)
      ->where('days_since_resumption', '>', $days_since_resumption)
      ->where('days_since_resumption', '<', $plus5)
      //->first()
      //->count();
      ->sum('days_allowed');
      ////echo("<br/>days entitled to: ");//////////var_dump($daysEntitledTo);
      //return;
      //    // $daysLeft = LeaveRequest::where('type',$thisRequest->type)
      //    // ->where('supervisor_response','=',true)
      //    // ->where('hr_response','=',true)
      //    // ->where('stand_in_response','=',true)->sum('days');
      //    // $thisRequest->daysLeft = $daysLeft;
      //   $daysLeft =0;
      //   $approvals = LeaveRequest::where('leave_type',$thisRequest->type)
      //    ->where('supervisor_response','=',true)
      //    ->where('hr_response','=',true)
      //    ->where('stand_in_response','=',true)->get();

      //   for ($i=0; $i < sizeof($approvals); $i++) {
      //     # code...
      //     $approval = $approvals[$i];
      //     $daysLeft+= Carbon\Carbon::parse($approval->end_date)->diffInDays(Carbon\Carbon::parse($approval->start_date));
      //   }
      $daysLeft = $daysEntitledTo -$daysGranted;
      if(!isset($daysLeft))
        $daysLeft=$daysEntitledTo;
      $thisRequest->daysLeft = $daysLeft;
    }
    $leaveTypes = LeaveType::where('name', '<>', 'Casual')->get();

    return view('leaverequests.index', array('leaverequests' => $requests, 'title' => 'My Leave Requests', 'leavetypes' => $leaveTypes));
  }

  /*
  *Shows list of requests pending supervisor approval
  **/
  public function pendingSupervisorApproval(Request $request)
  {
    $ROWS_PER_TABLE = 10;
    //if($officesRepository->isSupervisor($request->user->name))
    //{}

      //get days left per leave type

      //get all
      $requests = LeaveRequest::where('supervisor_username', $request->user()->username)
      ->where('stand_in_response', '=','1')
      ->orderBy('created_at', 'desc')
      ->paginate($ROWS_PER_TABLE);

      for ($i=0; $i < sizeof($requests) ; $i++) {
        # code...
         $thisRequest = $requests[$i];
         // $daysLeft = LeaveRequest::where('type',$thisRequest->type)
         // ->where('supervisor_response','=',true)
         // ->where('hr_response','=',true)
         // ->where('stand_in_response','=',true)->sum('days');
         // $thisRequest->daysLeft = $daysLeft;
        $daysLeft =0;
        $approvals = LeaveRequest::where('type',$thisRequest->type)
         ->where('supervisor_response','=',true)
         ->where('hr_response','=',true)
         ->where('stand_in_response','=',true);

        for ($i=0; $i < sizeof($approvals); $i++) {
          # code...
          $approval = $approvals[$i];
          $daysLeft+= Carbon\Carbon::parse($approval->end_date)->diffInDays(Carbon\Carbon::parse($approval->start_date));
        }
        $thisRequest->daysLeft = $daysLeft;
      }
      return view('leaverequests.index', array('leaverequests' => $requests, 'title' => 'Pending Approval'));

  }

  public function pendingHRApproval(Request $request)
  {
    $ROWS_PER_TABLE = 10;
    //if($officesRepository->isSupervisor($request->user->name))
    //{}

      //get days left per leave type

      //get all
      $requests = LeaveRequest::where('supervisor_response', '=', true)
      ->where('stand_in_response', '=',true)
      ->where('hr_response','=',null)
      ->orderBy('created_at', 'desc')
      ->paginate($ROWS_PER_TABLE);

      for ($i=0; $i < sizeof($requests) ; $i++) {
        # code...
         $thisRequest = $requests[$i];
         // $daysLeft = LeaveRequest::where('type',$thisRequest->type)
         // ->where('supervisor_response','=',true)
         // ->where('hr_response','=',true)
         // ->where('stand_in_response','=',true)->sum('days');
         $daysLeft =0;
    $approvals = LeaveRequest::where('type',$thisRequest->type)
         ->where('supervisor_response','=',true)
         ->where('hr_response','=',true)
         ->where('stand_in_response','=',true);

    for ($i=0; $i < sizeof($approvals); $i++) {
      # code...
      $approval = $approvals[$i];
      $daysLeft+= Carbon\Carbon::parse($approval->end_date)->diffInDays(Carbon\Carbon::parse($approval->start_date));
    }
         $thisRequest->daysLeft = $daysLeft;
      }
      return view('leaverequests.index', array('leaverequests' => $requests, 'title' => 'Pending Approval'));

  }

  /*
  *Shows list of requests for current user
  **/
  public function standInRequestsToMe(Request $request)
  {
    $ROWS_PER_TABLE = 10;
    //if($officesRepository->isSupervisor($request->user->name))
    //{}

      //get all
      $requests = LeaveRequest::where('stand_in_username', $request->user()->username)
      ->where('stand_in_response', '=', null)
      ->orderBy('created_at', 'desc')
      ->paginate($ROWS_PER_TABLE);
      return view('leaverequests.index', array('leaverequests' => $requests, 'title' => "Pending Stand-In's Acceptance"));

  }


  /**
  * Display the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function show($action)
  {
      $actions = Actions::where('action', $action)->first();
      return view('actions.show', array('actions' => $actions));
  }

  public function destroy(Request $request, $id)
  {
        //$this->authorize('destroy', $leaveRequest);
        if($id == null){
          return;
        }
        $leaveRequest = LeaveRequest::find($id);
        if($leaveRequest!=null){

          if($leaveRequest->created_by!=Auth::user()->username){
              Session::flash("flash_message","You cannot delete another's application");
          }
          if($leaveRequest->supervisor_response==null
            && $leaveRequest->hr_response==null){
              $leaveRequest->delete();
            }
            else{
              Session::flash("flash_message","You can only delete unprocessed applications");
            }
        }
        return redirect('/home');
  }

  public function download(Request $request)
  {
      if(!$request->has('id')){
          return redirect()->back()->with('flash_message', "Document not found, sorry!");
      }
      $id = $request->input('id');
      $document = Document::find($id)->first();
      if(!isset($document)){
          return redirect()->back()->with('flash_message', "Document not found, sorry!");
      }
      $header = [ "Content-Disposition" => "attachment; filename=" . $document->description ];
      return response()->download(storage_path("app/" . $document->filename), $document->description, $header);
  }

  public function create(Request $request)
  {
      if($request->has('leave_request_id')){
        $leave_request = LeaveRequest::find($request->input('leave_request_id'));
        if(!isset($leave_request)){
            return redirect()->back()->with('flash_message', 'Request not found!');
        }else{
            $document = new Document();
            $document->leave_request_id = $leave_request->id;
            $title = "Upload documents for " . $leave_request->name . " - " . $leave_request->id;
        }
      }
      return view('documents.create', compact('title', 'document'));
  }

  public function edit(Request $request)
  {
    $leaveTypes = $this->getLeaveBalanceForUser($request->user()); //LeaveType::all();
    $leavetypes = $this->filterOutOtherLeaveTypesIfAnnualLeaveAvailable($leaveTypes, $request->user());
    
    $title = "";
      if($request->has('id')){
        $application = LeaveRequest::find($request->input('id'));
        if(!isset($application)){
            return redirect()->back()->with('flash_message', 'Request not found!');
        }else{
            if(isset($application->supervisor_response)){
              return redirect()->back()->with('flash_message', 'You cannot edit an application that has been processed by your Supervisor!');
            }
            $title = " Edit Leave Request " . $application->name . " - " . $application->id;
        }
      }
      $selected_leave_type = LeaveType::where('name',$application->leave_type)->first();
      $max = $this->getDaysLeftByLeaveType($selected_leave_type);
      $application->reason = trim($application->reason);
      if($max==null){
          $max=30;
      }
      return view('leaverequests.create', compact('title', 'application', 'leavetypes', 'selected_leave_type', 'max'));
  }


  public function getADConnection(){
      $ldap = null;
      $username = "leave";
      $password = "Lms*1*2017.Final";
      try
      {
          $ldap = ldap_connect("ldap://dc.abujaelectricity.com");
      }
      catch(\Exception $ex)
      {
          //echo($ex->getMessage());
      }

      if(!$ldap)
      {
          Session::flash("flash_message", "Unable to connect to LDAP Server");
          return null;
      }

      $bind = null;

      try
      {
          $bind = ldap_bind($ldap, 'AEDC\\'.$username, $password);
      }
      catch(\Exception $e)
      {
          //echo($e->getMessage());
      }
      
      if (!$bind) {
          return;
      }
      return $ldap;        
  }

  public function searchForUserInAD($ldap, $username, $name){
      //echo("<br/>--->Searching for his/her AD account, using the combination: " . $username);
      require_once("adfunctions.php");
      ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
      ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

      $OUs = ['AbujaHQ', 'FCTCentralRO', 'FCTNorthRO', 'FCTSouthRO', 'KogiRO', 'NasarawaRO', 'NigerRO'];

      $basednPrefix = 'OU=';
      $basednSuffix = ',DC=abujaelectricity,DC=com';

      for ($i=0; $i < sizeOf($OUs); $i++) 
      {
          $basedn = $basednPrefix . $OUs[$i] . $basednSuffix;

          $userdn = getDN($ldap, $username, $basedn);

          if(isset($userdn) && strpos($userdn, 'OU'))
          {
              return $userdn;
          }
      }
      return null;
  }

  public function extractName($userdn)
  {
      try
      {
          if(!isset($userdn)){
              return null;
          }
          $parts = explode('=',$userdn);
          if(isset($parts) && sizeOf($parts) > 1)
          {
              $nameComponents = explode(",",$parts[1]);
              $name = $nameComponents[0];
              return $name;
          }
      }
      catch(\Exception $e)
      {

      }
      return null;
  }

  public function FixApplicationBalances(Request $request)
  {
    $rows = 50;
    $page = 0;
    
    if($request->has('page')){
      $page = $request->input('page');
    }
    if($request->has('rows')){
      $rows = $request->input('rows');
      if($count>200){
          $rows = 200; 
      }
    }       
    $counter = $page * $rows;
    // $counter=0;
      //get all users
      $usernames = LeaveRequest::where('id', '>', 0)->skip($counter)->take($rows)->pluck('created_by');
      foreach ($usernames as $username) {
        $counter++;
// dd($username);
        $user = User::where('username', $username)->first();
          try{
            if(isset($user)){
              $this->fixLeaveDaysLeft($user);
              //echo $counter . ". " . $username . ": done!<br/>";
            }
          }catch(\Exception $ex){
            //echo $counter . ". " . $username . ": !" . $ex->getMessage() . "<br/>";
          }
      }
  }

  public function applications(Request $request){
    // try{
    //   $this->FixApplicationBalances();
    // }catch(\Exception $ex){

    // }
    $count=50;$page=0;
    $filter = "";
    $applications=[];
    if($request->has('page')){
        $page = $request->input('page');
    }
    if($request->has('pageSize')){
        $count = $request->input('pageSize');
        if($count>200){
            $count = 200; 
        }
    }        
    if($request->has('filter')){
        $filter = $request->input('filter');
    }
    if(is_numeric($page) ==false){
        $page = 1;
    }
    else{
        $page+=1;
    }
    $raw_filter = $filter;
    if(isset($filter)){
        $filter = '%' . $filter . '%';
      $applications = LeaveRequest::where('created_by', 'like', $filter)
        ->orWhere('stand_in_username', 'like', $filter)
        ->orWhere('supervisor_username', 'like', $filter)
        ->orWhere('created_at', 'like', $filter)
        ->orWhere('days_requested', 'like', $filter)
        ->orWhere('name', 'like', $filter)
        ->orWhere('days_left', 'like', $filter)
        ->orWhere('leave_type', 'like', $filter)
        ->paginate($count);//->take($count)->skip($count*$page);
        // $users->appends(['search' => $filter]);

    }else{
      $applications = LeaveRequest::paginate($count);//->take($count)->skip($count*$page);
    }

    $prefix=0;

    $prefix = $count * ($applications->currentPage()-1);

    for ($i=0; $i < sizeof($applications); $i++) {
        $current = $applications[$i];
        $current->sn = $i+1;
    }
    return view("leaverequests.list",
        array('title' => 'Leave Applications', 'applications' => $applications, 'filter' => $raw_filter));

  }

}
