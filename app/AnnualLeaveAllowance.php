<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

//Stores the leave allowance granted each user each year
//So that if we need to reference the allowance at any previous year
//Because a user may be entitled to 21 days this year, then 30 days next year
//and vice versa
class AnnualLeaveAllowance extends Model
{
    protected $fillable = [ 
        'username', 
        'year_start',
        'year_end',
        'days', 
        'leave_type',
        'date_created',
        'created_by'
    ];
    
}
