<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AreaOffice extends Model
{
    protected $primaryKey = 'id';

    protected $table = 'area_offices';

    protected $fillable = [
      'name',
      'created_by',
      'region_id',
      'region'
    ];

}
