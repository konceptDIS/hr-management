<?php

namespace App\Http\Controllers;

use App\LeaveEntitlement;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Region;
use App\FOLevel;
use App\LeaveType;
use Session;

class LeaveEntitlementsController extends Controller
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

        $entitlements = LeaveEntitlement::all();
        // for ($i=0; $i < sizeof($leave_entitlements); $i++) {
        //    = Region::where('id', $leave_entitlements[$i]->region_id)->first();
        //   if($region!=null){
        //     $leave_entitlements[$i]->region = $region->name;
        //   }
        // }
        return view("leaveentitlement.index",
            [
            'leaveentitlements'=> $entitlements
            ]);
    }

    public function setDate($input){

      $parts = explode('/', $input);

      if($parts!=null && sizeof($parts)==3){
        return Carbon::create($parts[2],$parts[1],$parts[0]);
      }
      return null;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if(Auth::user()->hasRole('HR') == false || Auth::user()->hasRole('Admin') == false){
          Session::flash('flash_message', 'You are not authorized to perform this task');
          return redirect()->back();
        }
        //$regions = Region::all();
        $LeaveEntitlement = new LeaveEntitlement();
        $title = "";

        if($request->has('id')){
          $id = $request->input('id');
          $LeaveEntitlement = LeaveEntitlement::where('id', $id)->first();
          if(isset($LeaveEntitlement)==false){
            $LeaveEntitlement = new LeaveEntitlement();
          }
          else{
            $title = "Edit Leave Entitlement: " . $LeaveEntitlement->name;
          }
        }
        if($request->has('new')){
          $LeaveEntitlement = new LeaveEntitlement();
          $title = "New Leave Entitlement";
        }
        return view('leaveentitlement.create',
          [
            'salarygrades' => $this->getFOLevels(),
            'leavetypes' => LeaveType::all(),
            'leaveentitlement' => $LeaveEntitlement,
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
            Session::flash("flash_message", "You are not authorized to add LeaveEntitlements");
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
                    'days_since_resumption' => $request['days_since_resumption'],
                    'salary_grade' => $request['salary_grade'],
                    'days_allowed' => $request['days_allowed'],
                    'leave_type' => $request['leave_type'],
                    'id' => $request['id'],
                ];

        Validator::make($data, [
            'days_since_resumption' => 'required|integer',
            'days_allowed' => 'required|integer',
            'salary_grade' => 'required|string|max:5',
            'leave_type' => 'required|string|max:50',
        ]);

        $LeaveEntitlement = new LeaveEntitlement();
        if($request->has('id')){
          $id = $request->input('id');
          if($id>0)
            $LeaveEntitlement =  LeaveEntitlement::where('id', $id)->first();
        }
        if(isset($LeaveEntitlement)){
          $LeaveEntitlement->days_since_resumption = $request['days_since_resumption'];
          $LeaveEntitlement->salary_grade = $request['salary_grade'];
          $LeaveEntitlement->days_allowed = $request['days_allowed'];
          $LeaveEntitlement->leave_type = $request['leave_type'];
          $LeaveEntitlement->save();
        }//dd($LeaveEntitlement);

        return redirect()->action('LeaveEntitlementsController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LeaveEntitlement  $LeaveEntitlement
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveEntitlement $LeaveEntitlement)
    {
        if($LeaveEntitlement==null){
            Session::flash("flash_message", "LeaveEntitlement not found!");
            return redirect()->back();
        }
        return view("LeaveEntitlements.create", ['title'=>'View LeaveEntitlement', 'LeaveEntitlement' => $LeaveEntitlement]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LeaveEntitlement  $LeaveEntitlement
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveEntitlement $LeaveEntitlement)
    {
        if($LeaveEntitlement==null){
            Session::flash("flash_message", "LeaveEntitlement not found!");
            return redirect()->back();
        }
        return view("LeaveEntitlements.create", ['title'=>'Edit LeaveEntitlement', 'LeaveEntitlement' => $LeaveEntitlement]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LeaveEntitlement  $LeaveEntitlement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveEntitlement $LeaveEntitlement)
    {
        $this->checkPermission();

        if($LeaveEntitlement==null){
            Session::flash("flash_message", "LeaveEntitlement not found!");
            return redirect()->back();
        }

        Validator::make($LeaveEntitlement, [
            'name' => 'required|string|max:255',
            'date' => 'required|date'
        ]);
        $LeaveEntitlement->save();

        return redirect()->action('LeaveEntitlementsController@index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LeaveEntitlement  $LeaveEntitlement
     * @return \Illuminate\Http\Response
     */
    public function destroy($data)
    {
      if(Auth::user()->hasRole('HR') == false || Auth::user()->hasRole('Admin') == false){
        Session::flash('flash_message', 'You are not authorized to perform this task');
        return redirect()->back();
      }
        $this->checkPermission();

        $LeaveEntitlement = LeaveEntitlement::findOrFail($data);

        if($LeaveEntitlement==null){
            Session::flash("flash_message", "LeaveEntitlement not found!");
            return redirect()->back();
        }
        $LeaveEntitlement->delete();
        return redirect()->action('LeaveEntitlementsController@index');
    }
}
