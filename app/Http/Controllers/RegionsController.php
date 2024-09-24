<?php

namespace App\Http\Controllers;

use App\Region;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RegionsController extends Controller
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

        return view("regions.index",
            [
            'regions'=> Region::all()
            ]);
    }

    public function createRegions(){
      $regions = ['FCT North', 'FCT Central', 'FCT South', 'Kogi', 'Niger', 'Nassarawa', 'AEDC Headquarters'];

    }

    public function createAreaOffices($region){

      if($region->name == 'FCT North'){

        
      }
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
    public function create()
    {
        return view('regions.create');
    }

    public function checkPermission(){
        if(Auth::check()==false){
            Session::flash("flash_message", "Please login first");
            return redirect()->back();
        }

        if(!Auth::user()->hasRole("HR")){
            Session::flash("flash_message", "You are not authorized to add regions");
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
                ];

        Validator::make($data, [
            'name' => 'required|string|max:50',
        ]);

        $region =  new Region();
        $region->name = $request['name'];
        //dd($region);
        $region->save();

        return redirect()->action('RegionsController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function show(Region $region)
    {
        if($region==null){
            Session::flash("flash_message", "Region not found!");
            return redirect()->back();
        }
        return view("regions.create", ['title'=>'View Region', 'region' => $region]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function edit(Region $region)
    {
        if($region==null){
            Session::flash("flash_message", "Region not found!");
            return redirect()->back();
        }
        return view("regions.create", ['title'=>'Edit Region', 'region' => $region]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Region $region)
    {
        $this->checkPermission();

        if($region==null){
            Session::flash("flash_message", "Region not found!");
            return redirect()->back();
        }

        Validator::make($region, [
            'name' => 'required|string|max:50',
        ]);
        $region->save();

        return redirect()->action('RegionsController@index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function destroy($data)
    {
        $this->checkPermission();

        $region = Region::findOrFail($data);

        if($region==null){
            Session::flash("flash_message", "Region not found!");
            return redirect()->back();
        }
        $region->delete();
        return redirect()->action('RegionsController@index');
    }
}
