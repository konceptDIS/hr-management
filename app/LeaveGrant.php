<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeaveGrant extends Model
{
    protected $fillable = [ 
        'username', 
        'created_by', 
        'leave_type', 
        'days', 
        'reason', 
        'date_granted', 
        'expiry_date', 
        'used', 
        'date_used'];
}
