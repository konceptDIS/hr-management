<?php

namespace App\Console\Commands;
use \App\User;
use \App\LeaveRequest;

use Illuminate\Console\Command;

class FixDaysLeft extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'FixDaysLeft:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure accurate days left for all leave applications';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //get all users
        $usernames = LeaveRequest::where('id', '>', 0)->pluck('created_by');
        foreach ($usernames as $username) {

            try{
            
                $fields = array(
                    'user' => urlencode($username),
                    'username' => urlencode($username),
                );
        
                $fields_string = null;
                //url-ify the data for the POST
                foreach($fields as $key=>$value) 
                { 
                    $fields_string .= $key.'='.$value.'&'; 
                }
                rtrim($fields_string, '&');
                # code...
                // $url = "http://leave.abujaelectricity.com/fixBadDaysLeft?user=" . $username;
                $url = "https://leave.abujaelectricity.com/fixBadDaysLeft2";

                //open connection
                $ch = curl_init();
                    
                //set the url, number of POST vars, POST data
                curl_setopt($ch,CURLOPT_URL, $url);
                    
                curl_setopt($ch,CURLOPT_POST, count($fields));
                    
                curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
                    
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

                //execute post
                $result = curl_exec($ch);
                var_dump($result);

                //close connection
                curl_close($ch);
            }catch(\Exception $ex){
                var_dump($ex->getMessage());
            }
        }
    }
}
