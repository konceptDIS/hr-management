<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

//Represents a single office members of which can all see mail meant for that office
class Office extends Model
{
    protected $primaryKey = 'id';

    protected $table = 'offices';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'supervisor_username','created_by', 'section_id', 'parent_section_id','type', 'parent'];
}
