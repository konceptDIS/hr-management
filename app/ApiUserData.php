<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiUserData extends Model
{
    public $firstname;
    public $lastname;
    public $mobile_phone;
    public $region;
    public $department;
    public $job_title;
    public $name;
    public $mail;
    public $yearResumed;
    public $monthResumed;
    public $dayResumed;
}
