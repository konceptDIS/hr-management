<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuthCallResult 
{
    public $status_code; //404 : Not auth, 200: authenticated
    public $msg; //User authentication failed
    public $data;

}
