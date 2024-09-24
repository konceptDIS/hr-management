<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    public function setDate($input){

      $parts = explode('/', $input);

      if($parts!=null && sizeof($parts)==3){
        return Carbon::create($parts[2],$parts[1],$parts[0]);
      }
      return null;
  }
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            //'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'section' => 'required|min:2',
            'department' => 'required|min:2',
            'designation' => 'required|min:3',
            'region' => 'required|min:3',
            'area_office' => 'required|min:3',
            'resumption_date' => 'required|date',
            'staff_id' => 'required|min:3',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        /*$user = new App\User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->section = $data['section'];
        $user->salary_grade = $data['salary_grade'];
        $user->resumption_date = $data['resumption_date'];
        $user->save();*/

        $md_approval_required = false;
        if(array_key_exists('md_approval_required', $data))
        {
                $md_approval_required = true;
        }

        return User::create([
            'name' => $data['name'],
            'username' => $data['username'],//'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'section' => $data['section'],
            'resumption_date' => $this->setDate($data['resumption_date']),
            'md_approval_required'  => $md_approval_required,
            'staff_id' => $data['staff_id'],
            'designation' => $data['designation'],
            'region' => $data['region'],
            'area_office' => $data['area_office'],

        ]);
    }
}
