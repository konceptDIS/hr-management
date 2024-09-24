<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
    	'name','date','deleted','created_by'
    ];
}
