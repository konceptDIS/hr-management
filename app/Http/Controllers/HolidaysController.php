<?php

namespace App\Http\Controllers;

use App\Holiday;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\LeaveRequest;
use App\LeaveApproval;
use App\Role;
use App\User;
use Mail;
use App;
use Session;
use App\Mail\LeaveResumptionAdjusted;
use App\Services\HolidayService;
use Cache;
use Illuminate\Support\Facades\Log;

class HolidaysController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::check()==false){
            Session::flash("flash_message", "Please login first");
            return redirect()->back();
        }
        $known_holidays_created = Cache::get('known-holidays-check');
        Log::info(['known_holidays_created' => $known_holidays_created]);
        if(!$known_holidays_created){
            Log::info("Creating Known holidays");
            HolidayService::createKnownHolidays(); 
            Cache::rememberForever('known-holidays-check', function(){
               return true;
           });
        }else{
            Log::info("Known holidays previously created");
        }
        return view("holidays.index",
            [
            'holidays'=> Holiday::whereYear('date', Carbon::now()->year)
                ->orderBy('date', 'asc')->get()
            ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Session::flash("flash_message", "Please note that holidays once added, cannot be deleted");
        return view('holidays.create');
    }

    public function checkPermission(){
        if(Auth::check()==false){
            Session::flash("flash_message", "Please login first");
            return redirect()->back();
        }

        if(!Auth::user()->hasRole("HR")){
            Session::flash("flash_message", "You are not authorized to add holidays");
            return redirect()->back();
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->checkPermission();

        $data = [
                    'name' => $request['name'],
                    'date' => $request['date'],
                ];

        Validator::make($data, [
            'name' => 'required|string|max:255',
            'date' => 'required|date'
        ]);

        $holiday =  new Holiday();
        $holiday->name = $request['name'];
        $holiday->created_by = $request->user()->username;
        require_once("datefunctions.php");
        $holiday->date = setDate($request['date']);
        if($holiday->date <= Carbon::today()){
            Session::flash("flash_message", "Sorry, you can only add a future holiday!");
            return redirect()->back();
        }
        if(Holiday::whereYear('date', $holiday->date->year)
        ->whereMonth('date', $holiday->date->month)
        ->whereDay('date',$holiday->date->day)->count() > 0){
            Session::flash("flash_message", "Sorry, holiday already exists!");
            return redirect()->back();
        }
        ////dd($holiday);
        $holiday->save();

        $previousMonth =$holiday->date->month-1;
        $affected = LeaveRequest::whereYear('start_date', $holiday->date->year) //starts on the holiday year
        ->whereMonth('start_date', '>=', $previousMonth) //starts on  the holiday month, or month before
        // ->where('created_by','emem.isaac')
        // ->where('supervisor_username','emem.isaac')
        ->get();

        for ($i=0; $i < sizeof($affected); $i++) {
            $current = $affected[$i];
            $holiday_date = Carbon::parse($holiday->date);
            $start_date = Carbon::parse($current->start_date);
            $end_date = Carbon::parse($current->end_date);
            if($holiday_date->between($start_date, $end_date) && $holiday_date->isWeekend()===false){
                


                $resumption_date = Carbon::create($end_date->year, $end_date->month, $end_date->day);
                echo("<br/>Original resumption date: " . $resumption_date->year . "/" . $resumption_date->month . "/" . $resumption_date->day);
                $resumption_date->addDay();
                while($resumption_date->isWeekend() or Holiday::whereDay('date', $resumption_date->day)
                    ->whereMonth('date', $resumption_date->month)
                    ->whereYear('date', $resumption_date->year)
                    ->count()>0){
                    $resumption_date->addDay();
                }
                echo("<br/>New resumption date: " . $resumption_date->year . "/" . $resumption_date->month . "/" . $resumption_date->day);
                $current->end_date = $resumption_date;
                // dd($resumption_date);
                //shift resumption date by 1, add 1 to days in Approval
                $current->save();
                $approval = LeaveApproval::where('leave_request_id', $current->id)->first();
                if($approval!=null){
                    
                    $approval->days+=1;
                    // dd($approval);
                    $approval->save();
                }
            
                //send mails to the applicant and to his boss and to hr
                $applicant = \App\User::where('username', '=', $current->created_by)->first();
                $approver = $this->getUser($current->stand_in_username);
                $supervisor = $this->getUser($current->supervisor_username);
                    // ////////////dd($supervisor);
                try{
                    Mail::setSwiftMailer($this->getSwiftMailer());
                    // $cc = $this->getHREmailAddresses($applicant);
                    $cc = $this->getHREmailAddresses();
                    // array_push($cc, $request->user()->email());
                    array_push($cc, $approver->email());
                    array_push($cc, $supervisor->email());
                    Mail::to($applicant->email(), $applicant->name)->cc($cc)//to stand in, copy applicant, copy boss
                        ->send(new LeaveResumptionAdjusted($current, $applicant, $approver, $holiday));
                }catch(\Exception $ex){
                    Session::flash("flash_message","Your request was processed successfully, but the email notifications failed");
                    dd($ex);
                }
            }
        }
        return redirect()->action('HolidaysController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function show(Holiday $holiday)
    {
        if($holiday==null){
            Session::flash("flash_message", "Holiday not found!");
            return redirect()->back();
        }
        return view("holidays.create", ['title'=>'View Holiday', 'holiday' => $holiday]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function edit(Holiday $holiday)
    {
        if($holiday==null){
            Session::flash("flash_message", "Holiday not found!");
            return redirect()->back();
        }
        return view("holidays.create", ['title'=>'Edit Holiday', 'holiday' => $holiday]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Holiday $holiday)
    {
        $this->checkPermission();

        if($holiday==null){
            Session::flash("flash_message", "Holiday not found!");
            return redirect()->back();
        }

        Validator::make($holiday, [
            'name' => 'required|string|max:255',
            'date' => 'required|date'
        ]);
        if($holiday->date <= Carbon::today()){
            Session::flash("flash_message", "Sorry, you can only update a future holiday!");
            return redirect()->back();
        }
        $holiday->save();

        return redirect()->action('HolidaysController@index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function destroy($data)
    {
        $this->checkPermission();
        Session::flash("flash_message", "Holidays can no longer be deleted!");
        return redirect()->back();
        $holiday = Holiday::findOrFail($data);

        if($holiday==null){
            Session::flash("flash_message", "Holiday not found!");
            return redirect()->back();
        }
        if($holiday->date <= Carbon::today()){
            Session::flash("flash_message", "Sorry, you can only delete a future holiday!");
            return redirect()->back();
        }
        $holiday->delete();
        return redirect()->action('HolidaysController@index');
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

      public function getRoleMembers($roleName){
        $roleWithMembers = Role::with('users')->where('name', $roleName)->get();
        return $roleWithMembers;
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
}
