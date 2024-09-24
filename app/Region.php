<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'regions';
    protected $primaryKey = 'id';
    protected $fillable =
    [
      'name',
      'created_by'
    ];
}
