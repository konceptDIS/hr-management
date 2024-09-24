<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\User;

class SSOController extends Controller
{
  use AuthenticatesUsers;

  private $SSO_URL;
  private $ERP_URL;

  public function __construct()
  {
    $this->SSO_URL = env('SSO_URL');
    $this->ERP_URL = env('ERP_URL');
  }


  public function handleSSO(Request $request)
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
            $this->createUserIFNotExit($userData);
            Log::error('User Information' . $userData);
          }
          Log::error('User Information' . $user);
          $this->guard()->login($user, false);
          return redirect("/home");
        }
      }
    } catch (\Exception $e) {
      Log::error("SSO Verification Failed: " . $e->getMessage());
    }
    // return response()->json(['token' => $token]);
    // return redirect("/home");
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



  public function createUserIFNotExit($userData)
  {
    $user = new \App\User();
    $user->name = $userData['name'];
    $user->first_name = $userData['firstname'];
    $user->last_name = $userData['surname'];
    $user->middle_name = $userData['name'];
    $user->password = bcrypt("");
    $user->section = " ";
    $user->designation = $userData['job_title'];
    $user->department = $userData['department'];
    $user->phone_number = $userData['mobile_phone'];
    //update last login date
    $user->last_login_date = Carbon::now();
    $user->exists_in_ad = true;
    $user->save();
  }


  public function extractErrorMessage($response){
    $jsonStart = strpos($response, '{');
    $jsonEnd = strrpos($response, '}');
    $jsonLength = $jsonEnd - $jsonStart + 1;
    $jsonData = substr($response, $jsonStart, $jsonLength);
    $errorData = json_decode($jsonData, true);
    return $errorData;
  }

  public function logOut(Request $request)
  {
    Auth::logout();
    //return redirect($this->ERP_URL);
    return redirect()->away($this->ERP_URL);
  }
}