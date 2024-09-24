<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use App\User;
use App\Role;
use App\Http\Requests;
use Session;
use Auth;
use App\Designation;
use App\Office;
use Excel;
use App\LeaveApproval;
use Carbon\Carbon;

class UserController extends Controller
{
    public function __construct(){
    	$this->middleware('auth');
    }

    public function listAllUsers(Request $request){
    	return view('users.all', array(
            'title' => 'All Users', 
            'users' => User::all()
            ));
    }

    public function adAccountsWhoHaveNeverLoggedIn(Request $request){
    	return view('users.all', array(
            'title' => 'Users who have never logged in', 
            'users' => User::where('last_login_date', '=', null)->get()
            ));
    }

    public function accountsNotInAD(Request $request){
    	return view('users.all', array(
            'title' => 'Users not in AD', 
            'users' => User::where('exists_in_ad', '=', false)->get()
            ));
    }

    public function usersWhoHaveNeverAppliedForLeave(Request $request){
    	return view('users.all', array(
            'title' => 'All Users', 
            'users' => []
            ));
    }

    public function usersWhoHaveNeverBeenApprovedLeave(Request $request){
    	return view('users.all', array(
            'title' => 'All Users', 
            'users' => []
            ));
    }
    
    public function listUsers(Request $request){
        $count=50;$page=0;
        $filter = "";
        $users=[];
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
        if(isset($filter)){
            $filter = '%' . $filter . '%';
        	$users = User::where('name', 'like', $filter)
            ->orWhere('username', 'like', $filter)
            ->orWhere('department', 'like', $filter)
            // ->orWhere('staff_id', 'like', $filter)
            ->orWhere('region', 'like', $filter)
            ->orWhere('designation', 'like', $filter)
            ->orWhere('area_office', 'like', $filter)
            ->orWhere('region', 'like', $filter)
            ->orWhere('section', 'like', $filter)
            ->orWhere('salary_grade', 'like', $filter)
            ->orWhere('gender', 'like', $filter)
            ->orWhere('middle_name', 'like', $filter)
            ->orWhere('first_name', 'like', $filter)
            ->orWhere('last_name', 'like', $filter)
            ->paginate($count);//->take($count)->skip($count*$page);
            // $users->appends(['search' => $filter]);
        }else{
    	    $users = User::paginate($count);//->take($count)->skip($count*$page);
        }
        $roles = Role::all();

        $prefix=0;

        $prefix = $count * ($users->currentPage()-1);

        for ($i=0; $i < sizeof($users); $i++) {
            $user=$users[$i];
            $user->sn=$i+$prefix+1;
            // $designation = Designation::where('name',$user->designation)->first();
            // if(isset($designation)){
            //     $user->designation = $designation->name;
            // }
            $dept = Office::where('name',$user->department)->first();
            if(isset($dept)){
                $user->department = $dept->name;
            }
            $unit = Office::where('name',$user->section)->first();
            if(isset($unit)){
                $user->section = $unit->name;
            }
        }
        // $this->importProfiles();

    	return view('users.index', array('title' => 'Users', 'users' => $users, 'roles'=>$roles, 'filter' => $filter));
    }

    public function usersWithIncompleteProfiles(){
      if(Auth::user()->hasRole('HR')){
        $users = User::where('verified', '=', null)->get();
        $roles = Role::all();
        return view('users.index', array('title' => 'Users', 'users' => $users, 'roles'=>$roles));
      }
      return view('errors.not-authorized');
    }

    public function delete($id){
    	$user = User::findOrFail($id);
    	if($user!=null){
    		$user->delete();
    	}
    	else{
    		Session::flash("flash_message","User not found");
    	}
    	return redirect()->back();
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
            echo($ex->getMessage());
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
            echo($e->getMessage());
        }
        
        if (!$bind) {
            return;
        }
        return $ldap;        
    }

    public function searchForUserInAD($ldap, $username, $name){
        echo("<br/>--->Searching for his/her AD account, using the combination: " . $username);
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
    
    public static function combine($a, $b){
        return strtolower(trim($a)) . "." . strtolower(trim($b));
    }

    public function importProfiles(Request $request)
    {
        $filename = '2017_staff_records.csv';
        
        if($request->has('filename')){
            $filename = $request->input('filename') . "_". $filename;
        }
        try
        {
            $today = Carbon::now();
            Excel::load($filename, function($reader) {
            echo("Excel loaded");
            $import_ran = true;

            // reader methods
            // Getting all results
            $results = $reader->get();

            //get first row
            $headerRow = $results[0];

            for ($a=0; $a < sizeOf($results); $a++) {
                $current_row = $results[$a];

                $first_name = $current_row->first_name;
                $mname = $current_row->middle_name;
                $middle_name = $current_row->middle_name;
                $last_name = $current_row->surname;
                $username =  strtolower($first_name) . '.' . strtolower($last_name);
                $counter = $a+1;

                $number_of_names = 0;
                if(isset($first_name)){
                    $number_of_names +=1;
                }
                if(isset($middle_name)){
                    $number_of_names +=1;
                }
                if(isset($last_name)){
                    $number_of_names += 1;
                }
                
                if($number_of_names < 2){
                    echo("<br/>========================================================================================");
                    echo("<br/>" . $counter . ". Working on " . $first_name . " " . $middle_name . " " . $last_name ." Staff id = " . $current_row->staff_id);
                    echo("<br/>========================================================================================");
                    echo("<br/>--->Less than two names, moving on ...");
                    continue;
                }
                if(!isset($current_row->staff_id) && $current_row->staff_id == ""){
                    echo("<br/>--->Staff ID  missing, substituting with usernam ...");
                    $current_row->staff_id = $username;//trim($current_row->staff_id);
                }
                echo("<br/>========================================================================================");
                echo("<br/>" . $counter . ". Working on " . $first_name . " " . $middle_name . " " . $last_name ." Staff id = " . $current_row->staff_id);
                echo("<br/>========================================================================================");
                if(!isset($current_row->staff_id)){
                    echo("<br/>--->Staff number missing for " . $first_name . " " . $last_name . " skipped");
                    continue;
                }
                $combinations = [];
                if(isset($middle_name))
                {
                    $combinations =
                    [ 
                        static::combine($first_name, $last_name), 
                        static::combine($last_name, $first_name), 
                        static::combine($first_name, $middle_name), 
                        static::combine($middle_name, $first_name), 
                        static::combine($last_name, $middle_name), 
                        static::combine($middle_name, $last_name) 
                    ];
                }
                else{
                    $combinations =
                    [ 
                        static::combine($first_name, $last_name), 
                        static::combine($last_name, $first_name) 
                    ];
                }
                // dd($username);
                // dd($adconnection);
                $usersADAccount = null;
                for ($b=0; $b < sizeOf($combinations) ; $b++) { 
                    $username = $combinations[$b];
                    $usersADAccount = $this->searchForUserInAD($this->getADConnection(), $username, $first_name . " " . $last_name);
                    if($usersADAccount){
                        echo(" ..... Found!!!");
                        break;
                    }else{
                        echo(" ..... NOT found!");
                    }
                }
                
                // echo('after call to AD');
                $name = "";
                if($usersADAccount){
                    $name = $this->extractName($usersADAccount);

                    //update the staff id
                    if(!isset($current_row->staff_id) && $current_row->staff_id == ""){
                        echo("<br/>--->Updating staff id with valid username ...");
                        $current_row->staff_id = $username;//trim($current_row->staff_id);
                    }
    
                }

                $copies = User::where('staff_id', $current_row->staff_id)->get();
                if($copies>0){
                    echo("<br/>--->The following share staff id with this user");
                    
                    for ($cp=0; $cp < sizeof($copies); $cp++) { 
                        echo($copies[$cp]->id . " " . $copies[$cp]->first_name . " " . $copies[$cp]->last_name . " " . $copies[$cp]->middle_name . " " . $copies[$cp]->username . " " . $copies[$cp]->phone_number . " " . $copies[$cp]->department . " " . $copies[$cp]->region);
                        
                    }
                }

                    // echo("<br/>--->Current row staff id: " . $current_row->staff_id);
                    // echo("<br/>-->Staff id of user found using: " . $user->username);
                    // var_dump($user->staff_id);
                $user = User::where('staff_id', $current_row->staff_id)
                    ->where('username', $username)
                    ->first();
                if(isset($user)){
                    if(isset($current_row->department) && $user->staff_id == $current_row->staff_id){
                        if(!isset($user->department) or $user->department == ""){
                            $user->department = static::titleCase($current_row->department);
                            echo("<br/>---> Updating department of " . $first_name . " " . $last_name . " from " . $user->department . " to " . $current_row->department);
                            
                            //update department
                            $user->department = $current_row->department;
                            try{
                                $user->save();
                                echo(".... done!");
                            }
                            catch(\Exception $ex){
                                echo("This error occured: " . $ex->getMessage());
                            }
                        }
                    }
                    if(isset($current_row->designation) && $user->staff_id == $current_row->staff_id){
                        if(!isset($user->designation) or $user->designation == ""){
                            $user->designation = static::titleCase($current_row->designation);
                            echo("<br/>---> Updating designation of " . $first_name . " " . $last_name . " from " . $user->designation . " to " . $current_row->designation);
                            
                            //update designation
                            $user->designation = $current_row->designation;
                            try{
                                $user->save();
                                echo(".... done!");
                            }
                            catch(\Exception $ex){
                                echo("This error occured: " . $ex->getMessage());
                            }
                        }
                    }
                    // echo("<br/>--->Staff id of user found using: " . $user->username . " = " . $user->staff_id);
                    if(!isset($user->region) or $user->region=="" or trim($user->region)==false){
                        if($user->region != $current_row->region && $usersADAccount && $user->staff_id == $current_row->staff_id){
                            echo("<br/>---> Updating region of " . $first_name . " " . $last_name . " from " . $user->region . " to " . $current_row->region);
                            
                            //update region
                            $user->region = $current_row->region;
                            try{
                                $user->save();
                                echo(".... done!");
                            }
                            catch(\Exception $ex){
                                echo("This error occured: " . $ex->getMessage());
                            }
                        }
                    }
                    if($user->username != $username && $usersADAccount && $user->staff_id == $current_row->staff_id){
                        echo("<br/>---> Updating username of " . $first_name . " " . $last_name . " from " . $user->username . " to " . $username);
                        
                        //update username
                        $user->username = $username;
                        try{
                            $user->save();
                            echo(".... done!");
                        }
                        catch(\Exception $ex){
                            echo("This error occured: " . $ex->getMessage());
                        }
                    }
                    if(trim($user->name)==false && $usersADAccount && $user->staff_id == $current_row->staff_id){
                        echo("<br/>---> Updating name of " . $first_name . " " . $last_name . " from " . $user->name . " to " . $name);
                        //update name
                        $user->name = $name;
                       
                        try{
                            $user->save();
                            echo(".... done!");
                        }
                        catch(\Exception $ex){
                            echo("This error occured: " . $ex->getMessage());
                        }
                    }
                    if(!isset($user->area_office) or $user->area_office=="" or trim($user->area_office)==""){
                        echo("<br/>---> Updating area office of " . $first_name . " " . $last_name . " from " . $user->area_office . " to " . $current_row->area_office);
                        //update name
                        $user->area_office = static::titleCase($current_row->area_office);
                       
                        try{
                            $user->save();
                            echo(".... done!");
                        }
                        catch(\Exception $ex){
                            echo("This error occured: " . $ex->getMessage());
                        }
                    }
                    if($user->resumption_date != "0000-00-00"){
                        echo("<br/>--->Details of " . $first_name . " " . $last_name . " are already in Leave database with user id: " . $user->id . " ... moving on");
                        continue;
                    }
                }
                // $user= $user ? null;
                $user = User::where('username', $username)->first();
                
                if(isset($user) && $user->staff_id != $current_row->staff_id){ //same person update
                    echo("<br/>--->" . $first_name . " " . $last_name . " already in Leave database, with id = " . $user->id);
                }
                // var_dump($user);
                if($user == null){
                    $user =  new User();
                }
                $user->name = $name;//ucfirst(strtolower(trim($first_name))) . ' ' . ucfirst(strtolower(trim($last_name)));//  . ' ' . ucfirst($surname);
                $user->username =  $username;
                $user->section = trim($current_row->unit);
                if(isset($current_row->designation)){
                    $user->designation = static::titleCase($current_row->designation);
                }
                if(isset($current_row->middle_name)){
                    $user->middle_name = ucfirst(strtolower(trim($current_row->middle_name)));
                }
                if(isset($current_row->first_name)){
                    $user->first_name = ucfirst(strtolower(trim($current_row->first_name)));
                }
                if(isset($current_row->last_name)){
                    $user->last_name = ucfirst(strtolower(trim($current_row->last_name)));
                }
                if(isset($current_row->leave_allowance)){
                    $leave_allowance = $current_row->leave_allowance;
                    if(!isset($leave_allowance)){
                        $leave_allowance=21; //Default to 21 days leave
                    }
                    if($leave_allowance>21)
                    {
                        $user->salary_grade = 'FO1';
                    }
                    else
                    {
                        $user->salary_grade = 'FO2';
                    }
                }
                require_once('datefunctions.php');

                if(!isset($current_row->employed_on)){
                    $user->resumption_date = $today;
                }
                else{
                    $user->resumption_date = $current_row->employed_on;
                }
                if(strtoupper($current_row->contract_staff) == 'YES')
                {
                    $user->is_contract_staff = true;
                }
                else
                {
                    $user->is_contract_staff = false;
                }
                if(isset($current_row->staff_id) && $current_row->staff_id != ""){
                    $user->staff_id = trim($current_row->staff_id);
                }else{
                    $user->staff_id = $user->username; //Temporary measure
                }
                if(strlen($user->staff_id) < 6){
                    $digitsToAdd = 6 - strlen($user->staff_id);
                    for ($i=0; $i <= sizeof($digitsToAdd); $i++) { 
                        $user->staff_id = "0" . $user->staff_id;
                    }
                }
                if(isset($current_row->gender)){
                    $user->gender = trim($current_row->gender);
                }
                // echo('After gender');
                if(isset($current_row->department)){
                    $user->department = static::titleCase($current_row->department);
                }
                if(isset($current_row->region)){
                    $user->region = static::titleCase($current_row->region);
                }
                if(isset($current_row->area_office)){
                    $user->area_office = static::titleCase($current_row->area_office);
                }
                // echo('After region');
                try
                {
                    //   //dd($current_row->employed_on);
                      if(isset($user->resumption_date) && isset($user->salary_grade)){
                        //   echo('Before save');
                          $user->save();
                          echo('<br/>--->Saved ' . $user->name . " id = " . $user->id);

                        //   //dd($user);
                          if(isset($current_row->taken)){
                              $taken = $current_row->taken;
                              if(strtoupper($taken) != "NIL"){

                                  //strip out days
                                  $taken = str_replace('days', ' ', $taken);

                                  if($taken > 0){
                                      $approver = 'offline';
                                      if(isset($current_row->leave_type)){
                                          $leave_type = ucfirst(strtolower($current_row->leave_type));
                                          $approval = LeaveApproval::where('leave_request_id', 0)
                                          ->where('days', $taken)
                                          ->where('leave_type', $leave_type)
                                          ->where('applicant_username', '=', $user->username)
                                          ->where('approved_by', '=', $approver)->first();

                                          if(isset($approval) == false){
                                              $approval = new LeaveApproval();
                                              $approval->leave_request_id = 0;
                                              $approval->days = $taken;
                                              $approval->leave_type = $leave_type;
                                              $approval->date_approved = Carbon::now();
                                              $approval->applicant_username = $user->username;
                                              $approval->approved_by = $approver;
                                              try{
                                                  $approval->save();
                                                  echo("<br/>--->Saved " . $first_name . "'s leave approvals");
                                                //   var_dump($user);
                                                //   dd($approval);
                                              }catch(\Exception $e){
                                                  //log
                                                //   echo($e->getMessage());
                                                  Session::flash("flash_message","Errors occured during the staff data import. " . $e->getMessage());
                                                  echo("<br/>--->Errors occured during the staff data import. " . $e->getMessage());
                                              }
                                          }else{
                                            echo("<br/>--->Leave approvals already saved");// . $e->getMessage());
                                          }
                                      }
                                  }
                              }
                          }
                          else{
                              echo("--->No leave approvals provided, proceeding ...");
                          }
                      }
                      else{
                        echo("--->Resumption date and salary grade not provided, skipping ...");
                      }
                  }catch(\Exception $e){
                    // dd($e);
                          //log
                          Session::flash("flash_message","Errors occured during the staff data import. " . $e->getMessage());
                          echo("<br/>--->Errors occured during the staff data import. " . $e->getMessage());
                          
                  }
              }
          });
        }catch(\Exception $ex){
            // dd($ex);
              Session::flash("flash_message","Errors occured during the staff data import. " . $ex->getMessage());
              echo("<br/>--->Errors occured during the staff data import. " . $ex->getMessage());
              
        }
    }

    static function startsWith($haystack, $needle)
    {
         $length = strlen($needle);
         return (substr($haystack, 0, $length) === $needle);
    }
    
    static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
    
        return $length === 0 || 
        (substr($haystack, -$length) === $needle);
    }

    public static function titleCase($input){
        if(!isset($input) && $input != ""){
            return $input;
        }
        // if($input)
        $output = "";
        // echo("<br/> Running title case on ". $input);
        $parts = explode(" ", $input);
        try
        {
            if(sizeOf($parts)==1 && strlen($parts[0])<5){
                return $input;
            }
        }catch(\Exception $e){
            return $input;
        }
        try{
            for ($i=0; $i < sizeOf($parts); $i++) 
            { 
                $tmp = $parts[$i];
                if(!static::startsWith($tmp, "(")){
                    $output = $output . ucfirst(strtolower(trim($tmp)));
                }
            }
        }
        catch(\Exception $e){
            // dd($e);
        }
        return $output;
    }

    public function destroy(Request $request, $id){
        if($request->user()->hasRole('Admin') == false){
            return redirect()->back()->with("flash_message", "You are not allowed to delete users");
        }
        $user = User::find($id);
        if($user){
            $user->delete();
        }
        return redirect("/users")->with("flash_message", "User deleted!");
    }
}
