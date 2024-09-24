<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Session\Store;
use Session;
use Cookie;
use Carbon\Carbon;
use Crypt;
class SessionTimeout
{

    protected $except = ['/'];
    protected $session;
    protected $timeout=15;
    protected $logout=false;
    public function __construct(Store $session){
        $this->session=$session;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!$request->cookie('lastActivityTime')){
            // echo("Site was loading so I created the cookie @ " . Carbon::now() . "<Br/>");
            $this->createCookie();
        }
        $last_activity_time = $request->cookie('lastActivityTime');
        // echo('Last active time: ' .  $last_activity_time . "<br/>");
        if(!$last_activity_time instanceof Carbon){
            //that means that we got the cookie unencrypted
            // get the encrypter service
            $encrypter = app(\Illuminate\Contracts\Encryption\Encrypter::class);
            try{
                //attempt to decrypt
                $last_activity_time = $encrypter->decrypt($last_activity_time);
                if(!$last_activity_time instanceof Carbon){
                    $last_activity_time = Carbon::parse($last_activity_time);
                }
            }
            catch(\Exception $ex){
                // echo($ex); //iif encryption failed, let me know & 
                $last_activity_time = Carbon::now(); //default to current time 
            }
        }
        $seconds_of_inactivity = Carbon::now()->diffInSeconds($last_activity_time);
        // echo("Seconds of inactivity = " . $seconds_of_inactivity . '<br/>');
        $minutes_of_inactivity = $seconds_of_inactivity/60; 
        // echo("Minutes of inactivity = " . $minutes_of_inactivity . '<br/>');
        // $minutes_difference = $last_activity_time->diffInMinutes(Carbon::now());
        // echo("This minute: " . Carbon::now()->minute . " last activity was " . $last_activity_time->minute . " diff= " . $minutes_of_inactivity . " timeout = " . $this->getTimeOut() . "<Br/>");
        $elapsed = $minutes_of_inactivity > $this->timeout;
        // var_dump($elapsed);
        // echo("Elapsed: " . $elapsed . "<br/>");
        if($minutes_of_inactivity > $this->timeout) //has this user been inactive in excess of five minutes
        {
            Auth::logout();
            $msg = "Your current login has expired due to inactivity.";
            // echo($msg);
            Session::flash('flash_message', $msg);

            //recreate the cookie, so the user has a fresh start
            $this->createCookie();
        }else{
            //renew the ticket
            // echo("I just updated the last activity time @ " . Carbon::now()->minute . " because the minutes of activity was just " . $minutes_of_inactivity. '<br/>');
            $this->createCookie();
        }
        return $next($request);
    }

    protected function createCookie($timeout=5){
        Cookie::queue('lastActivityTime', Carbon::now(), $timeout*$timeout*$timeout*99999);
    }

    protected function getTimeOut()
    {
        return $this->timeout;
    }
}
