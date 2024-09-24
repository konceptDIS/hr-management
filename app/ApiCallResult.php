<?php
namespace App;

class ApiCallResult 
{
    public $status_code; //404 : Not auth, 200: authenticated
    public $msg; //User authentication failed
    public $data;
}