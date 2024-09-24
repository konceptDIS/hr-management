<?php

namespace App\Http\Controllers;

use App\AreaOffice;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Region;

class AreaOfficesController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function getByRegion(Request $request){
      $url = $request->fullUrl();
      $region_id = 0;
      if($request->has('id')){
        $region_id = $request->input('id');
      }
      $offices = AreaOffice::where('region_id', $region_id)->get();
      return $offices;
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

        $area_offices = AreaOffice::all();
        for ($i=0; $i < sizeof($area_offices); $i++) {
          $region = Region::where('id', $area_offices[$i]->region_id)->first();
          if($region!=null){
            $area_offices[$i]->region = $region->name;
          }
        }
        return view("areaoffices.index",
            [
            'areaoffices'=> $area_offices
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
        $regions = Region::all();
        $areaOffice = new AreaOffice();
        $title = "";

        if($request->has('id')){
          $id = $request->input('id');
          $areaOffice = AreaOffice::where('id', $id)->first();
          if(isset($areaOffice)==false){
            $areaOffice = new AreaOffice();
          }
          else{
            $title = "Edit Area Office: " . $areaOffice->name;
          }
        }
        if($request->has('new')){
          $areaOffice = new AreaOffice();
          $title = "New Area Office";
        }
        return view('areaoffices.create',
          [
            'regions' => $regions,
            'areaOffice' => $areaOffice,
            'title' => $title,
        ]);
    }

    public function checkPermission(){
        if(Auth::check()==false){
            Session::flash("flash_message", "Please login first");
            return redirect()->back();
        }

        if(!Auth::user()->hasRole("HR")){
            Session::flash("flash_message", "You are not authorized to add areaoffices");
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
                    'region_id' => $request['region_id'],
                    'name' => $request['name'],
                    'id' => $request['id'],
                ];

        Validator::make($data, [
            'name' => 'required|string|max:255',
            'region_id' => 'required|integer',
        ]);

        $areaoffice = new AreaOffice();
        if($request->has('id')){
          $id = $request->input('id');
          if($id>0)
          $areaoffice =  AreaOffice::where('id', $id)->first();
        }
        if(isset($areaoffice)){
          $areaoffice->name = $request['name'];
          $areaoffice->region_id = $request['region_id'];
          $areaoffice->save();
        }//dd($areaoffice);

        return redirect()->action('AreaOfficesController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AreaOffice  $areaoffice
     * @return \Illuminate\Http\Response
     */
    public function show(AreaOffice $areaoffice)
    {
        if($areaoffice==null){
            Session::flash("flash_message", "AreaOffice not found!");
            return redirect()->back();
        }
        return view("areaoffices.create", ['title'=>'View AreaOffice', 'areaoffice' => $areaoffice]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AreaOffice  $areaoffice
     * @return \Illuminate\Http\Response
     */
    public function edit(AreaOffice $areaoffice)
    {
        if($areaoffice==null){
            Session::flash("flash_message", "AreaOffice not found!");
            return redirect()->back();
        }
        return view("areaoffices.create", ['title'=>'Edit AreaOffice', 'areaoffice' => $areaoffice]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AreaOffice  $areaoffice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AreaOffice $areaoffice)
    {
        $this->checkPermission();

        if($areaoffice==null){
            Session::flash("flash_message", "AreaOffice not found!");
            return redirect()->back();
        }

        Validator::make($areaoffice, [
            'name' => 'required|string|max:255',
            'date' => 'required|date'
        ]);
        $areaoffice->save();

        return redirect()->action('AreaOfficesController@index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AreaOffice  $areaoffice
     * @return \Illuminate\Http\Response
     */
    public function destroy($data)
    {
        $this->checkPermission();

        $areaoffice = AreaOffice::findOrFail($data);

        if($areaoffice==null){
            Session::flash("flash_message", "AreaOffice not found!");
            return redirect()->back();
        }
        $areaoffice->delete();
        return redirect()->action('AreaOfficesController@index');
    }
}
