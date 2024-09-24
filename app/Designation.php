<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $table = 'designations';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'fo_equivalent','created_by', 'leave_days'];
}
