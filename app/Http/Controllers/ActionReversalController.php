<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\LeaveRequest;
use App\LeaveApproval;
use App\ActionReversal;
use Session;
use Auth;

class ActionReversalController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }
    
    public function index(){
        if(Auth::user()->hasRole("HR")==false){
            Session::flash("flash_message", "Access denied");
            return redirect()->back();
        }
        $reversals = ActionReversal::paginate(50);
        return view("reversals.index", compact('reversals'));
    }

        /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $req)
    {
        try{ Log::info(request()->url); }catch(\Throwable $t){}
        Session::flash("flash_message", "Please note that entries once added, cannot be deleted");
        $leave_request_id = $req->has('leave_request_id') ? $req->leave_request_id : '';
        $applicant_username = $req->has('applicant_username') ? $req->applicant_username : '';
        return view('reversals.create', compact('leave_request_id', 'applicant_username'));
    }    

/**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!$request->user()->hasRole('HR')){
            Session::flash("flash_message", "Access deniend");
            return back();
        }
        
        $data = [
                    'leave_request_id' => $request['leave_request_id'],
                    'reason' => $request['reason'],
                    'applicant_username' => $request['applicant_username'],
                ];

        Validator::make($data, [
            'leave_request_id' => 'required|integer',
            'reason' => 'required|string|max:500',
            'applicant_username' => 'required|string|max:50'
        ]);
        $leave = LeaveRequest::where('id', $request->leave_request_id)->where('created_by', trim($request['applicant_username']))->first();
        $approval = LeaveApproval::where('leave_request_id', $leave->id)->first();
        if(strtolower(trim($request->user()->username)) === strtolower(trim($leave->created_by))){
            Session::flash("flash_message", "Access deniend - you cannot reverse an action for yourself");
            return back();
        }
        if(!$leave){
            Session::flash("flash_message", "Invalid request -> there is no application with that id by that person");
            return redirect()->back();
        }
        $last_action = $leave->lastAction();
        try{
            $ar =  new ActionReversal();
            $ar->leave_request_id = $data['leave_request_id'];
            $ar->reason = $data['reason'];
            $ar->reversed_action = $leave->lastAction();
            $this->reverse($leave, $approval);
            $ar->applicant_username = $data['applicant_username'];
            try { $ar->application_data = \json_encode($leave); }catch(\Throwable $t){ Log::error($t); }
            try { if($approval) $ar->approval_data = \json_encode($approval); }catch(\Throwable $t){ Log::error($t); }
            $ar->created_by = $request->user()->username;
            $ar->save();
        }catch(\Throwable $t){
            Log::error($t);
            Session::flash("flash_message", $t->getMessage());
            return redirect()->back();
        }
        Session::flash("flash_message", "$last_action reversed");
        return redirect("/reversals");
    }

    private function reverse($leave, $approval){
        if($leave->lastAction() == "Supervisor Approved" || $leave->LastAction() == "Supervisor Denied"){
            $approval->delete();
            $leave->supervisor_response = null;
            $leave->supervisor_response_date = null;
            $leave->supervisor_response_reason = null;
            $leave->save();
        }
        if($leave->LastAction() == "Stand-in Approved" || $leave->LastAction() == "Stand-in Denied"){
            $approval->delete();
            $leave->stand_in_response = null;
            $leave->stand_in_response_date = null;
            $leave->stand_in_response_reason = null;
            $leave->save();
        }
    }

}
