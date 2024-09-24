<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Designation;
use App\FOLevel;
use Session;

class DesignationsController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function gatherFOLevels(){
      $fo1 = new FOLevel();
      $fo1->id ="FO1";
      $fo1->name = "30 Days";

      $fo2 = new FOLevel();
      $fo2->id ="FO2";
      $fo2->name = "21 Days";

      return array($fo1, $fo2);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fo_levels = $this->gatherFOLevels();

        if(Auth::check()==false){
            Session::flash("flash_message", "Please login first");
            return redirect()->back();
        }

        $designations = Designation::all();

        for ($i=0; $i < sizeof($designations); $i++) {
          $current = $designations[$i];

          for ($j=0; $j < sizeof($fo_levels); $j++) {
            # code...
            if($current->fo_equivalent == $fo_levels[$j]->id){
              $current->leave_days = $fo_levels[$j]->name;
            }
          }
        }
        return view("designations.index",
            [
            'designations'=> $designations,
            'fo_levels' => $fo_levels,
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
      $title="Create Designation";
      $designation = null;
      $id=0;
      if($request->has('id')){
        $id = $request->input('id');
        $designation = Designation::find('id',$id)->get();
        $title="Update Designation";
      }
      if(isset($designation)==false){
        $designation = new Designation();
      }
      $levels = $this->gatherFOLevels();
      // dd($designation);
      return view('designations.create',
                      [
                        'title' => $title,
                        'fo_levels' => $levels,
                        'designation' =>$designation
                      ]);
    }

    public function checkPermission(){
        if(Auth::check()==false){
            Session::flash("flash_message", "Please login first");
            return redirect()->back();
        }

        if(!Auth::user()->hasRole("HR")){
            Session::flash("flash_message", "You are not authorized to add designations");
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

        $designation =  new AreaOffice();
        $designation->name = $request['name'];
        $designation->date = $this->setDate($request['date']);
        //dd($designation);
        $designation->save();

        return redirect()->action('AreaOfficesController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AreaOffice  $designation
     * @return \Illuminate\Http\Response
     */
    public function show(AreaOffice $designation)
    {
        if($designation==null){
            Session::flash("flash_message", "AreaOffice not found!");
            return redirect()->back();
        }
        return view("designations.create", ['title'=>'View AreaOffice', 'designation' => $designation]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AreaOffice  $designation
     * @return \Illuminate\Http\Response
     */
    public function edit(AreaOffice $designation)
    {
        if($designation==null){
            Session::flash("flash_message", "AreaOffice not found!");
            return redirect()->back();
        }
        return view("designations.create", ['title'=>'Edit AreaOffice', 'designation' => $designation]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AreaOffice  $designation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AreaOffice $designation)
    {
        $this->checkPermission();

        if($designation==null){
            Session::flash("flash_message", "AreaOffice not found!");
            return redirect()->back();
        }

        Validator::make($designation, [
            'name' => 'required|string|max:255',
            'date' => 'required|date'
        ]);
        $designation->save();

        return redirect()->action('AreaOfficesController@index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AreaOffice  $designation
     * @return \Illuminate\Http\Response
     */
    public function destroy($data)
    {
        $this->checkPermission();

        $designation = AreaOffice::findOrFail($data);

        if($designation==null){
            Session::flash("flash_message", "AreaOffice not found!");
            return redirect()->back();
        }
        $designation->delete();
        return redirect()->action('AreaOfficesController@index');
    }
}
