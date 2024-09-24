<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
   protected $fillable = [
     'leave_request_id',
     'name',
     'file_path'
   ];
}
