<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeaveEntitlement extends Model
{
    protected $fillable =[
      'salary_grade',
      'leave_type',
      'days_since_resumption',
      'days_since_resumption_max',
      'days_allowed',
      'show'
    ];
}
