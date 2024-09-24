<?php

namespace App\Http\Controllers;


use App\Http\Requests;
use Illuminate\Http\Request;
use App\User;
use App\FOLevel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Auth;
use Carbon\Carbon;
use App\AreaOffice;
use App\Region;
use App\Designation;
use App\Office;
use Session;
use App\Role;

class ProfileController extends Controller
{
		/**
		 * Create a new controller instance.
		 *
		 * @return void
		 */
		public function __construct()
		{
				$this->middleware('auth');
		}

		public function setDate($input){

			$parts = explode('/', $input);

			if($parts!=null && sizeof($parts)==3){
				return Carbon::create($parts[2],$parts[1],$parts[0]);
			}
			return null;
		}

		public function completeProfileGet(Request $request){
				$user = Auth::user();
				$id=0;
				$showNameField=false;
				if($request->has('id')){
					$id = $request->input('id');
				}
				if($request->has('new')){
					$showNameField=true;
					$user= new User();
				}
				$title="";

				if($id>0 && (Auth::user()->hasRole('Admin') or Auth::user()->hasRole('HR'))){
					$user = User::where('id', $id)->first();
				}
				if($id>0 && Auth::user()->hasRole('Admin')){
					$showNameField=true;
				}
				$genders = ['Male', 'Female'];
				$units = Office::where('type', 'unit')->get();
				// if(isset($user->section)){
				//   $units = Office::where('name', $user->section)->get();
				// }else{
				//   $units = Office::where('type', 'unit');
				// }

				if(Auth::user()->id==$id){
					$title = "Your profile";
				}
				else{
					$title = "Profile - " . $user->name;
				}

				if($showNameField){
					// $title = "New Staff Profile";
				}

				try{
					$mdrole = Role::where('name','MD');
					if($mdrole !=null){
						$mdrole->delete();
					}
				}catch(Exception $ex){

				}
				$roles = Role::all();

				return view("profile.completeprofile",
					[
						'user' => $user,
						'designations' => Designation::all(),
						'departments' => Office::where('type','department')->get(),
						'regions' => Region::all(),
						'area_offices' => AreaOffice::all(),
						'units' => $units,
						'genders' => $genders,
						'title' => $title,
						'roles' => $roles,
						'showName' => $showNameField,
						'folevels' => $this->getFOLevels()
					]
				);
		}

		public function getFOLevels(){
			$fo1 = new FOLevel();
			$fo1->id =1;
			$fo1->name="FO1";
			$fo1->display_name = "30 days Leave";

			$fo2 = new FOLevel();
			$fo2->id = 2;
			$fo2->name ="FO2";
			$fo2->display_name = "21 days Leave";

			return array($fo1, $fo2);
		}

		public function completeProfilePost(Request $request){
			if(!Auth::user()->hasRole('Admin') and !Auth::user()->hasRole('HR')){
				return redirect()->back()->with('flash_message', 'You cannot update your profile. If changes are required please contact your Office Manager or send a chat message.');
			}
				$data = [
						'name' => $request['name'],
						'isnew' => $request['isnew'],
						'salary_grade' => $request['salary_grade'],
						'section' => $request['section'],
						'resumption_date' =>$request['resumption_date'],
						'staff_id' => $request['staff_id'],
						'gender' => $request['gender'],
						'designation' => $request['designation'],
						'region' => $request['region'],
						'area_office' => $request['area_office'],
						'department' => $request['department'],
						'username' => $request['username'],
						'is_contract_staff' => $request['is_contract_staff'],
						];

				$operator = Auth::user();

				$user = null;
				$id=0;
				if($operator->hasRole("HR") or $operator->hasRole("Admin")){
					if($request->has('id')){
						$id=$request->input('id');
						$user = User::where('id', $id)->first();
					}else{
						$user = User::where('username', $data['username'])->first();
					}
				}
				if($user == null){
					return redirect()->back()->with('flash_message', 'User not found');
				}
				// //dd($request);
				// //dd($user);
				if($user!=null){
						$is_contract_staff = false;
						if($request->has('is_contract_staff'))
						{
								$is_contract_staff = true;
						}

						$valid = Validator::make($data, [
								// 'name' => 'required|string|max:255',
								'section' => 'required|min:2',
								'department' => 'required|min:3',
								'salary_grade' => 'required',
								'resumption_date' => 'required|date',
								'staff_id' => 'required|min:3',
								'designation' => 'required|min:5',
								'gender' => 'required|min:5',
								'area_office' => 'required|min:2',
								'region' => 'required|min:2',
								'username' => 'required'
						]);
						//var_dump($data);
						if($valid){

								//check if user already has an account, then update it, where the id is null
								$user = User::where('username', '=', $data['username'])->first();
								if(!isset($user)){
									return redirect()->back()->with('flash_message', 'User not found');
								}
								$user->username = $data['username'];
								$user->name = $user->getName();
								$user->section = $data['section'];
								$user->designation = $data['designation'];
								$user->salary_grade = $data['salary_grade'];
								$user->resumption_date = $this->setDate($data['resumption_date']);
								$user->is_contract_staff = $is_contract_staff;
								$user->staff_id = $data['staff_id'];
								$user->gender = $data['gender'];
								$user->department = $data['department'];
								$user->section = $data['section'];
								$user->region = $data['region'];
								$user->area_office = $data['area_office'];
								$current = Carbon::now();
								if(Auth::user()->hasRole("HR") && $user->last_login_date != null){
									$user->verified = true;
									$user->verified_by = Auth::user()->username;
									$user->date_verified = $current;
								}
								$user->save();
								if(Auth::user()->hasRole("Admin")){
									$this->updateRoles($request, $user);
								}

								Session::flash('flash_message', $user->name . ' added successfully!');

								return redirect()->back();
						}
				}
				else{
						var_dump($data);
						Session::flash('flash_message', 'Your account was not found!');
						return redirect()->back();
				}
		}

		public function updateRoles(Request $request, $user){
			if($user==null){
				Session::flash("flash_message", "Please specify the username");
				return redirect()->back();
			}

			$roles = Role::all();
				try{
					for ($i=0; $i < sizeof($roles); $i++) {
						$current = $roles[$i];
						$current_role_checked = $request->input($current->name, false);
						echo($current->name . " checked? " . $current_role_checked);
						if($current_role_checked == "on"){
							if(!$user->hasRole($current->name)){
									echo("User DOES NOT HAVE role " . $current->name . " Adding <br/>");
									$user->roles()->attach($current);
								}
						}
						else{
								if($user->hasRole($current->name)){
									echo("User HAS role " . $current->name . " Removing <br/>");
									$user->roles()->detach($current);
								}
						}
					}

				}catch (Exception $e) {

				}
				// //dd($request);
			}

}
