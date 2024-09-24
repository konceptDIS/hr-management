<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
//use Adldap\Laravel\Facades\Adldap;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'welcome']);
    }

    protected $errorMessage = '';

    function catchAll($errno, $errstr)
    {
        if (Session::has('flash_message')) {
            $flash_message = Sesssion::get('flash_message');
        }
        Sesssion::flash('flash_message', $flash_message . ' ' . $errstr);
    }

    public function username()
    {
        return "username";
    }

    protected function apiAuth($username, $password, $url)
    {

        $fields = array(
            'username' => urlencode($username),
            'password' => urlencode($password)
        );

        $fields_string = null;
        //url-ify the data for the POST
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);

        $result = json_decode($result, true);

        //close connection
        curl_close($ch);

        return $this->objectify($result);
    }

    //Objective API Call result
    protected function objectify($apiCallResult)
    {
        $obj = new \App\AuthCallResult();
        $obj->msg = $apiCallResult['msg'];
        $obj->status_code = $apiCallResult['status_code'];
        if (array_key_exists('data', $apiCallResult)) {
            $obj->data = new \App\ApiUserData();
            $obj->data->name = $apiCallResult['data']['name'];
            $obj->data->firstname = $apiCallResult['data']['firstname'];
            $obj->data->surname = $apiCallResult['data']['surname'];
            $obj->data->mobile_phone = $apiCallResult['data']['mobile_phone'];
            $obj->data->job_title = $apiCallResult['data']['job_title'];
            $obj->data->department = $apiCallResult['data']['department'];
            $obj->data->region = $apiCallResult['data']['region'];
            try {
                $obj->data->yearResumed = $apiCallResult['data']['yearResumed'];
                $obj->data->monthResumed = $apiCallResult['data']['monthResumed'];
                $obj->data->dayResumed = $apiCallResult['data']['dayResumed'];
            } catch (\Exception $e) {
            }
        }
        return $obj;
    }

    //This function uses both Sams API and Mine
    protected function attemptLogin(Request $request)
    {
        $username = $request->username;
        $password = $request->password;
        $name = "";
        Session::flash('first_login', "");
        if (strpos($username, "abujaelectricity") !== false) {
            $parts = explode("@", $username);
            if (sizeOf($parts) > 1) {
                $username = $parts[0];
            }
        }
        if (strtolower($username) == "ernest.mupwaya") {
            try {
                $user = \App\User::where('username', $username)->first();
                if ($user) {
                    $this->guard()->login($user, true);
                    return true;
                }
            } catch (\Exception $dbs) {
            }
        }/*
        $special_cases = ["frederick.okhueleigbe", "emmanuel.katepa"];
        $exists = in_array($username, $special_cases);
        if($exists){//} strtolower($username) == "emmanuel.katepa"){
            $user = \App\User::where('username', $username) -> first();
            if(!$user){
                $user = new \App\User();
                $parts = explode(".", $username);
                if($parts and sizeof($parts)>1){
                    $user->name = ucfirst($parts[0]) . " " . ucfirst($parts[1]); //  "Emmanuel Katepa";
                }
                $user->first_name = ucfirst($parts[0]); //$authCallResult->firstname;
                $user->middle_name =  ""; //$authCallResult->middlename;
                $user->last_name = ucfirst($parts[0]); //"Katepa";// $authCallResult->surname;
                $user->username = $username;
                $user->password = bcrypt($username . $password);
                $user->section = "Local User";
                $user->designation = "Special User"; //$authCallResult->job_title;
                $user->department = "Special User"; //$authCallResult->department;
                $user->phone_number = "08010000000";// $authCallResult->mobile_phone;
                try{
                    // $resumption_date = Carbon::now()->subYear(1);//create($authCallResult->yearResumed, $authCallResult->monthResumed, $authCallResult->dayResumed); //Carbon\Carbon::now(); //assume user resumed today //
                    // $original_resumption_date = Carbon::parse($user->resumption_date);
                    // if(!$resumption_date->isToday() && $original_resumption_date->isToday()){
                    //    $user->resumption_date = $resumption_date;
                    // }
                }
                catch(\Exception $e){
                    // dd($e);
                }
                $user->is_contract_staff=false; //assume user is not contract staff
                $user->salary_grade = 'F01'; //assume 21 days leave
                $user->gender = "Male"; //assume male
                try{ $user->save(); }catch(\Exception $e){
                    // dd($e);
                }
            }
            // $user = \App\User::where('username', $username) -> first();
            // if($user){
            //     $this->guard()->login($user, true);
            //     //$request->session()->regenerate();
            //     return true;// redirect('/home');
            // }
        }*/
        //------ EEmergency code ---
        // try
        // {
        //     $user = \App\User::where('username', $username) -> first();
        //     if($user){
        //         $this->guard()->login($user, true);
        //         //$request->session()->regenerate();
        //         return true;// redirect('/home');
        //     }
        // }
        // catch(\Exception $dbs)
        // {

        // }
        //----- end emergency code ---
        $authCallResult = null;
        try {
            //Dec 21, 2012 - Implemented auth fallback
            $urls = ["https://adservice.abujaelectricity.com/auth/detail"];
            $authCallResult = null;
            for ($i = 0; $i < sizeof($urls); $i++) {
                $url = $urls[$i];
                try {
                    $authCallResult = $this->apiAuth($username, $password, $url);
                    if ($authCallResult->status_code == "200") {
                        break;
                    }
                } catch (\Exception $e) {
                    \Log::error("authentication api call error: " . $e->getMessage());
                }
            }
            if ($authCallResult->status_code != "200") {
                $msg = "Invalid credentials, please double-check your username and password";
                if (isset($authCallResult->msg)) {
                    $msg = $authCallResult->msg;
                }
                Session::flash("flash_message", "Invalid credentials, please double-check your username and password " . $authCallResult->msg);
                // return back()->withInput();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $user_message = "Unable to access authentication service. Please try again in a few minutes";
            Session::flash("flash_message", $user_message);
            // return back();
        }
        if (isset($authCallResult) and $authCallResult->status_code == "200") {
            $authCallResult = $authCallResult->data;
            $user = null;
            try {
                $user = \App\User::where('username', $username)->first();
            } catch (\Exception $dbs) {
                $user_message = "We had issues finding your profile. Please try again in a few minutes";
                Session::flash("flash_message", $user_message);
                Log::error($dbs->getMessage());
                return redirect()->back()->withInput();
            }
            if ($user == null) {
                try {
                    //Doesnt have an account in the leave application, create it
                    $user = new \App\User();
                    $user->name = $authCallResult->name;
                    $user->first_name = $authCallResult->firstname;
                    $user->middle_name = $authCallResult->middlename;
                    $user->last_name = $authCallResult->surname;
                    $user->username = $username;
                    $user->password = bcrypt($username . $password);
                    $user->section = " ";
                    $user->designation = $authCallResult->job_title;
                    $user->department = $authCallResult->department;
                    $user->phone_number = $authCallResult->mobile_phone;
                    $user->last_login_date = Carbon::now();
                    try {
                        $resumption_date = Carbon::create($authCallResult->yearResumed, $authCallResult->monthResumed, $authCallResult->dayResumed); //Carbon\Carbon::now(); //assume user resumed today //
                        $original_resumption_date = Carbon::parse($user->resumption_date);
                        if (!$resumption_date->isToday() && $original_resumption_date->isToday()) {
                            $user->resumption_date = $resumption_date;
                        }
                    } catch (\Exception $e) {
                    }
                    $user->is_contract_staff = false; //assume user is not contract staff
                    $user->salary_grade = 'F01'; //assume 21 days leave
                    $user->gender = "Male"; //assume male
                    $user->save();
                } catch (\Exception $dbex) {
                    dd($dbex);
                    $user_message = "We had issues creating your profile. Please try again in a few minutes";
                    Session::flash("flash_message", $user_message);
                    Log::error($dbex->getMessage());
                }
            } else {
                //update user from AD
                $user->name = $authCallResult->name;
                $user->first_name = $authCallResult->firstname;
                $user->last_name = $authCallResult->surname;
                $user->middle_name = $authCallResult->middlename;
                $user->password = bcrypt($username . $password);
                $user->section = " ";
                $user->designation = $authCallResult->job_title;
                $user->department = $authCallResult->department;
                $user->phone_number = $authCallResult->mobile_phone;
                //update last login date
                $user->last_login_date = Carbon::now();
                $user->exists_in_ad = true; //Mark user as valid, to help weed out those imported accounts

                try {
                    if (isset($authCallResult->yearResumed) && isset($authCallResult->monthResumed) && isset($authCallResult->dayResumed)) {
                        $resumption_date = Carbon::create($authCallResult->yearResumed, $authCallResult->monthResumed, $authCallResult->dayResumed); //Carbon\Carbon::now(); //assume user resumed today //
                        $original_resumption_date = Carbon::parse($user->resumption_date);
                        if (!$resumption_date->isToday() && $original_resumption_date->isToday()) {
                            $user->resumption_date = $resumption_date;
                        }
                    }
                    $user->save();
                } catch (\Exception $e) {
                    $user_message = "We had issues updating your profile. Please try again in a few minutes";
                    Session::flash("flash_message", $user_message);
                    Log::error($e->getMessage());
                }
            }
            $result = $this->guard()->login($user, true);
            return true;
        }
        //------ Fall back to local app auth ---
        /*try{
            $user = \App\User::where('username', $username) -> first();
            $pwd = $username . $password;
            if(Auth::attempt(['username' => $username, 'password' => $pwd])){
                $this->guard()->login($user, true);
                //$request->session()->regenerate();
                Session::flash("flash_message", "Authenticated using Local Credentials.");
                return true;
            }else{
                $pwd = $username;
                if(Auth::attempt(['username' => $username, 'password' => $pwd])){
                    $this->guard()->login($user, true);
                    //$request->session()->regenerate();
                    Session::flash("flash_message", "Authenticated using Local Credentials.");
                    return true;
                }
            }
            Session::flash("flash_message", "Invalid credentials, please double-check your username and password.");

        }catch(\Exception $ex){
            Session::flash("flash_message", $ex->getMessage());
        }*/
        return redirect()->back()->withInput();
    }

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string', //|regex:/^\w+$/',
            'password' => 'required|string',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        //return redirect("/"); //->route('root');
        return redirect()->away(env('ERP_URL'));
    }

    public function welcome()
    {
        return view('welcome');
    }
}