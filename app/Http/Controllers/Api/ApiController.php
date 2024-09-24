<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request as RequestsRequest;
use App\LeaveApproval;
use App\LeaveEntitlement;
use App\LeaveRequest;
use App\LeaveType;
use App\LeaveTypeViewModel;
use App\Recall;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Auth;
use Carbon\Carbon;
use Session;
use GuzzleHttp\Client;

class ApiController extends Controller
{
    private $SSO_URL;

    public function __construct()
    {
        $this->SSO_URL = env('SSO_URL');
    }


    public function myAedcDashboard(Request $request)
    {
        try {
        // Retrieve the 'token' query parameter from the request
        $token = $request->query('token');
        $content = $this->attemptSSOAutentication($token);

        if ($content != null && !empty($content)) {
            $response = json_decode($content, true);
            // dd($response['decoded']['user']);
            if (!property_exists((object) $response, 'decoded')) {
            $error = $this->extractErrorMessage($content);
            return response()->json($error);
            //dd($content);
            }
            if (property_exists((object) $response, 'decoded')) {
                $userData = $response['decoded']['user'];
                $staff_id =  $userData['staff_id'];
                $user = \App\User::where('staff_id', $staff_id)->first();

                if (!$user) {
                    return response()->json(['message' => 'error', 'error' => 'No user record found']);
                    Log::error('No user record found');
                }

                $leave_response = [
                    'annual' => [
                        'total' => 30,
                        'taken' => 0,
                        'remaining' => 30,
                    ],
                    'examination' => [
                        'total' => 5,
                        'taken' => 0,
                        'remaining' => 5,
                    ],
                    'paternity' => [
                        'total' => 5,
                        'taken' => 0,
                        'remaining' => 5,
                    ],
                    'maternity' => [
                        'total' => 112,
                        'taken' => 0,
                        'remaining' => 112,
                    ],
                ];

                $currentYear = Carbon::now()->year;

                $approved_leave = LeaveRequest::where('created_by', '=',$user->username)
                ->where('stand_in_response', '=', 1)
                ->where('supervisor_response', '=', 1)
                ->whereRaw('YEAR(created_at) = ?', [$currentYear])
                ->get();

                foreach ($approved_leave as $leave) {
                    $leave_type = strtolower($leave->leave_type);

                    if(isset($leave_response[$leave_type])){
                        $leave_response[$leave_type]['taken'] += $leave->days_requested;
                    }
                }

                foreach($leave_response as $key => $response){
                    if($key == "maternity"){
                        //Get total taken days from annual and maternity
                        $taken = $leave_response['maternity']['taken'] + $leave_response['annual']['taken'];
                        //remove taken days from maternity total
                        $leave_response['maternity']['remaining'] = $leave_response['maternity']['total'] - $taken;
                    }else{
                        $leave_response[$key]['remaining'] = $leave_response[$key]['total'] - $leave_response[$key]['taken'];
                    }
                }

                //unset maternity or paternity based on gender
                if($user->gender == 'Male'){
                    unset($leave_response['maternity']);
                }else{
                    unset($leave_response['paternity']);
                }

                // $leaveTypes = $this->getLeaveBalanceForUser($user);
                // Log::error($leaveTypes);
                // $leaveTypes = $this->resetLeaveBalanceForUnavailableLeaveTypes($leaveTypes, $user);

                return response()->json($leave_response);
            }
        }
        } catch (\Exception $e) {
        Log::error("Get Dashboard Data Error: " . $e->getMessage());
        }
    }


    public function attemptSSOAutentication($jwtToken)
    {
        $url =  $this->SSO_URL;
        try {
        $client = new Client();
        $response = $client->post($url, ['json' => ['token' => $jwtToken]]);
        $responsebody = $response->getBody()->getContents();   // Get response body
        return $responsebody;
        } catch (\Exception $e) {
        return  $e->getMessage();
        }
    }

    public function extractErrorMessage($response){
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');
        $jsonLength = $jsonEnd - $jsonStart + 1;
        $jsonData = substr($response, $jsonStart, $jsonLength);
        $errorData = json_decode($jsonData, true);
        return $errorData;
    }
}
