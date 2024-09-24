<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;
//use Adldap\Laravel\Traits\AdldapUserModelTrait; //Throws errors will be deleted



class User extends Authenticatable
{
    //use Notifiable;
    use EntrustUserTrait; // add this trait to your user model
    //use AdldapUserModelTrait;    //Throws errors delete


    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function getAvatarUrl()
    {
        return "https://www.gravatar.com/avatar/" . md5($this->email) . "?d=mm";
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'password',
        'department',
        'region',
        'area_office',
        'section',
        'salary_grade',
        'resumption_date',
        'is_contract_staff',
        'username',
        'designation',
        'gender',
        'profile_verified',
        'verified_by',
        'date_verified',
        'middle_name',
        'sn',
        'last_login_date',
        'exists_in_ad',
        'staff_number'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get all of the requests for the user.
     */
    public function leave_requests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function email(){
        return str_replace(" ", "", $this->username) . '@abujaelectricity.com';
    }

    public function getName(){
        $this->name = $this->username;//ucfirst($parts[0]);
        $parts = explode(".",$this->username);
        if(sizeOf($parts)>1){
              $this->name = ucfirst($parts[0]) . " " . ucfirst($parts[1]);
        }
        else if(sizeOf($parts)==1){
                $this->name = ucfirst($parts[0]);
        }
        return $this->name;
    }
    public function formatted_rdate(){
        if(!$this->resumption_date instanceof \Carbon\Carbon){
            $rdate = $this->resumption_date ? \Carbon\Carbon::parse($this->resumption_date) : \Carbon\Carbon::now();
            $rdate == null ? \Carbon\Carbon::now() : $rdate;
            $day = strval($rdate->day);
            $month = strval($rdate->month);
            $day = strlen($day) == 2 ? $day : '0' . $day;
            $month = strlen($month) == 2 ? $month : '0' . $month;
            return $day . '/' . $month . '/' . $rdate->year;
        }
        return $this->resumption_date;
    }

    public function isFirstYear(){
        $rdate = \Carbon\Carbon::parse($this->resumption_date);
        return \Carbon\Carbon::now()->diffInDays($rdate) < 365;    
    }

    public function isAnniversary(){
        $rdate = clone \Carbon\Carbon::parse($this->resumption_date);
        return \Carbon\Carbon::now()->diffInDays($rdate->addYear()) == 0;    
    }

    public function wasFirstYear(\Carbon\Carbon $date){
        // echo "<br/>---------> in user->wasFirstYear() about to check if differeince in days between $date and user employ date < 365 ";
        $diff = $date->diffInDays($this->carbonResumptionDate()) < 365;    
        // echo "<br/>---------> answer is " . var_export($diff, true);
        return $diff;
    }

    public function wasSecondYear(\Carbon\Carbon $date){
        $diff = $date->diffInDays($this->carbonResumptionDate());
        $less_than_plus2 = $date->year < $this->carbonResumptionDate()->year +2;
        $diff = $diff > 365 ;    
        // echo "<br/>less_than_plus2 : " . $less_than_plus2 . " diff: " . $diff;
        return $diff and $less_than_plus2;
    }

    public function onThirdCalendarYear(\Carbon\Carbon $date){
        $plus2 = $this->carbonResumptionDate()->year +2;
        $is3rdYear = $date->year == $plus2;
        // echo "<br/>is " . $date . " 3rd Year? : " . $is3rdYear;
        return $is3rdYear;    
    }

    public function isSecondYear(){
        $rdate = \Carbon\Carbon::parse($this->resumption_date);
        return \Carbon\Carbon::now()->diffInDays($rdate) > 365 and \Carbon\Carbon::now()->diffInDays($rdate) < 730;    
    }

    public function carbonResumptionDate(){
        return \Carbon\Carbon::parse($this->resumption_date);
    }
    
    public function getAnnualLeaveEntitlement(){
        $grade = $this->salary_grade ?? null;
        if(trim($grade) ==  "FO2") return 21;
        if(trim($grade) ==  "FO1") return 30;
        return 0;
    }
    
    public function isOnProrated(){
        if (!$this->carbonResumptionDate()) return false;
        $res_date = $this->carbonResumptionDate();
        $now = \Carbon\Carbon::now();
        $is_prorated = $res_date->year > 2015 && $res_date->month > 1 && $now->year < $res_date->year +1;
        return $is_prorated;
    }
}
