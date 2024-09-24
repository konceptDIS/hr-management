<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    protected $fillable = [ 
        'name',
        'leave_days', //21 or 30
        'daily_pay'
    ];
}
