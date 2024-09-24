<?php

namespace App\Http\Controllers;

use App\LeaveApproval;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Region;
use App\FOLevel;
use App\LeaveType;
use App\LeaveRequest;
use Session;
use App\User;


class LeaveApprovalsController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function onlineOnly()
    {

        $query = LeaveApproval::where('leave_request_id', '>',0);//->get();
        $approvals = $query->get();

        for ($i=0; $i < sizeof($approvals); $i++) {
            $current = $approvals[$i];
            $current->sn = $i+1;
            if($current!=null){
                    $user = User::where('username', $current->applicant_username)->first();
                    if(isset($user)){
                        $current->applicant_name = $user->name;
                    }else{
                        $current->applicant_name = "";
                    }
            }
            $application = LeaveRequest::find($current->leave_request_id);
            if($application){
                $current->start_date = $application->start_date;
                $current->end_date = $application->end_date;
            }
        }
        return view("leaveapprovals.online",
            [
            'approvals'=> $approvals
            ]);
    }

    public function index(Request $request){
        $count=50;$page=0;
        $filter = "";
        $leaveApprovals=[];
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
        	$leaveApprovals = LeaveApproval::where('applicant_username', 'like', $filter)
            ->orWhere('days', 'like', $filter)
            ->orWhere('approved_by', 'like', $filter)
            ->orWhere('leave_type', 'like', $filter)
            ->orWhere('leave_request_id', 'like', $filter)
            ->orWhere('date_approved', 'like', $filter)
            ->orWhere('created_at', 'like', $filter)
            ->paginate($count);//->take($count)->skip($count*$page);
            // $users->appends(['search' => $filter]);
        }else{
    	    $leaveApprovals = LeaveApproval::paginate($count);//->take($count)->skip($count*$page);
        }

        $prefix=0;

        $prefix = $count * ($leaveApprovals->currentPage()-1);

        for ($i=0; $i < sizeof($leaveApprovals); $i++) {
            $current = $leaveApprovals[$i];
            $current->sn = $i+1;
            if($current!=null){
                    $user = User::where('username', $current->applicant_username)->first();
                    if(isset($user)){
                        $current->applicant_name = $user->name;
                    }else{
                        $current->applicant_name = "";
                    }
            }
        }
        return view("leaveapprovals.index",
            array('title' => 'Leave Approvals', 'approvals' => $leaveApprovals, 'filter' => $raw_filter));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if(Auth::user()->hasRole('HR') == false && Auth::user()->hasRole('Admin') == false){
          Session::flash('flash_message', 'You are not authorized to perform this task');
          return redirect()->back();
        }
        //$regions = Region::all();
        
        $LeaveApproval = new LeaveApproval();
        $title = "New Offline Leave Approval";

        if($request->has('id')){
          $id = $request->input('id');
          $LeaveApproval = LeaveApproval::where('id', $id)->first();
          if(isset($LeaveApproval)==false){
            $LeaveApproval = new LeaveApproval();
          }
          else{
            $title = "Edit Offline Leave Approval: " . $LeaveApproval->applicant_username;
          }
        }
        if($request->has('new')){
          $LeaveApproval = new LeaveApproval();
          $title = "New Leave Approval";
        }
        return view('leaveapprovals.create',
          [
            'salarygrades' => $this->getFOLevels(),
            'leavetypes' => LeaveType::where('name', '!=', 'Casual')->get(),
            'leaveapproval' => $LeaveApproval,
            'title' => $title,
        ]);
    }

    public function getFOLevels(){
      $fo1 = new FOLevel();
      $fo1->id =1;
      $fo1->name="FO1";
      $fo1->display_name = "Functional Officer I (30 days Leave)";

      $fo2 = new FOLevel();
      $fo2->id = 2;
      $fo2->name ="FO2";
      $fo2->display_name = "Functional Officer 2 (21 days Leave)";

      return array($fo1, $fo2);
    }

    public function checkPermission(){
        if(Auth::check()==false){
            Session::flash("flash_message", "Please login first");
            return redirect()->back();
        }

        if(!Auth::user()->hasRole("HR")){
            Session::flash("flash_message", "You are not authorized to add LeaveApprovals");
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
                    'days' => $request['days'],
                    'applicant_username' => $request['applicant_username'],
                    'date_approved' => $request['date_approved'],
                    'approved_by' => $request['approved_by'],
                    'leave_type' => $request['leave_type'],
                    'id' => $request['id'],
                ];

        Validator::make($data, [
            'days' => 'required|integer',
            'approved_by' => 'required|integer',
            'applicant_username' => 'required|string|max:5',
            'leave_type' => 'required|string|max:50',
            'date_approved' => 'required|string|max:10',
        ]);

        $LeaveApproval = new LeaveApproval();
        if($request->has('id')){
          $id = $request->input('id');
          if($id>0)
            $LeaveApproval =  LeaveApproval::where('id', $id)->first();
        }
        if(isset($LeaveApproval)){
          $LeaveApproval->days = $request['days'];
          $LeaveApproval->applicant_username = $request['applicant_username'];
          $LeaveApproval->approved_by = $request['approved_by'];
          $LeaveApproval->leave_type = $request['leave_type'];
          require_once('datefunctions.php');
          $LeaveApproval->date_approved = setDate($request['date_approved']);
          $LeaveApproval->save();
        }//dd($LeaveApproval);

        return redirect()->action('LeaveApprovalsController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LeaveApproval  $LeaveApproval
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveApproval $LeaveApproval)
    {
        if($LeaveApproval==null){
            Session::flash("flash_message", "LeaveApproval not found!");
            return redirect()->back();
        }
        return view("LeaveApprovals.create", ['title'=>'View LeaveApproval', 'LeaveApproval' => $LeaveApproval]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LeaveApproval  $LeaveApproval
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveApproval $LeaveApproval)
    {
        if($LeaveApproval==null){
            Session::flash("flash_message", "LeaveApproval not found!");
            return redirect()->back();
        }
        return view("LeaveApprovals.create", ['title'=>'Edit LeaveApproval', 'LeaveApproval' => $LeaveApproval]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LeaveApproval  $LeaveApproval
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveApproval $LeaveApproval)
    {
        $this->checkPermission();

        if($LeaveApproval==null){
            Session::flash("flash_message", "LeaveApproval not found!");
            return redirect()->back();
        }

        Validator::make($LeaveApproval, [
            'name' => 'required|string|max:255',
            'date' => 'required|date'
        ]);
        $LeaveApproval->save();

        return redirect()->action('LeaveApprovalsController@index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LeaveApproval  $LeaveApproval
     * @return \Illuminate\Http\Response
     */
    public function destroy($data)
    {
      if(Auth::user()->hasRole('HR') == false || Auth::user()->hasRole('Admin') == false){
        Session::flash('flash_message', 'You are not authorized to perform this task');
        return redirect()->back();
      }
        $this->checkPermission();

        $LeaveApproval = LeaveApproval::find($data);

        if($LeaveApproval==null){
            Session::flash("flash_message", "LeaveApproval not found!");
            return redirect()->back();
        }
        $LeaveApproval->delete();
        return redirect()->action('LeaveApprovalsController@index');
    }
}
