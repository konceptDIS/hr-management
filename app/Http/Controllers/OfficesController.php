<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests;
use App\Office;
use Auth;
use Session;



class OfficesController extends Controller
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
        $offices = Office::all();
        for ($i=0; $i < sizeof($offices); $i++) {
            $current = $offices[$i];
            // $current->type = strtoupper(substr(0,1,$current->type) . )
            $parent = Office::where('section_id', $current->parent_section_id)->first();
            if($parent != null){
              $current->parent = $parent->name;
            }
        }
        return view('offices.index', [ 'offices' => $offices]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $office = new Office();
        $title = "";

        $title = "New Office";

        if($request->has('id')){
          $id = $request->input('id');

          if($id>0)
            $office = Offices::find($id);
            if(isset($office)==false){
              $office = new Office();
            }else{
              $title = "Edit Office: " . $office->name;
            }
        }
        if($request->has('new')){
          $office = new Office();
        }
        return view('offices.create', [
          'office' => $office,
          'title' => $title,
          'parent' => Office::all()
        ]);
    }

   public function checkPermission(){
         if(Auth::check()==false){
             Session::flash("flash_message", "Please login first");
             return redirect()->back();
         }

         if(!Auth::user()->hasRole("HR")){
             Session::flash("flash_message", "You are not authorized to add Office");
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
        //  dd($request);
         $data = [
                     'parent_section_id' => $request['parent_section_id'],
                     'abbreviation' => $request['abbreviation'],
                     'name' => $request['name'],
                     'type' => $request['type'],
                     'id' => $request['id'],
                 ];

         Validator::make($data, [
           'name' => 'required|string|max:255',
           'abbreviation' => 'required|string|max:255',
           'type' => 'required|string|max:15',
             'parent_section_id' => 'required|integer',
         ]);

         $office = new Office();
         if($request->has('id')){
           $id = $request->input('id');
           if($id>0){
              $office =  Office::where('id', $id)->first();
            }
         }
         if(isset($office)){
           $office->name = $request['name'];
           $office->type = $request['type'];
           $office->created_by = Auth::user()->username;
           $office->abbreviation = $request['abbreviation'];
           $office->parent_section_id = $request['parent_section_id'];
           $office->save();
         }//dd($office);

         return redirect()->action('OfficesController@index');
     }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function getUnits(Request $request){
      $url = $request->fullUrl();
      $dept_id = 0;
      if($request->has('id')){
        $dept_id = $request->input('id');
      }
      $units = Office::where('parent_section_id', $dept_id)->get();
      return $units;
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if($id!=null){
          $office = Office::find($id);
          if(isset($office)){
            $count = User::where('section', $office->name)
              ->orWhere('department', $office->name)
              ->orWhere('section', $office->id)
              ->orWhere('department', $office->id)->count();

            if($count>0){
              //abort delete
              Session::flash('flash_message', 'Please move all users from that Department/Unit before attempting delete');
              return;
            }
            else{
              $office->delete();
              return redirect()->back();
            }
          }
        }
    }
}
