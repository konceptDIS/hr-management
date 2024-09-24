<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Carbon\Carbon;
use App\DeleteableLeave;
use Illuminate\Support\Facades\Log;

class LeaveRequest extends Model
{
  protected $primaryKey = 'id';

  protected $table = 'leave_requests';
  /**
  * Mass assignable attributes
  *
  *@var array
  */

  protected $fillable = [
    'name',
    'date_created',
    'leave_type',
    'attachement_file_path',
    'created_by',
    'start_date',
    'end_date',
    'submitted',
    'date_submitted',
    'recalled',
    'date_recalled',
    'md_approval_required',
    'supervisor_response', //true=approved, false=denied, null=unprocessed
    'supervisor_username',
    'supervisor_response_date',
    'supervisor_response_reason',
    'hr_response', //true=approved, false=denied, null=unprocessed
    'hr_response_date',
    'hr_response_reason',
    'hr_username',
    'md_response', //true=approved, false=denied, null=unprocessed
    'md_response_date',
    'md_response_reason', //now used to explain discrepancy in leave balance between requests for new staff
    'md_username',
    'stand_in_response', //true=accepts, false=refused, null=no response
    'stand_in_username',
    'stand_in_response_date',
    'stand_in_response_reason',
    'days_requested',
    'days_left',
    'section',
    'salary_grade',
    'status',
    'phone_number',
    'address',
    'reason',
    'days_left_after_approval',//not in use, as this calculation is done in the ui
    'can_be_recalled',
    'date_approved',
    'assisted_by' //The person who applied in behalf of the applicant
  ];

  protected $dates = [
    //'date_created',
    //'date_submitted',
    //'start_date',
    //'end_date'
    // 'date_supervisor_approved',
    // 'date_hr_approved',
    // 'date_supervisor_denied',
    // 'date_hr_denied',
    // 'date_md_denied',
    // 'date_recalled',
    // 'date_stand_in_accepts'
  ];

  /*
  * The attributes that should be cast to native types
  *
  * @var array
  */
  protected $casts = [
    //'taker_user_id' => 'int',
  //  'for_next_action_by_user_id' => 'int'
  ];

  /**
  * Get the user that took the action
  ***/
  public function actor(){
    return $this->hasOne(User::class, 'username', 'created_by');
  }

  public function applicant(){
    return \App\User::where('username', '=', $this->created_by)->first();
  }
  /**
  * Get the leave type
  ***/
  public function leave_type(){
    return $this->hasOne(LeaveType::class, 'id', 'leave_type_id');
  }

  public function isDated(){
    return Carbon::parse($this->start_date) < Carbon::now() or Carbon::parse($this->end_date) < Carbon::now();
  }

  public function getStatus($full=null){

    // if($this->isDated() && ($this->supervisor_response == null or $this->stand_in_response == null)){
    //   return 'Not approvable because the start date or end date is in the past. Please delete';
    // }
    $date_submitted = Carbon::parse($this->created_at);
      $prefix= "Submitted by " . \App\User::where('username', $this->created_by)->first()->getName() . " on " . $date_submitted->toDateString() ." @ " . $date_submitted->toTimeString();

      if($this->hr_response == true){
        $approval = $this->getApproval();
        if($approval == null){
          return $prefix;
        }
        $date_approved = Carbon::parse($approval->created_at);
        if($date_approved){
          $approver = \App\User::where('username', $approval->approved_by)->first();
          if($full){
            return $prefix . " | Approved on " . $date_approved->toDateString() . " @ " . $date_approved->toTimeString() . " by " . $approver->getName();
          }
          return "Approved on " . $date_approved->toDateString() . " @ " . $date_approved->toTimeString() . " by " . $approver->getName();
        }
        return "Approved";
      }
      elseif($this->stand_in_response == true){
        if($this->supervisor_response === null){
          if($this->supervisor_username != Auth::user()->username){
            return "Pending Supervisor Approval";
          }

          if($this->supervisor_username == Auth::user()->username){
            return "Awaiting your Approval";
          }
        }
        if($this->supervisor_response == true && $this->hr_response==null){
          return "Pending HR Approval" . $this->hr_response;
        }
      }
      if($this->hr_response==true && $this->md_response==null && $this->md_approval_required==true){
        return "Pending MD's Approval";
      }
      if($this->stand_in_response === null){
        if($this->stand_in_username == Auth::user()->username){
          return "Will you Stand in?";
        }
        if($this->stand_in_username != Auth::user()->username){
          return "Pending Stand in's Approval";
        }
      }
      if($this->stand_in_response == false){
        return "Stand in Declined: " . $this->stand_in_response_reason;
      }
      if($this->supervisor_response == false){
        return "Supervisor Declined: " . $this->supervisor_response_reason;
      }
      if($this->hr_response == false){
        return "HR Declined: " . $this->hr_response_reason;
      }
    }

  public function recallable(){
      $now_is_between_start_and_end = Carbon::now()->between(Carbon::parse($this->start_date), Carbon::parse($this->end_date));
      $status = $now_is_between_start_and_end && !$this->recalled && $this->supervisor_response == true;
      return $status;
  }

  public function getDateApproved(){
      $approval = LeaveApproval::where('leave_request_id', $this->id)->first();
      return Carbon::parse($approval->created_at);
  }

  public function getApproval(){
      $approval = LeaveApproval::where('leave_request_id', $this->id)->first();
      return $approval;
  }

  public function getStartDate(){
      $date = Carbon::parse($this->start_date);
      if($date){
        return $date->day . '/' . $date->month . '/' . $date->year;
     }
  }

  public function getEndDate(){
      $date = Carbon::parse($this->end_date);
      if($date){
        return $date->day . '/' . $date->month . '/' . $date->year;
      }
  }

  public function dateSubmitted(){
    return Carbon::parse($this->created_at);
  }

  public function allowDelete(){
    $now = Carbon::today();
    $hasNotStarted = Carbon::parse($this->start_date)->isFuture();
    $not_approved_and_new = ($this->supervisor_response === null && strtolower(trim($this->created_by)) == strtolower(trim(Auth::user()->username)) && $now->diffInHours($this->created_at) < 48);
    $approved_and_not_started = (strtolower(trim($this->created_by)) == strtolower(trim(Auth::user()->username)) and ($this->supervisor_response === 1 || $this->supervisor_response === true) && $hasNotStarted);
    $delete_approved = $this->addedToDeletable() > 0;
    $allow_delete = $not_approved_and_new || $approved_and_not_started || $delete_approved;
    //dd(['not_approved_and_new' => $not_approved_and_new, 'approved_and_not_started' => $approved_and_not_started, 'delete_approved' => $delete_approved, 'allow_delete' => $allow_delete]);
    return $allow_delete;
  }

  public function addedToDeletable(){
    return DeleteableLeave::where('leave_request_id', $this->id)->count();
  }

  public function daysEditable(){
    $editable = true;
    if($this->id > 0){
      $future = Carbon::parse($this->start_date)->isFuture();
      $no_response = $this->stand_in_response === null;
      $editable = $future && $no_response;
      Log::info(["leave_request_id" => $this->id, "editable" => $editable, "start_date_is_future" => $future, "stand_in_response_null" => $no_response]);
    }
    return $editable;
  }

  public function lastAction(){
    if($this->supervisor_response && $this->supervisor_response === true){
      return "Supervisor Approved";
    }
    if($this->supervisor_response && $this->supervisor_response === false){
      return "Supervisor Denied";
    }
    if($this->stand_in_response && $this->stand_in_response === true){
      return "Stand-in Approved";
    }
    if($this->stand_in_response && $this->stand_in_response === false){
      return "Stand-in Denied";
    }
    return null;
  }

  public function standInResponseText(){
    $text = "No response";
    if($this->stand_in_response === null) $text =  "No response";
    if($this->stand_in_response === 1 or $this->stand_in_response === true) $text = "Yes";
    if($this->stand_in_response === 0 or $this->stand_in_response === false) $text = "No";
    return $text;
  }

  public function supervisorResponseText(){
    $text = "No response";
    if($this->supervisor_response === null) $text = "No response";
    if($this->supervisor_response === 1 or $this->supervisor_response === true) $text =  "Yes";
    if($this->supervisor_response === 0 or $this->supervisor_response === false) $text =  "No";
    return $text;
  }

  public function allowEdit(){
    $req0 = strtolower(trim($this->created_by)) == strtolower(trim(Auth::user()->username));
    $case1 = $this->stand_in_response === null && $req0;
    $case2 = Carbon::parse($this->start_date)->isFuture() && $req0;
    $case3 = $this->supervisor_response === null && $req0;
    $allowEdit = $case1 || $case2 || $case3;
    return $allowEdit;
  }
}
