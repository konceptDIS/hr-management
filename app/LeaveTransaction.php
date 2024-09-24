<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeaveTransaction
{
    public $date; 
    public $type; 
    public $leave_type; 
    public $before; 
    public $amount; 
    public $balance; 
    public $remarks; 
    public $application_id; 
    public $approval_id; 
    public $recall_id;
    public $is_new_year;
}
