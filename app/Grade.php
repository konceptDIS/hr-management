<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = [
        'username',
        'grade_level_id',
        'start_date',
        'end_date',
    ];

    public function gradeLevel(){
        return $this->hasOne('\App\GradeLevel', 'id', 'grade_level_id');
    }
}
